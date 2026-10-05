<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InventoryService;
use App\Services\OrderActivityService;
use App\Services\OrderEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Throwable;

class OrderRefundController extends Controller
{
    /**
     * Cancel an UNPAID order.
     *
     * Paid orders must use the refund workflow instead.
     */
    public function cancel(
        Request $request,
        Order $order,
        InventoryService $inventoryService,
        OrderActivityService $activityService,
        OrderEmailService $orderEmailService
    ): RedirectResponse {
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $oldStatus = (string) $order->order_status;

        if (in_array($oldStatus, ['cancelled', 'refunded'], true)) {
            return back()->with('warning', 'This order is already closed.');
        }

        if ($this->isPaid($order)) {
            return back()->with(
                'error',
                'A paid order cannot be cancelled directly. Use the refund action instead.'
            );
        }

        try {
            DB::transaction(function () use (
                $order,
                $validated,
                $inventoryService,
                $activityService,
                $oldStatus
            ): void {
                $locked = Order::query()
                    ->with('items')
                    ->lockForUpdate()
                    ->findOrFail($order->id);

                if (in_array($locked->order_status, ['cancelled', 'refunded'], true)) {
                    return;
                }

                if ($this->isPaid($locked)) {
                    throw new RuntimeException(
                        'The payment became paid while cancellation was being processed. Use the refund action instead.'
                    );
                }

                /*
                 * Normal unpaid orders have no deducted inventory.
                 * This also safely repairs legacy orders where inventory
                 * happened to be deducted before cancellation.
                 */
                $inventoryService->restoreForOrder($locked, 'cancelled');

                $notes = trim((string) ($validated['admin_notes'] ?? ''));

                $locked->forceFill([
                    'order_status' => 'cancelled',
                    'admin_notes' => $notes !== ''
                        ? $this->appendAdminNote($locked->admin_notes, $notes)
                        : $locked->admin_notes,
                    'manual_status_override_at' => now()->startOfSecond(),
                ])->save();

                $activityService->orderStatusChanged(
                    $locked,
                    $oldStatus,
                    'cancelled'
                );
            }, 3);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'The order could not be cancelled: ' . $exception->getMessage()
            );
        }

