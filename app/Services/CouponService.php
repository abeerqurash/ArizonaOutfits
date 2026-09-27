<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Carbon;

class CouponService
{
    /**
     * Evaluate a coupon code against the live cart and current customer.
     *
     * Discount rules:
     * - minimum_order_amount is checked against the complete cart subtotal.
     * - product/category/variant targeting limits the discountable subtotal.
     * - percentage discounts apply only to the eligible subtotal.
     * - fixed discounts can never exceed the eligible subtotal.
     * - maximum_discount caps either discount type when configured.
     * - global usage uses the larger of coupons.used_count and paid orders found
     *   for the code, which keeps legacy paid orders from being ignored.
     * - per-user limits are based on paid orders for the authenticated user.
     */
    public function evaluateByCode(
        string $code,
        array $cart,
        ?int $userId = null
    ): array {
        $normalized = strtoupper(trim($code));

        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [$normalized])
            ->first();

        if (!$coupon) {
            return $this->failure('The coupon code is invalid.');
        }

        return $this->evaluate($coupon, $cart, $userId);
    }

    public function evaluateSessionCoupon(
        array $cart,
        ?int $userId = null
    ): array {
        $sessionCoupon = session('cart_coupon');

        if (
            !is_array($sessionCoupon)
            || empty($sessionCoupon['id'])
        ) {
            return $this->failure('No coupon is applied.', false);
        }

        $coupon = Coupon::query()->find((int) $sessionCoupon['id']);

        if (!$coupon) {
            return $this->failure('The applied coupon no longer exists.');
        }

        return $this->evaluate($coupon, $cart, $userId);
    }

    public function refreshSessionCoupon(
        array $cart,
        ?int $userId = null
    ): array {
        $result = $this->evaluateSessionCoupon($cart, $userId);

        if (!$result['valid']) {
            session()->forget('cart_coupon');
            return $result;
        }

        session()->put('cart_coupon', $result['session']);

        return $result;
    }

    public function applyToSession(
        Coupon $coupon,
        array $cart,
        ?int $userId = null
    ): array {
        $result = $this->evaluate($coupon, $cart, $userId);

        if ($result['valid']) {
            session()->put('cart_coupon', $result['session']);
        }

        return $result;
    }

    public function evaluate(
        Coupon $coupon,
        array $cart,
        ?int $userId = null
    ): array {
        if (empty($cart)) {
            return $this->failure('You cannot apply a coupon to an empty cart.');
        }

        if (!$coupon->status) {
            return $this->failure('This coupon is inactive.');
        }

        $now = Carbon::now();

        if ($coupon->start_date && $now->lt($coupon->start_date)) {
            return $this->failure('This coupon is not active yet.');
        }

        if ($coupon->end_date && $now->gt($coupon->end_date)) {
            return $this->failure('This coupon has expired.');
        }

        $cartSubtotal = $this->cartSubtotal($cart);

        if (
            (float) ($coupon->minimum_order_amount ?? 0) > 0
            && $cartSubtotal < (float) $coupon->minimum_order_amount
        ) {
            return $this->failure(
                'A minimum order amount of $'
                . number_format((float) $coupon->minimum_order_amount, 2)
                . ' is required for this coupon.'
            );
        }

        $paidGlobalUses = Order::query()
            ->where('payment_status', 'paid')
            ->whereRaw('UPPER(coupon_code) = ?', [strtoupper($coupon->code)])
            ->count();

        $effectiveGlobalUses = max(
            (int) ($coupon->used_count ?? 0),
            (int) $paidGlobalUses
        );

        if (
            $coupon->usage_limit !== null
            && $effectiveGlobalUses >= (int) $coupon->usage_limit
        ) {
            return $this->failure('This coupon has reached its usage limit.');
        }

        if ($coupon->per_user_usage_limit !== null) {
            if (!$userId) {
                return $this->failure(
                    'Please sign in to use this coupon because it has a per-customer usage limit.'
                );
            }

            $userUses = Order::query()
                ->where('user_id', $userId)
                ->where('payment_status', 'paid')
                ->whereRaw('UPPER(coupon_code) = ?', [strtoupper($coupon->code)])
                ->count();

            if ($userUses >= (int) $coupon->per_user_usage_limit) {
                return $this->failure(
                    'You have already used this coupon the maximum number of times allowed.'
                );
            }
        }

        $eligibleSubtotal = $this->eligibleSubtotal($coupon, $cart);

        if ($eligibleSubtotal <= 0) {
            return $this->failure(
                'This coupon does not apply to any eligible item in your cart.'
            );
        }

        $value = max(0, (float) $coupon->value);

        $discount = match ($coupon->type) {
            'percentage' => $eligibleSubtotal
                * (min(100, $value) / 100),
            'fixed' => $value,
            default => 0,
        };

        $discount = min($eligibleSubtotal, max(0, $discount));

        if (
            $coupon->maximum_discount !== null
            && (float) $coupon->maximum_discount > 0
        ) {
            $discount = min(
                $discount,
                (float) $coupon->maximum_discount
            );
        }

        $discount = round(min($cartSubtotal, $discount), 2);

        if ($discount <= 0) {
            return $this->failure(
                'This coupon does not provide a valid discount for the current cart.'
            );
        }

        $session = [
            'id' => (int) $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
            'minimum_order_amount' =>
                (float) ($coupon->minimum_order_amount ?? 0),
            'maximum_discount' => $coupon->maximum_discount !== null
                ? (float) $coupon->maximum_discount
                : null,
            'target_type' => $coupon->target_type ?: 'all',
            'eligible_subtotal' => round($eligibleSubtotal, 2),
            'discount' => $discount,
        ];

        return [
            'valid' => true,
            'message' => 'Coupon applied successfully.',
            'coupon' => $coupon,
            'cart_subtotal' => $cartSubtotal,
            'eligible_subtotal' => round($eligibleSubtotal, 2),
            'discount' => $discount,
            'session' => $session,
        ];
    }

    private function eligibleSubtotal(
        Coupon $coupon,
        array $cart
    ): float {
        $target = $coupon->target_type ?: 'all';

        if ($target === 'all') {
            return $this->cartSubtotal($cart);
        }

        $productIds = collect($cart)
            ->pluck('product_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $products = Product::query()
            ->with('categories:id,parent_id,title')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $allowedProducts = collect($coupon->product_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->all();

        $allowedVariants = collect($coupon->variant_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->all();

        $allowedCategories = collect($coupon->category_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->all();

        if (
            $target === 'categories'
            && $coupon->include_child_categories
            && !empty($allowedCategories)
        ) {
            $allowedCategories = $this->expandCategoryIds(
                $allowedCategories
            );
        }

        $eligible = 0.0;

        foreach ($cart as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $variantId = (int) ($item['variant_id'] ?? 0);
            $matches = false;

            if ($target === 'products') {
                $matches = in_array(
                    $productId,
                    $allowedProducts,
                    true
                );
            } elseif ($target === 'variants') {
                $matches = $variantId > 0
                    && in_array(
                        $variantId,
                        $allowedVariants,
                        true
                    );
            } elseif ($target === 'categories') {
                $product = $products->get($productId);

                if ($product) {
                    $productCategoryIds = $product->categories
                        ->pluck('id')
                        ->map(fn ($id) => (int) $id)
                        ->all();

                    $matches = !empty(
                        array_intersect(
                            $productCategoryIds,
                            $allowedCategories
                        )
                    );
                }
            }

            if (!$matches) {
                continue;
            }

            $price = max(0, (float) ($item['price'] ?? 0));
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $eligible += $price * $quantity;
        }

        return round($eligible, 2);
    }

    private function expandCategoryIds(array $rootIds): array
    {
        $all = collect($rootIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $frontier = $all;

        while ($frontier->isNotEmpty()) {
            $children = \App\Models\ProductCategory::query()
                ->whereIn('parent_id', $frontier->all())
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->diff($all)
                ->values();

            if ($children->isEmpty()) {
                break;
            }

            $all = $all->merge($children)->unique()->values();
            $frontier = $children;
        }

        return $all->all();
    }

    private function cartSubtotal(array $cart): float
    {
        return round(
            collect($cart)->sum(
                fn ($item) =>
                    max(0, (float) ($item['price'] ?? 0))
                    * max(1, (int) ($item['quantity'] ?? 1))
            ),
            2
        );
    }

    private function failure(
        string $message,
        bool $clearable = true
    ): array {
        return [
            'valid' => false,
            'message' => $message,
            'clearable' => $clearable,
            'coupon' => null,
            'cart_subtotal' => 0.0,
            'eligible_subtotal' => 0.0,
            'discount' => 0.0,
            'session' => null,
        ];
    }
}
