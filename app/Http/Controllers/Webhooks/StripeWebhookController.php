<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use App\Services\OrderEmailService;
use Throwable;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    private InventoryService $inventoryService;

    public function __construct(
        InventoryService $inventoryService,
        private readonly OrderEmailService $orderEmailService
    ) {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Receive and process Stripe webhook events.
     */
    public function handle(Request $request): JsonResponse
    {
        $webhookSecret = trim(
            (string) config(
                'payments.stripe.webhook_secret',
                ''
            )
        );

        if ($webhookSecret === '') {
            report(
                new \RuntimeException(
                    'STRIPE_WEBHOOK_SECRET is not configured.'
                )
            );

            return response()->json([
                'success' => false,
                'message' => 'Webhook configuration is missing.',
            ], 500);
        }

        /*
         * Stripe signature verification requires the exact,
         * unmodified raw request body.
         */
        $payload = $request->getContent();

        $signature = (string) $request->header(
            'Stripe-Signature',
            ''
        );

        if ($payload === '' || $signature === '') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook request.',
            ], 400);
        }

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $webhookSecret
            );
        } catch (UnexpectedValueException $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook payload.',
            ], 400);
        } catch (SignatureVerificationException $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Webhook signature verification failed.',
            ], 400);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Webhook verification failed.',
            ], 400);
        }

        try {
            match ($event->type) {
                'payment_intent.succeeded' =>
                $this->handlePaymentSucceeded(
                    $event->data->object,
                    $event->id
                ),

                'payment_intent.processing' =>
                $this->handlePaymentProcessing(
                    $event->data->object,
                    $event->id
                ),

                'payment_intent.payment_failed' =>
                $this->handlePaymentFailed(
                    $event->data->object,
                    $event->id
                ),

                'payment_intent.canceled' =>
                $this->handlePaymentCancelled(
                    $event->data->object,
                    $event->id
                ),

                default =>
                null,
            };
        } catch (Throwable $exception) {
            report($exception);

            /*
             * Return an error so Stripe can retry delivery.
             */
            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'received' => true,
        ]);
    }

    /**
     * Mark a Stripe order as successfully paid.
     */
    private function handlePaymentSucceeded(
        PaymentIntent $paymentIntent,
        string $eventId
    ): void {
        $order = DB::transaction(function () use (
            $paymentIntent,
            $eventId
        ): Order {
            $order = $this->findAndLockOrder(
                $paymentIntent
            );

            if (!$order) {
                throw new \RuntimeException(
                    'Stripe order not found for PaymentIntent: '
                        . $paymentIntent->id
                );
            }

            $this->verifyPaymentMatchesOrder(
                $order,
                $paymentIntent
            );

            /*
 * Deduct inventory only after Stripe confirms
 * that the payment succeeded and the amount
 * and currency match the local order.
 *
 * InventoryService uses inventory_deducted_at,
 * so duplicate Stripe webhooks cannot deduct
 * the stock more than once.
 */
            $this->inventoryService->deductForOrder(
                $order
            );

            /*
 * Stripe may resend the same event.
 * Updating an already-paid order remains safe.
 */
            $metadata = $this->mergePaymentMetadata(
                $order,
                [
                    'provider' => 'stripe',
                    'stripe_event_id' => $eventId,
                    'stripe_event_type' =>
                    'payment_intent.succeeded',
                    'stripe_status' =>
                    $paymentIntent->status,
                    'stripe_payment_intent_id' =>
                    $paymentIntent->id,
                    'amount' =>
                    $paymentIntent->amount,
                    'amount_received' =>
                    $paymentIntent->amount_received,
                    'currency' =>
                    strtolower(
                        (string) $paymentIntent->currency
                    ),
                    'latest_charge' =>
                    $this->extractStripeIdentifier(
                        $paymentIntent->latest_charge
                    ),
                    'processed_at' =>
                    now()->toIso8601String(),
                ]
            );

            $order->update([
                'payment_provider' => 'stripe',

                'payment_reference' => $paymentIntent->id,

                'payment_intent_id' => $paymentIntent->id,

                'payment_status' => 'paid',

                'order_status' => in_array(
                    $order->order_status,
                    [
                        null,
                        '',
                        'pending',
                        'payment_pending',
                    ],
                    true
                )
                    ? 'processing'
                    : $order->order_status,

                'paid_at' => $order->paid_at ?? now(),

                'payment_failed_at' => null,

                'payment_failure_message' => null,

                'payment_metadata' => $metadata,
            ]);

            return $order;
        });

        $this->orderEmailService->sendOrderEmails(
            $order->fresh()
        );
    }

    /**
     * Mark the Stripe payment as processing.
     */
    private function handlePaymentProcessing(
        PaymentIntent $paymentIntent,
        string $eventId
    ): void {
        DB::transaction(function () use (
            $paymentIntent,
            $eventId
        ): void {
            $order = $this->findAndLockOrder(
                $paymentIntent
            );

            if (!$order) {
                throw new \RuntimeException(
                    'Stripe order not found for PaymentIntent: '
                        . $paymentIntent->id
                );
            }

            $this->verifyPaymentMatchesOrder(
                $order,
                $paymentIntent
            );

            /*
             * Never downgrade an order that has already
             * been confirmed as paid.
             */
            if ($order->payment_status === 'paid') {
                return;
            }

            $metadata = $this->mergePaymentMetadata(
                $order,
                [
                    'provider' => 'stripe',
                    'stripe_event_id' => $eventId,
                    'stripe_event_type' =>
                    'payment_intent.processing',
                    'stripe_status' =>
                    $paymentIntent->status,
                    'stripe_payment_intent_id' =>
                    $paymentIntent->id,
                    'amount' =>
                    $paymentIntent->amount,
                    'currency' =>
                    strtolower(
                        (string) $paymentIntent->currency
                    ),
                    'processed_at' =>
                    now()->toIso8601String(),
                ]
            );

            $order->update([
                'payment_provider' => 'stripe',

                'payment_reference' =>
                $paymentIntent->id,

                'payment_intent_id' =>
                $paymentIntent->id,

                'payment_status' => 'processing',

                'payment_failed_at' => null,

                'payment_failure_message' => null,

                'payment_metadata' => $metadata,
            ]);
        });
    }

    /**
     * Mark a Stripe payment attempt as failed.
     */
    private function handlePaymentFailed(
        PaymentIntent $paymentIntent,
        string $eventId
    ): void {
        DB::transaction(function () use (
            $paymentIntent,
            $eventId
        ): void {
            $order = $this->findAndLockOrder(
                $paymentIntent
            );

            if (!$order) {
                throw new \RuntimeException(
                    'Stripe order not found for PaymentIntent: '
                        . $paymentIntent->id
                );
            }

            /*
             * Do not change an order back to failed if an
             * earlier or duplicate event arrives after success.
             */
            if ($order->payment_status === 'paid') {
                return;
            }

            $failureMessage =
                $paymentIntent
                ->last_payment_error
                ?->message
                ?? 'The Stripe payment was not completed.';

            $failureCode =
                $paymentIntent
                ->last_payment_error
                ?->code;

            $declineCode =
                $paymentIntent
                ->last_payment_error
                ?->decline_code;

            $metadata = $this->mergePaymentMetadata(
                $order,
                [
                    'provider' => 'stripe',
                    'stripe_event_id' => $eventId,
                    'stripe_event_type' =>
                    'payment_intent.payment_failed',
                    'stripe_status' =>
                    $paymentIntent->status,
                    'stripe_payment_intent_id' =>
                    $paymentIntent->id,
                    'failure_code' => $failureCode,
                    'decline_code' => $declineCode,
                    'failure_message' =>
                    $failureMessage,
                    'processed_at' =>
                    now()->toIso8601String(),
                ]
            );

            $order->update([
                'payment_provider' => 'stripe',

                'payment_reference' =>
                $paymentIntent->id,

                'payment_intent_id' =>
                $paymentIntent->id,

                'payment_status' => 'failed',

                'payment_failed_at' => now(),

                'payment_failure_message' =>
                $failureMessage,

                'payment_metadata' => $metadata,
            ]);
        });
    }

    /**
     * Mark a cancelled Stripe PaymentIntent.
     */
    private function handlePaymentCancelled(
        PaymentIntent $paymentIntent,
        string $eventId
    ): void {
        DB::transaction(function () use (
            $paymentIntent,
            $eventId
        ): void {
            $order = $this->findAndLockOrder(
                $paymentIntent
            );

            if (!$order) {
                throw new \RuntimeException(
                    'Stripe order not found for PaymentIntent: '
                        . $paymentIntent->id
                );
            }

            if ($order->payment_status === 'paid') {
                return;
            }

            $cancellationReason =
                $paymentIntent->cancellation_reason
                ?? 'Stripe payment was cancelled.';

            $metadata = $this->mergePaymentMetadata(
                $order,
                [
                    'provider' => 'stripe',
                    'stripe_event_id' => $eventId,
                    'stripe_event_type' =>
                    'payment_intent.canceled',
                    'stripe_status' =>
                    $paymentIntent->status,
                    'stripe_payment_intent_id' =>
                    $paymentIntent->id,
                    'cancellation_reason' =>
                    $cancellationReason,
                    'processed_at' =>
                    now()->toIso8601String(),
                ]
            );

            $order->update([
                'payment_provider' => 'stripe',

                'payment_reference' =>
                $paymentIntent->id,

                'payment_intent_id' =>
                $paymentIntent->id,

                'payment_status' => 'cancelled',

                'payment_failed_at' => now(),

                'payment_failure_message' =>
                is_string($cancellationReason)
                    ? $cancellationReason
                    : 'Stripe payment was cancelled.',

                'payment_metadata' => $metadata,
            ]);
        });
    }

    /**
     * Find the local order associated with a PaymentIntent
     * and lock it to prevent concurrent webhook updates.
     */
    private function findAndLockOrder(
        PaymentIntent $paymentIntent
    ): ?Order {
        $order = Order::query()
            ->where(
                'payment_intent_id',
                $paymentIntent->id
            )
            ->where(
                'payment_provider',
                'stripe'
            )
            ->lockForUpdate()
            ->first();

        if ($order) {
            return $order;
        }

        $metadataOrderId = $this->metadataValue(
            $paymentIntent,
            'order_id'
        );

        if ($metadataOrderId !== null) {
            $order = Order::query()
                ->whereKey($metadataOrderId)
                ->where(
                    'payment_method',
                    'stripe'
                )
                ->lockForUpdate()
                ->first();

            if ($order) {
                return $order;
            }
        }

        $metadataOrderNumber = $this->metadataValue(
            $paymentIntent,
            'order_number'
        );

        if ($metadataOrderNumber !== null) {
            return Order::query()
                ->where(
                    'order_number',
                    $metadataOrderNumber
                )
                ->where(
                    'payment_method',
                    'stripe'
                )
                ->lockForUpdate()
                ->first();
        }

        return null;
    }

    /**
     * Confirm that the Stripe amount and currency match
     * the order stored by Laravel.
     */
    private function verifyPaymentMatchesOrder(
        Order $order,
        PaymentIntent $paymentIntent
    ): void {
        $orderCurrency = strtolower(
            (string) (
                $order->currency
                ?: config(
                    'payments.currency',
                    'USD'
                )
            )
        );

        $stripeCurrency = strtolower(
            (string) $paymentIntent->currency
        );

        if ($orderCurrency !== $stripeCurrency) {
            throw new \RuntimeException(
                'Stripe currency does not match order '
                    . $order->order_number
                    . '.'
            );
        }

        $expectedAmount = $this->convertToMinorUnit(
            (float) $order->total,
            $orderCurrency
        );

        if (
            (int) $paymentIntent->amount
            !== $expectedAmount
        ) {
            throw new \RuntimeException(
                'Stripe amount does not match order '
                    . $order->order_number
                    . '.'
            );
        }

        $metadataOrderId = $this->metadataValue(
            $paymentIntent,
            'order_id'
        );

        if (
            $metadataOrderId !== null
            && (string) $metadataOrderId
            !== (string) $order->id
        ) {
            throw new \RuntimeException(
                'Stripe metadata order ID does not match.'
            );
        }

        $metadataOrderNumber = $this->metadataValue(
            $paymentIntent,
            'order_number'
        );

        if (
            $metadataOrderNumber !== null
            && $metadataOrderNumber
            !== $order->order_number
        ) {
            throw new \RuntimeException(
                'Stripe metadata order number does not match.'
            );
        }
    }

    /**
     * Convert the order total to Stripe's minor unit.
     */
    private function convertToMinorUnit(
        float $amount,
        string $currency
    ): int {
        $zeroDecimalCurrencies = [
            'bif',
            'clp',
            'djf',
            'gnf',
            'jpy',
            'kmf',
            'krw',
            'mga',
            'pyg',
            'rwf',
            'ugx',
            'vnd',
            'vuv',
            'xaf',
            'xof',
            'xpf',
        ];

        if (
            in_array(
                strtolower($currency),
                $zeroDecimalCurrencies,
                true
            )
        ) {
            return (int) round($amount);
        }

        return (int) round($amount * 100);
    }

    /**
     * Read one metadata value safely.
     */
    private function metadataValue(
        PaymentIntent $paymentIntent,
        string $key
    ): ?string {
        $metadata = $paymentIntent->metadata;

        if (!$metadata) {
            return null;
        }

        $value = $metadata->{$key} ?? null;

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }

    /**
     * Preserve existing payment metadata while adding
     * the latest Stripe event information.
     */
    private function mergePaymentMetadata(
        Order $order,
        array $newMetadata
    ): array {
        $existingMetadata = is_array(
            $order->payment_metadata
        )
            ? $order->payment_metadata
            : [];

        return array_merge(
            $existingMetadata,
            $newMetadata
        );
    }

    /**
     * Extract an ID from either a Stripe object or string.
     */
    private function extractStripeIdentifier(
        mixed $value
    ): ?string {
        if (is_string($value)) {
            return $value !== ''
                ? $value
                : null;
        }

        if (
            is_object($value)
            && isset($value->id)
        ) {
            return (string) $value->id;
        }

        return null;
    }
}
