<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use Illuminate\Database\QueryException;
use RuntimeException;

class CouponRedemptionService
{
    /**
     * Consume the coupon attached to a successfully paid order.
     *
     * IMPORTANT:
     * Call this from inside the same database transaction that marks the
     * order paid. The caller should already hold an order row lock.
     *
     * The coupon row is also locked here. The unique order_id constraint on
     * coupon_redemptions is the final database-level idempotency guarantee.
     */
    public function redeemPaidOrder(Order $order): ?CouponRedemption
    {
        $code = trim((string) $order->coupon_code);

        if ($code === '' || (float) $order->discount <= 0) {
            return null;
        }

        if ($order->payment_status !== 'paid') {
            throw new RuntimeException(
                'A coupon redemption can only be recorded for a paid order.'
            );
        }

        $existing = CouponRedemption::query()
            ->where('order_id', $order->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [strtoupper($code)])
            ->lockForUpdate()
            ->first();

        if (!$coupon) {
            throw new RuntimeException(
                'The coupon used by this order no longer exists.'
            );
        }

        $usedCount = CouponRedemption::query()
            ->where('coupon_id', $coupon->id)
            ->count();

        $usedCount = max($usedCount, (int) $coupon->used_count,
            Order::withTrashed()->whereKeyNot($order->id)->where('payment_status', 'paid')
                ->whereRaw('UPPER(coupon_code) = ?', [strtoupper($code)])->count());

        if (
            $coupon->usage_limit !== null
            && $usedCount >= (int) $coupon->usage_limit
        ) {
            throw new RuntimeException(
                'This coupon reached its total usage limit before payment could be finalized.'
            );
        }

        if ($coupon->per_user_usage_limit !== null) {
            if (!$order->user_id) {
                throw new RuntimeException(
                    'This coupon requires an authenticated customer.'
                );
            }

            $customerUses = CouponRedemption::query()
                ->where('coupon_id', $coupon->id)
                ->where('user_id', $order->user_id)
                ->count();

            $customerUses = max($customerUses,
                Order::withTrashed()->whereKeyNot($order->id)->where('user_id', $order->user_id)
                    ->where('payment_status', 'paid')
                    ->whereRaw('UPPER(coupon_code) = ?', [strtoupper($code)])->count());
            if ($customerUses >= (int) $coupon->per_user_usage_limit) {
                throw new RuntimeException(
                    'This customer has reached the coupon usage limit.'
                );
            }
        }

        try {
            $redemption = CouponRedemption::query()->create([
                'coupon_id' => $coupon->id,
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'coupon_code' => $coupon->code,
                'discount_amount' => round((float) $order->discount, 2),
                'redeemed_at' => $order->paid_at ?? now(),
            ]);
        } catch (QueryException $exception) {
            // A concurrent duplicate webhook may have inserted the unique
            // order redemption first. Return it if so; otherwise rethrow.
            $existing = CouponRedemption::query()
                ->where('order_id', $order->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            throw $exception;
        }

        // Keep the legacy/admin counter synchronized with the authoritative
        // redemption ledger instead of blindly incrementing it.
        $coupon->forceFill([
            'used_count' => max($usedCount + 1, CouponRedemption::query()
                ->where('coupon_id', $coupon->id)->count()),
        ])->save();

        return $redemption;
    }
}
