<?php

namespace App\Services;

class OrderShipmentStatusTransitionService
{
    /**
     * Shipment-driven ArizonaOutfits order statuses in forward-progress order.
     *
     * "confirmed" is included because an already-confirmed paid order may move
     * forward to processing when courier activity begins.
     */
    private const PROGRESS_RANK = [
        'pending' => 0,
        'confirmed' => 10,
        'processing' => 20,
        'packed' => 30,
        'shipped' => 40,
        'out_for_delivery' => 50,
        'delivered' => 60,
        'completed' => 70,
    ];

    /**
     * Terminal/sensitive states that courier automation must never overwrite.
     */
    private const PROTECTED_STATUSES = [
        'cancelled',
        'refunded',
        'completed',
    ];

    /**
     * Decide whether a normalized courier status may automatically update the
     * ArizonaOutfits customer-facing order status.
     *
     * This service only makes the decision. It does NOT write to the database.
     */
    public function canTransition(
        ?string $currentOrderStatus,
        ?string $normalizedShipmentStatus
    ): bool {
        $current = $this->normalize($currentOrderStatus);
        $next = $this->normalize($normalizedShipmentStatus);

        if ($current === null || $next === null) {
            return false;
        }

        /*
         * Courier automation cannot revive or overwrite a protected order.
         */
        if (in_array($current, self::PROTECTED_STATUSES, true)) {
            return false;
        }

        /*
         * Cancellation is intentionally NOT automatic. A courier-side
         * cancellation/return can have business/payment implications and must
         * be reviewed by ArizonaOutfits before changing the customer order.
         */
        if ($next === 'cancelled') {
            return false;
        }

        if (
            !array_key_exists($current, self::PROGRESS_RANK)
            || !array_key_exists($next, self::PROGRESS_RANK)
        ) {
            return false;
        }

        /*
         * Same status is not a transition, and older/out-of-order courier
         * events must never move an order backwards.
         */
        return self::PROGRESS_RANK[$next] > self::PROGRESS_RANK[$current];
    }

    /**
     * Explain why a transition is or is not eligible.
     *
     * Useful later for webhook logs/admin diagnostics without exposing raw
     * courier data to customers.
     */
    public function decision(
        ?string $currentOrderStatus,
        ?string $normalizedShipmentStatus
    ): array {
        $current = $this->normalize($currentOrderStatus);
        $next = $this->normalize($normalizedShipmentStatus);

        if ($current === null || $next === null) {
            return [
                'allowed' => false,
                'reason' => 'missing_or_invalid_status',
            ];
        }

        if (in_array($current, self::PROTECTED_STATUSES, true)) {
            return [
                'allowed' => false,
                'reason' => 'current_order_status_is_protected',
            ];
        }

        if ($next === 'cancelled') {
            return [
                'allowed' => false,
                'reason' => 'courier_cancellation_requires_manual_review',
            ];
        }

        if (
            !array_key_exists($current, self::PROGRESS_RANK)
            || !array_key_exists($next, self::PROGRESS_RANK)
        ) {
            return [
                'allowed' => false,
                'reason' => 'status_not_supported_for_automatic_transition',
            ];
        }

        if (self::PROGRESS_RANK[$next] === self::PROGRESS_RANK[$current]) {
            return [
                'allowed' => false,
                'reason' => 'order_already_has_this_status',
            ];
        }

        if (self::PROGRESS_RANK[$next] < self::PROGRESS_RANK[$current]) {
            return [
                'allowed' => false,
                'reason' => 'regressive_transition_blocked',
            ];
        }

        return [
            'allowed' => true,
            'reason' => 'forward_transition_allowed',
        ];
    }

    private function normalize(?string $status): ?string
    {
        if ($status === null) {
            return null;
        }

        $status = trim(mb_strtolower($status));

        return $status === '' ? null : $status;
    }
}