        $orderEmailService->sendCustomerStatusUpdated($order->fresh(), $oldStatus);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order cancelled successfully.');
    }

    /**
     * Full refund for a paid Stripe or Bank Transfer order.
     *
     * Stripe: creates a real Stripe refund first.
     * Bank transfer: requires an administrator-entered external refund reference.
     */
    public function refund(
        Request $request,
        Order $order,
        InventoryService $inventoryService,
        OrderActivityService $activityService,
        OrderEmailService $orderEmailService
    ): RedirectResponse {
        $isStripe = $this->isStripe($order);
        $isBankTransfer = $this->isBankTransfer($order);

        if (!$isStripe && !$isBankTransfer) {
            return back()->with(
                'error',
                'This payment method does not have a configured refund workflow.'
            );
        }

        if ($order->order_status === 'refunded' || $order->payment_status === 'refunded') {
            return back()->with('warning', 'This order has already been refunded.');
        }

        if (!$this->isPaid($order)) {
            return back()->with(
                'error',
                'Only a paid order can be refunded.'
            );
        }

        $rules = [
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ];

        if ($isBankTransfer) {
            $rules['refund_reference'] = ['required', 'string', 'max:255'];
        } else {
            $rules['refund_reference'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        $oldOrderStatus = (string) $order->order_status;
        $oldPaymentStatus = (string) $order->payment_status;

        $providerRefundId = null;
        $providerRefundStatus = null;

        /*
         * Stripe must be refunded at Stripe BEFORE the local database is
         * allowed to claim that the order is refunded.
         *
         * A stable idempotency key makes retrying safe if Stripe succeeds
         * but the local database operation later fails.
         */
        if ($isStripe) {
            $paymentIntentId = trim((string) $order->payment_intent_id);

            if ($paymentIntentId === '') {
                return back()->with(
                    'error',
                    'This Stripe order has no PaymentIntent reference and cannot be refunded automatically.'
                );
            }

            $stripeSecret = trim((string) config('payments.stripe.secret'));

            if (
                !config('payments.stripe.enabled', false)
                || $stripeSecret === ''
                || str_contains($stripeSecret, 'your_secret')
            ) {
                return back()->with(
                    'error',
                    'Stripe is not configured correctly, so this refund was not attempted.'
                );
            }

            try {
                $stripe = new StripeClient($stripeSecret);

                $refund = $stripe->refunds->create(
                    [
                        'payment_intent' => $paymentIntentId,
                        'metadata' => [
                            'order_id' => (string) $order->id,
                            'order_number' => (string) $order->order_number,
                            'source' => 'arizonaoutfits_admin',
                        ],
                    ],
                    [
                        'idempotency_key' => 'arizonaoutfits_order_' . $order->id . '_full_refund',
                    ]
                );

                $providerRefundId = (string) $refund->id;
                $providerRefundStatus = (string) $refund->status;

                if (!in_array($providerRefundStatus, ['succeeded', 'pending'], true)) {
                    return back()->with(
                        'error',
                        'Stripe did not accept the refund. Local order status was not changed.'
                    );
                }
            } catch (ApiErrorException $exception) {
                report($exception);

                return back()->with(
                    'error',
                    'Stripe refund failed. Local order status was not changed: '
                    . $this->safeStripeMessage($exception)
                );
            } catch (Throwable $exception) {
                report($exception);

                return back()->with(
                    'error',
                    'Stripe refund could not be completed. Local order status was not changed.'
                );
            }
        }

        try {
            DB::transaction(function () use (
                $order,
                $validated,
                $inventoryService,
                $activityService,
                $oldOrderStatus,
                $oldPaymentStatus,
                $isStripe,
                $providerRefundId,
                $providerRefundStatus
            ): void {
                $locked = Order::query()
                    ->with('items')
                    ->lockForUpdate()
                    ->findOrFail($order->id);

                if (
                    $locked->order_status === 'refunded'
                    || $locked->payment_status === 'refunded'
                ) {
                    return;
                }

                if (!$this->isPaid($locked)) {
                    throw new RuntimeException(
                        'The order is no longer in a refundable paid state.'
                    );
                }

                $inventoryService->restoreForOrder($locked, 'refunded');

                $metadata = is_array($locked->payment_metadata)
                    ? $locked->payment_metadata
                    : [];

                $refundMetadata = [
                    'refunded_at' => now()->toIso8601String(),
                    'refunded_by_admin_id' => auth('admin')->id(),
                    'refund_type' => 'full',
                ];

                if ($isStripe) {
                    $refundMetadata['stripe_refund_id'] = $providerRefundId;
                    $refundMetadata['stripe_refund_status'] = $providerRefundStatus;
                } else {
                    $refundMetadata['bank_refund_reference'] =
                        trim((string) $validated['refund_reference']);
                }

                $metadata['refund'] = $refundMetadata;

                $notes = trim((string) ($validated['admin_notes'] ?? ''));

                $locked->forceFill([
                    'payment_status' => 'refunded',
                    'order_status' => 'refunded',
                    'payment_metadata' => $metadata,
                    'admin_notes' => $notes !== ''
                        ? $this->appendAdminNote($locked->admin_notes, $notes)
                        : $locked->admin_notes,
                    'manual_status_override_at' => now()->startOfSecond(),
                ])->save();

                if ($oldPaymentStatus !== 'refunded') {
                    $activityService->paymentStatusChanged(
                        $locked,
                        $oldPaymentStatus,
                        'refunded'
                    );
                }

                if ($oldOrderStatus !== 'refunded') {
                    $activityService->orderStatusChanged(
                        $locked,
                        $oldOrderStatus,
                        'refunded'
                    );
                }
            }, 3);
        } catch (Throwable $exception) {
            report($exception);

            $message = $isStripe
                ? 'Stripe accepted the refund, but the local order update failed. Retry this same refund action; the Stripe idempotency key prevents a second refund.'
                : 'The bank-transfer refund could not be recorded locally.';

            return back()->with('error', $message);
        }

        $orderEmailService->sendCustomerStatusUpdated($order->fresh(), $oldOrderStatus);

        $message = $isStripe && $providerRefundStatus === 'pending'
            ? 'Stripe accepted the refund and the order was marked refunded. Stripe currently reports the refund as pending.'
            : 'Order refunded successfully and inventory was restored.';

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', $message);
    }

    private function isPaid(Order $order): bool
    {
        return in_array(
            (string) $order->payment_status,
            ['paid', 'completed', 'succeeded'],
            true
        );
    }

    private function isStripe(Order $order): bool
    {
        return $order->payment_provider === 'stripe'
            || $order->payment_method === 'stripe';
    }

    private function isBankTransfer(Order $order): bool
    {
        return $order->payment_provider === 'bank_transfer'
            || $order->payment_method === 'bank_transfer';
    }

    private function appendAdminNote(?string $existing, string $newNote): string
    {
        $existing = trim((string) $existing);

        if ($existing === '') {
            return $newNote;
        }

        return $existing . PHP_EOL . PHP_EOL . $newNote;
    }

    private function safeStripeMessage(ApiErrorException $exception): string
    {
        $message = trim((string) $exception->getMessage());

        return $message !== ''
            ? $message
            : 'Stripe rejected the refund request.';
    }
}
