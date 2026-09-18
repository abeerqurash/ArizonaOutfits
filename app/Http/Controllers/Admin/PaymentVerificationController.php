<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InventoryService;
use App\Services\OrderEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class PaymentVerificationController extends Controller
{
    /**
     * Display orders that need payment verification and recent verified payments.
     */
    public function index(Request $request): View
    {
        $query = Order::query()
            ->with('user')
            ->whereIn('payment_provider', [
                'bank_transfer',
                'stripe',
            ]);

        if ($request->filled('provider')) {
            $query->where(
                'payment_provider',
                $request->string('provider')->toString()
            );
        }

        if ($request->filled('payment_status')) {
            $query->where(
                'payment_status',
                $request->string('payment_status')->toString()
            );
        }

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('order_number', 'like', '%' . $search . '%')
                    ->orWhere('payment_reference', 'like', '%' . $search . '%')
                    ->orWhere('billing_name', 'like', '%' . $search . '%')
                    ->orWhere('billing_email', 'like', '%' . $search . '%');
            });
        }

        $orders = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.payment-verifications.index',
            compact('orders')
        );
    }

    /**
     * Display one payment verification record.
     */
    public function show(Order $order): View
    {
        $order->load([
            'user',
            'items',
        ]);

        return view(
            'admin.payment-verifications.show',
            compact('order')
        );
    }

    /**
     * Verify a pending bank-transfer payment.
     *
     * Stripe payments are never manually marked as paid here.
     * Stripe remains controlled by its verified webhook.
     */
    public function verifyBankTransfer(
        Request $request,
        Order $order,
        InventoryService $inventoryService,
        OrderEmailService $orderEmailService
    ): RedirectResponse {
        $validated = $request->validate([
            'payment_reference' => [
                'required',
                'string',
                'max:255',
            ],

            'admin_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        if (
            $order->payment_provider !== 'bank_transfer'
            && $order->payment_method !== 'bank_transfer'
        ) {
            return back()->with(
                'error',
                'Only bank-transfer orders can be manually verified.'
            );
        }

        if ($order->payment_status === 'paid') {
            return back()->with(
                'success',
                'This bank-transfer payment has already been verified.'
            );
        }

        $verifiedNow = false;

        try {
            DB::transaction(
                function () use (
                    $order,
                    $validated,
                    $inventoryService,
                    &$verifiedNow
                ): void {
                    $lockedOrder = Order::query()
                        ->whereKey($order->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($lockedOrder->payment_status === 'paid') {
                        return;
                    }

                    if (
                        $lockedOrder->payment_provider !== 'bank_transfer'
                        && $lockedOrder->payment_method !== 'bank_transfer'
                    ) {
                        throw new RuntimeException(
                            'Only bank-transfer orders can be manually verified.'
                        );
                    }

                    /*
                     * Stock is deducted only after the administrator
                     * confirms that the bank transfer was received.
                     *
                     * InventoryService is expected to protect against
                     * duplicate deductions using inventory_deducted_at.
                     */
                    $inventoryService->deductForOrder(
                        $lockedOrder
                    );

                    $metadata = $this->paymentMetadata(
                        $lockedOrder
                    );

                    $metadata['bank_transfer_verification'] = [
                        'verified' => true,
                        'verified_at' => now()->toIso8601String(),
                        'verified_by' => auth()->id(),
                        'payment_reference' =>
                            $validated['payment_reference'],
                    ];

                    $lockedOrder->update([
                        'payment_provider' => 'bank_transfer',
                        'payment_method' => 'bank_transfer',

                        'payment_reference' =>
                            $validated['payment_reference'],

                        'payment_status' => 'paid',

                        'paid_at' =>
                            $lockedOrder->paid_at ?? now(),

                        'order_status' => in_array(
                            $lockedOrder->order_status,
                            [
                                null,
                                '',
                                'pending',
                                'payment_pending',
                            ],
                            true
                        )
                            ? 'processing'
                            : $lockedOrder->order_status,

                        'payment_failed_at' => null,
                        'payment_failure_message' => null,

                        'payment_metadata' => $metadata,

                        'admin_notes' =>
                            $validated['admin_notes']
                                ?? $lockedOrder->admin_notes,
                    ]);

                    $verifiedNow = true;
                }
            );
        } catch (RuntimeException $exception) {
            return back()->with(
                'error',
                $exception->getMessage()
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'The bank-transfer payment could not be verified. Please try again.'
            );
        }

        /*
         * Send only after the database transaction has committed.
         * OrderEmailService also protects against duplicate sends.
         */
        if ($verifiedNow) {
            $orderEmailService->sendBankTransferVerified(
                $order->fresh()
            );
        }

        return redirect()
            ->route(
                'admin.payment-verifications.show',
                $order
            )
            ->with(
                'success',
                'Bank-transfer payment verified successfully.'
            );
    }

    /**
     * Reject a bank-transfer payment submission without marking it paid.
     */
    public function rejectBankTransfer(
        Request $request,
        Order $order,
        OrderEmailService $orderEmailService
    ): RedirectResponse {
        $validated = $request->validate([
            'admin_notes' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        if (
            $order->payment_provider !== 'bank_transfer'
            && $order->payment_method !== 'bank_transfer'
        ) {
            return back()->with(
                'error',
                'Only bank-transfer orders can be manually reviewed.'
            );
        }

        if ($order->payment_status === 'paid') {
            return back()->with(
                'error',
                'A verified paid order cannot be rejected from payment verification.'
            );
        }

        $rejectedNow = false;

        try {
            DB::transaction(
                function () use (
                    $order,
                    $validated,
                    &$rejectedNow
                ): void {
                    $lockedOrder = Order::query()
                        ->whereKey($order->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($lockedOrder->payment_status === 'paid') {
                        throw new RuntimeException(
                            'A verified paid order cannot be rejected.'
                        );
                    }

                    $metadata = $this->paymentMetadata(
                        $lockedOrder
                    );

                    $metadata['bank_transfer_verification'] = [
                        'verified' => false,
                        'rejected_at' => now()->toIso8601String(),
                        'reviewed_by' => auth()->id(),
                        'notes' => $validated['admin_notes'],
                    ];

                    $lockedOrder->update([
                        'payment_status' => 'failed',

                        'payment_failure_message' =>
                            'Bank transfer verification was rejected.',

                        'payment_failed_at' => now(),

                        'payment_metadata' => $metadata,

                        'admin_notes' =>
                            $validated['admin_notes'],
                    ]);

                    $rejectedNow = true;
                }
            );
        } catch (RuntimeException $exception) {
            return back()->with(
                'error',
                $exception->getMessage()
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'The bank-transfer review could not be saved. Please try again.'
            );
        }

        /*
         * Send only after the database transaction has committed.
         * OrderEmailService also protects against duplicate sends.
         */
        if ($rejectedNow) {
            $orderEmailService->sendBankTransferRejected(
                $order->fresh()
            );
        }

        return redirect()
            ->route(
                'admin.payment-verifications.show',
                $order
            )
            ->with(
                'success',
                'Bank-transfer payment was rejected.'
            );
    }

    /**
     * Normalize the order payment metadata into an array.
     */
    private function paymentMetadata(Order $order): array
    {
        $metadata = $order->payment_metadata;

        if (is_array($metadata)) {
            return $metadata;
        }

        if (is_string($metadata) && $metadata !== '') {
            $decoded = json_decode(
                $metadata,
                true
            );

            return is_array($decoded)
                ? $decoded
                : [];
        }

        return [];
    }
}
