<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Display the shopping cart.
     */
    public function index(): View
    {
        $cart = session()->get('cart', []);

        $this->validateAppliedCoupon($cart);

        return view(
            'cart.index',
            compact('cart')
        );
    }

    /**
     * Apply a coupon to the current cart.
     */
    public function applyCoupon(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'coupon_code' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return back()->withErrors([
                'coupon_code' =>
                    'You cannot apply a coupon to an empty cart.',
            ]);
        }

        $code = strtoupper(
            trim($validated['coupon_code'])
        );

        /*
         * Your coupons table uses "status", not "is_active".
         */
        $coupon = Coupon::query()
            ->whereRaw(
                'UPPER(code) = ?',
                [$code]
            )
            ->where('status', true)
            ->first();

        if (!$coupon) {
            session()->forget('cart_coupon');

            return back()
                ->withInput()
                ->withErrors([
                    'coupon_code' =>
                        'The coupon code is invalid or inactive.',
                ]);
        }

        $now = Carbon::now();

        /*
         * Your database uses start_date, not starts_at.
         */
        if (
            $coupon->start_date
            && $now->lt($coupon->start_date)
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'coupon_code' =>
                        'This coupon is not active yet.',
                ]);
        }

        /*
         * Your database uses end_date, not expires_at.
         */
        if (
            $coupon->end_date
            && $now->gt($coupon->end_date)
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'coupon_code' =>
                        'This coupon has expired.',
                ]);
        }

        $subtotal = $this->cartSubtotal($cart);

        $minimumOrderAmount = (float) (
            $coupon->minimum_order_amount ?? 0
        );

        if (
            $minimumOrderAmount > 0
            && $subtotal < $minimumOrderAmount
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'coupon_code' =>
                        'A minimum order amount of $'
                        . number_format(
                            $minimumOrderAmount,
                            2
                        )
                        . ' is required for this coupon.',
                ]);
        }

        $discount = $this->calculateCouponDiscount(
            $coupon,
            $subtotal
        );

        if ($discount <= 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'coupon_code' =>
                        'This coupon does not provide a valid discount.',
                ]);
        }

        session()->put('cart_coupon', [
            'id' => (int) $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
            'minimum_order_amount' =>
                (float) ($coupon->minimum_order_amount ?? 0),
            'discount' => $discount,
        ]);

        return back()->with(
            'success',
            'Coupon applied successfully.'
        );
    }

    /**
     * Remove the applied coupon.
     */
    public function removeCoupon(): RedirectResponse
    {
        session()->forget('cart_coupon');

        return back()->with(
            'success',
            'Coupon removed successfully.'
        );
    }

    /**
     * Add a product or product variant to the cart.
     */
    public function add(
        Request $request
    ): JsonResponse|RedirectResponse {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'variant_id' => [
                'nullable',
                'integer',
                'exists:product_variants,id',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'product_options' => [
                'nullable',
                'array',
            ],

            'product_options.*' => [
                'nullable',
            ],

            'options' => [
                'nullable',
                'array',
            ],

            'buy_now' => [
                'nullable',
                'boolean',
            ],
        ]);

        $product = Product::query()
            ->with([
                'images',
                'variants',
                'options',
                'optionValues',
            ])
            ->where('status', 'active')
            ->findOrFail(
                $validated['product_id']
            );

        $submittedOptions =
            $validated['product_options']
            ?? $validated['options']
            ?? [];

        $selectedOptions =
            $this->prepareSelectedOptions(
                $product,
                $submittedOptions
            );

        $hasVariants =
            $product->variants->isNotEmpty();

        $requiredOptionIds = $product->options
            ->pluck('id')
            ->map(
                fn ($id) => (int) $id
            )
            ->values();

        $selectedOptionIds = collect(
            $selectedOptions
        )
            ->pluck('option_id')
            ->map(
                fn ($id) => (int) $id
            )
            ->unique()
            ->values();

        $missingOptionIds =
            $requiredOptionIds->diff(
                $selectedOptionIds
            );

        $variant = null;

        if (
            $hasVariants
            && (
                $requiredOptionIds->isEmpty()
                || $missingOptionIds->isNotEmpty()
                || $selectedOptionIds->count()
                    !== $requiredOptionIds->count()
            )
        ) {
            return $this->cartError(
                $request,
                'Please select one value from every product option.'
            );
        }

        if (
            $hasVariants
            && empty($validated['variant_id'])
        ) {
            return $this->cartError(
                $request,
                'Please select one value from every product option.'
            );
        }

        if (!empty($validated['variant_id'])) {
            $variant = ProductVariant::query()
                ->where(
                    'product_id',
                    $product->id
                )
                ->whereKey(
                    $validated['variant_id']
                )
                ->first();

            if (!$variant) {
                return $this->cartError(
                    $request,
                    'The selected product variant is invalid.'
                );
            }
        }

        if ($hasVariants && $variant) {
            $variantOptions =
                $this->normalizeVariantOptions(
                    $variant->options
                );

            $submittedOptionMap =
                collect($selectedOptions)
                    ->mapWithKeys(
                        function (array $option) {
                            return [
                                (string) $option['option_id']
                                    => (string) $option['value_id'],
                            ];
                        }
                    )
                    ->all();

            ksort($variantOptions);
            ksort($submittedOptionMap);

            if (
                $variantOptions
                !== $submittedOptionMap
            ) {
                return $this->cartError(
                    $request,
                    'The selected option values do not match this product variant.'
                );
            }
        }

        if (
            $hasVariants
            && $variant === null
        ) {
            return $this->cartError(
                $request,
                'Please select all product options before adding this product to your cart.'
            );
        }

        $quantity =
            (int) $validated['quantity'];

        $availableStock = $variant
            ? (int) $variant->stock
            : (int) $product->stock;

        if ($availableStock < 1) {
            return $this->cartError(
                $request,
                'This product is out of stock.'
            );
        }

        if ($quantity > $availableStock) {
            return $this->cartError(
                $request,
                "Only {$availableStock} item(s) are currently available."
            );
        }

        $cartKey = $this->createCartKey(
            $product->id,
            $variant?->id,
            $selectedOptions
        );

        $regularPrice = $variant
            ? (
                $variant->regular_price !== null
                    ? (float) $variant->regular_price
                    : (float) $product->regular_price
            )
            : (float) $product->regular_price;

        $salePrice = $variant
            ? (
                $variant->sale_price !== null
                    ? (float) $variant->sale_price
                    : null
            )
            : (
                $product->sale_price !== null
                    ? (float) $product->sale_price
                    : null
            );

        $price = (
            $salePrice !== null
            && $salePrice < $regularPrice
        )
            ? $salePrice
            : $regularPrice;

        $image = (
            $variant
            && !empty($variant->image)
        )
            ? $variant->image
            : $product->featured_image;

        $sku = $variant?->sku
            ?: $product->sku;

        $cart = session()->get(
            'cart',
            []
        );

        if (isset($cart[$cartKey])) {
            $newQuantity =
                (int) $cart[$cartKey]['quantity']
                + $quantity;

            if ($newQuantity > $availableStock) {
                return $this->cartError(
                    $request,
                    "Only {$availableStock} item(s) are currently available."
                );
            }

            $cart[$cartKey]['quantity'] =
                $newQuantity;

            $cart[$cartKey]['stock'] =
                $availableStock;

            $cart[$cartKey]['price'] =
                $price;

            $cart[$cartKey]['regular_price'] =
                $regularPrice;

            $cart[$cartKey]['sale_price'] =
                $salePrice;
        } else {
            $cart[$cartKey] = [
                'cart_key' => $cartKey,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'sku' => $sku,
                'image' => $image,
                'regular_price' => $regularPrice,
                'sale_price' => $salePrice,
                'price' => $price,
                'quantity' => $quantity,
                'stock' => $availableStock,
                'options' => $selectedOptions,
            ];
        }

        

        session()->put(
            'cart',
            $cart
        );

        /*
         * Revalidate the coupon because the subtotal changed.
         */
        $this->validateAppliedCoupon($cart);

        $product->increment('cart_count');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' =>
                    'Product added to cart.',
                'cart_count' =>
                    $this->cartCount($cart),
                'cart_subtotal' =>
                    $this->cartSubtotal($cart),
                'cart' => $cart,
            ]);
        }

        if (!empty($validated['buy_now'])) {
            return redirect()
                ->route('checkout.index')
                ->with(
                    'success',
                    'Product added to cart.'
                );
        }

        return redirect()
            ->route('cart.index')
            ->with(
                'success',
                'Product added to cart.'
            );
    }

    /**
     * Update one cart item or all cart items.
     */
    public function update(
        Request $request
    ): JsonResponse|RedirectResponse {
        $validated = $request->validate([
            'cart_key' => [
                'nullable',
                'string',
            ],

            'quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'quantities' => [
                'nullable',
                'array',
            ],

            'quantities.*' => [
                'integer',
                'min:1',
            ],
        ]);

        $cart = session()->get(
            'cart',
            []
        );

        if (
            !empty($validated['cart_key'])
            && isset($validated['quantity'])
        ) {
            $cartKey =
                $validated['cart_key'];

            if (!isset($cart[$cartKey])) {
                return $this->cartError(
                    $request,
                    'Cart item was not found.',
                    404
                );
            }

            $quantity =
                (int) $validated['quantity'];

            $availableStock =
                (int) (
                    $cart[$cartKey]['stock']
                    ?? 0
                );

            if (
                $availableStock > 0
                && $quantity > $availableStock
            ) {
                return $this->cartError(
                    $request,
                    "Only {$availableStock} item(s) are currently available."
                );
            }

            $cart[$cartKey]['quantity'] =
                $quantity;

            session()->put(
                'cart',
                $cart
            );

            $this->validateAppliedCoupon($cart);

            return response()->json([
                'success' => true,
                'message' => 'Cart updated.',
                'cart_key' => $cartKey,
                'quantity' => $quantity,
                'item_subtotal' => round(
                    (float) $cart[$cartKey]['price']
                        * $quantity,
                    2
                ),
                'cart_count' =>
                    $this->cartCount($cart),
                'cart_subtotal' =>
                    $this->cartSubtotal($cart),
                'coupon' =>
                    session('cart_coupon'),
            ]);
        }

        foreach (
            $validated['quantities'] ?? []
            as $cartKey => $quantity
        ) {
            if (!isset($cart[$cartKey])) {
                continue;
            }

            $quantity = max(
                1,
                (int) $quantity
            );

            $availableStock =
                (int) (
                    $cart[$cartKey]['stock']
                    ?? 0
                );

            if (
                $availableStock > 0
                && $quantity > $availableStock
            ) {
                $quantity =
                    $availableStock;
            }

            $cart[$cartKey]['quantity'] =
                $quantity;
        }

        session()->put(
            'cart',
            $cart
        );

        $this->validateAppliedCoupon($cart);

        return back()->with(
            'success',
            'Cart updated.'
        );
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(
        Request $request
    ): JsonResponse|RedirectResponse {
        $validated = $request->validate([
            'cart_key' => [
                'required_without:product_id',
                'nullable',
                'string',
            ],

            'product_id' => [
                'required_without:cart_key',
                'nullable',
                'integer',
            ],
        ]);

        $cart = session()->get(
            'cart',
            []
        );

        $cartKey =
            $validated['cart_key'] ?? null;

        if (
            empty($cartKey)
            && !empty($validated['product_id'])
        ) {
            foreach (
                $cart as $key => $item
            ) {
                if (
                    (int) (
                        $item['product_id'] ?? 0
                    )
                    === (int) $validated['product_id']
                ) {
                    $cartKey = $key;
                    break;
                }
            }
        }

        if (
            empty($cartKey)
            || !isset($cart[$cartKey])
        ) {
            return $this->cartError(
                $request,
                'Cart item was not found.',
                404
            );
        }

        unset($cart[$cartKey]);

        session()->put(
            'cart',
            $cart
        );

        if (empty($cart)) {
            session()->forget(
                'cart_coupon'
            );
        } else {
            $this->validateAppliedCoupon(
                $cart
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' =>
                    'Product removed from cart.',
                'cart_key' => $cartKey,
                'cart_count' =>
                    $this->cartCount($cart),
                'cart_subtotal' =>
                    $this->cartSubtotal($cart),
                'cart_empty' =>
                    empty($cart),
                'coupon' =>
                    session('cart_coupon'),
            ]);
        }

        return back()->with(
            'success',
            'Product removed from cart.'
        );
    }

    /**
     * Convert submitted option IDs into readable data.
     */
    private function prepareSelectedOptions(
        Product $product,
        array $submittedOptions
    ): array {
        if (empty($submittedOptions)) {
            return [];
        }

        $selectedOptions = [];

        foreach (
            $submittedOptions
            as $optionId => $valueId
        ) {
            $option = $product->options
                ->firstWhere(
                    'id',
                    (int) $optionId
                );

            $value = $product->optionValues
                ->firstWhere(
                    'id',
                    (int) $valueId
                );

            if (!$option || !$value) {
                continue;
            }

            $selectedOptions[] = [
                'option_id' =>
                    (int) $option->id,
                'option_name' =>
                    $option->name,
                'value_id' =>
                    (int) $value->id,
                'value_label' =>
                    $value->label
                    ?: $value->value,
            ];
        }

        return $selectedOptions;
    }

    /**
     * Normalize product variant option data.
     */
    private function normalizeVariantOptions(
        mixed $rawOptions
    ): array {
        if (is_string($rawOptions)) {
            $decoded = json_decode(
                $rawOptions,
                true
            );

            if (
                json_last_error()
                === JSON_ERROR_NONE
            ) {
                $rawOptions = $decoded;
            }
        }

        if (is_string($rawOptions)) {
            $decoded = json_decode(
                $rawOptions,
                true
            );

            if (
                json_last_error()
                === JSON_ERROR_NONE
            ) {
                $rawOptions = $decoded;
            }
        }

        if (!is_array($rawOptions)) {
            return [];
        }

        $normalized = [];

        foreach (
            $rawOptions
            as $key => $option
        ) {
            if (!is_array($option)) {
                if (
                    $key !== ''
                    && $option !== ''
                ) {
                    $normalized[
                        (string) $key
                    ] = (string) $option;
                }

                continue;
            }

            $optionId =
                $option['option_id']
                ?? $option['product_option_id']
                ?? null;

            $valueId =
                $option['value_id']
                ?? $option['option_value_id']
                ?? $option['product_option_value_id']
                ?? $option['value']
                ?? null;

            if (
                $optionId !== null
                && $valueId !== null
            ) {
                $normalized[
                    (string) $optionId
                ] = (string) $valueId;
            }
        }

        return $normalized;
    }

    /**
     * Create a unique key for a cart item.
     */
    private function createCartKey(
        int $productId,
        ?int $variantId,
        array $options
    ): string {
        $optionValues = collect($options)
            ->sortBy('option_id')
            ->map(
                function ($option) {
                    return
                        $option['option_id']
                        . ':'
                        . $option['value_id'];
                }
            )
            ->implode('|');

        return hash(
            'sha256',
            $productId
                . '|'
                . ($variantId ?? 'default')
                . '|'
                . $optionValues
        );
    }

    /**
     * Count all product quantities in the cart.
     */
    private function cartCount(
        array $cart
    ): int {
        return collect($cart)->sum(
            function ($item) {
                return (int) (
                    $item['quantity'] ?? 0
                );
            }
        );
    }

    /**
     * Calculate the cart subtotal.
     */
    private function cartSubtotal(
        array $cart
    ): float {
        return round(
            collect($cart)->sum(
                function ($item) {
                    return
                        (float) (
                            $item['price'] ?? 0
                        )
                        * (int) (
                            $item['quantity'] ?? 0
                        );
                }
            ),
            2
        );
    }

    /**
     * Calculate coupon discount.
     */
    private function calculateCouponDiscount(
        Coupon $coupon,
        float $subtotal
    ): float {
        $value =
            (float) $coupon->value;

        if (
            $coupon->type === 'percentage'
        ) {
            /*
             * Prevent percentage values greater than 100%.
             */
            $percentage = min(
                100,
                max(0, $value)
            );

            $discount =
                $subtotal
                * ($percentage / 100);
        } elseif (
            $coupon->type === 'fixed'
        ) {
            $discount = max(
                0,
                $value
            );
        } else {
            $discount = 0;
        }

        return round(
            min(
                $subtotal,
                $discount
            ),
            2
        );
    }

    /**
     * Revalidate a coupon after cart changes.
     */
    private function validateAppliedCoupon(
        array $cart
    ): void {
        $appliedCoupon =
            session('cart_coupon');

        if (
            !is_array($appliedCoupon)
            || empty($appliedCoupon['id'])
        ) {
            return;
        }

        if (empty($cart)) {
            session()->forget(
                'cart_coupon'
            );

            return;
        }

        $coupon = Coupon::query()
            ->find(
                $appliedCoupon['id']
            );

        if (
            !$coupon
            || !$coupon->status
        ) {
            session()->forget(
                'cart_coupon'
            );

            return;
        }

        $now = Carbon::now();

        if (
            $coupon->start_date
            && $now->lt(
                $coupon->start_date
            )
        ) {
            session()->forget(
                'cart_coupon'
            );

            return;
        }

        if (
            $coupon->end_date
            && $now->gt(
                $coupon->end_date
            )
        ) {
            session()->forget(
                'cart_coupon'
            );

            return;
        }

        $subtotal =
            $this->cartSubtotal($cart);

        $minimumOrderAmount =
            (float) (
                $coupon->minimum_order_amount
                ?? 0
            );

        if (
            $minimumOrderAmount > 0
            && $subtotal
                < $minimumOrderAmount
        ) {
            session()->forget(
                'cart_coupon'
            );

            return;
        }

        $discount =
            $this->calculateCouponDiscount(
                $coupon,
                $subtotal
            );

        if ($discount <= 0) {
            session()->forget(
                'cart_coupon'
            );

            return;
        }

        session()->put(
            'cart_coupon',
            [
                'id' =>
                    (int) $coupon->id,
                'code' =>
                    $coupon->code,
                'type' =>
                    $coupon->type,
                'value' =>
                    (float) $coupon->value,
                'minimum_order_amount' =>
                    (float) (
                        $coupon->minimum_order_amount
                        ?? 0
                    ),
                'discount' =>
                    $discount,
            ]
        );
    }

    /**
     * Return JSON or regular validation errors.
     */
    private function cartError(
        Request $request,
        string $message,
        int $status = 422
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return back()
            ->withInput()
            ->withErrors([
                'cart' => $message,
            ]);
    }
}