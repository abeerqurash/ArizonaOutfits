<?php

namespace App\Services;

use App\Mail\CustomerOrderStatusUpdatedMail;
use App\Models\Order;
use App\Models\OrderShipment;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ShipmentOrderStatusSyncService
{
    public function __construct(
        private OrderShipmentStatusTransitionService $transitionService,
        private OrderActivityService $activityService
    ) {
    }

    /**
     * Safely apply a shipment's trusted normalized status to its
     * ArizonaOutfits order.
     *
     * A deliberate admin status override is authoritative over courier state
     * that was already known when the override was made. Only a genuinely
     * newer trusted shipment snapshot may pass that override boundary.
     *
     * Customer notification is scheduled only after a successful normalized
     * status transition and only after the surrounding database transaction
     * commits successfully.
     */
    public function sync(OrderShipment $shipment): array
    {
        return DB::transaction(function () use ($shipment): array {
            $lockedShipment = OrderShipment::query()
                ->whereKey($shipment->getKey())
                ->lockForUpdate()
                ->first();

            if (!$lockedShipment) {
                return $this->result(false, false, 'shipment_not_found');
            }

            $normalizedStatus = $this->nullableStatus(
                $lockedShipment->normalized_status
            );

            if ($normalizedStatus === null) {
                return $this->result(
                    false,
                    false,
                    'shipment_has_no_normalized_status'
                );
            }

            $order = Order::query()
                ->whereKey($lockedShipment->order_id)
                ->lockForUpdate()
                ->first();

            if (!$order) {
                return $this->result(
                    false,
                    false,
                    'order_not_found',
                    null,
                    $normalizedStatus
                );
            }

            $oldStatus = $this->nullableStatus($order->order_status);

            /*
             * Manual admin override boundary.
             *
             * Courier state that was already known when an admin deliberately
             * changed the ArizonaOutfits order status must not immediately undo
             * that decision.
             *
             * A null saved shipment boundary means no trusted courier event was
             * known at the time of the admin override, so a later real event may
             * be evaluated normally.
             */
            if ($order->manual_status_override_at !== null) {
                $overrideBoundary = $this->databaseSecondPrecision(
                    $order->manual_status_override_shipment_event_at
                );

                $shipmentEventAt = $this->databaseSecondPrecision(
                    $lockedShipment->last_event_at
                );

                if (
                    $overrideBoundary !== null
                    && (
                        $shipmentEventAt === null
                        || $shipmentEventAt->lessThanOrEqualTo($overrideBoundary)
                    )
                ) {
                    return $this->result(
                        false,
                        false,
                        'manual_admin_override_blocks_known_shipment_state',
                        $oldStatus,
                        $normalizedStatus,
                        $order
                    );
                }
            }

            /*
             * Preserve the existing bank-transfer payment gate.
             * Courier activity must never bypass payment verification.
             */
            $isBankTransfer =
                $order->payment_provider === 'bank_transfer'
                || $order->payment_method === 'bank_transfer';

            if (
                $isBankTransfer
                && $order->payment_status !== 'paid'
                && in_array(
                    $normalizedStatus,
                    [
                        'confirmed',
                        'processing',
                        'packed',
                        'shipped',
                        'out_for_delivery',
                        'delivered',
                        'completed',
                    ],
                    true
                )
            ) {
                return $this->result(
                    false,
                    false,
                    'bank_transfer_payment_not_verified',
                    $oldStatus,
                    $normalizedStatus,
                    $order
                );
            }

            $decision = $this->transitionService->decision(
                $oldStatus,
                $normalizedStatus
            );

            if (!($decision['allowed'] ?? false)) {
                return $this->result(
                    false,
                    false,
                    (string) ($decision['reason'] ?? 'transition_blocked'),
                    $oldStatus,
                    $normalizedStatus,
                    $order
                );
            }

            /*
             * Only the normalized ArizonaOutfits status is customer-facing.
             * Raw provider status and courier terminology remain internal.
             *
             * Once a genuinely newer courier snapshot successfully advances the
             * order, the previous manual override boundary has served its
             * purpose and is cleared.
             */
            $order->order_status = $normalizedStatus;
            $order->manual_status_override_at = null;
            $order->manual_status_override_shipment_event_at = null;
            $order->save();

            $this->activityService->orderStatusChanged(
                $order,
                $oldStatus,
                $normalizedStatus
            );

            /*
             * Schedule exactly one customer status notification for this
             * successful normalized transition.
             *
             * DB::afterCommit is important:
             * - a rollback produces no email;
             * - blocked/stale/duplicate/same-status paths returned above never
             *   register this callback;
             * - the email reads the committed ArizonaOutfits order state.
             *
             * Notification failure is reported but must never roll back or
             * corrupt the already-committed order/shipment state.
             */
            $this->scheduleCustomerStatusNotificationAfterCommit(
                $order,
                $oldStatus
            );

            return $this->result(
                true,
                true,
                'forward_transition_applied',
                $oldStatus,
                $normalizedStatus,
                $order->fresh()
            );
        });
    }

    private function scheduleCustomerStatusNotificationAfterCommit(
        Order $order,
        ?string $previousStatus
    ): void {
        $customerEmail = $this->customerEmail($order);

        if ($customerEmail === null) {
            return;
        }

        $orderId = (int) $order->getKey();
        $safePreviousStatus = $previousStatus ?? 'not_set';

        DB::afterCommit(function () use (
            $orderId,
            $customerEmail,
            $safePreviousStatus
        ): void {
            try {
                $committedOrder = Order::query()->find($orderId);

                if (!$committedOrder) {
                    return;
                }

                Mail::to($customerEmail)->send(
                    new CustomerOrderStatusUpdatedMail(
                        $committedOrder,
                        $safePreviousStatus
                    )
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        });
    }

    private function customerEmail(Order $order): ?string
    {
        $possibleEmails = [
            $order->billing_email,
            $order->shipping_email,
            $order->customer_email,
            $order->user?->email,
        ];

        foreach ($possibleEmails as $email) {
            if (
                is_string($email)
                && trim($email) !== ''
                && filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false
            ) {
                return trim($email);
            }
        }

        return null;
    }

    private function result(
        bool $allowed,
        bool $updated,
        string $reason,
        ?string $oldStatus = null,
        ?string $newStatus = null,
        ?Order $order = null
    ): array {
        return [
            'allowed' => $allowed,
            'updated' => $updated,
            'reason' => $reason,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'order_id' => $order?->id,
            'order_number' => $order?->order_number,
        ];
    }

    private function nullableStatus(mixed $status): ?string
    {
        if ($status === null) {
            return null;
        }

        $status = trim(mb_strtolower((string) $status));

        return $status === '' ? null : $status;
    }

    /**
     * Match the shipment architecture's database timestamp precision.
     */
    private function databaseSecondPrecision(
        ?CarbonInterface $value
    ): ?CarbonInterface {
        return $value?->copy()->setMicrosecond(0);
    }
}
