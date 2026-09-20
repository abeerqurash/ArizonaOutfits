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

        /*
         * Never trust stock or pricing that was stored in the session
         * when the item was originally added. Inventory and prices may
         * have changed since then.
         */
        $syncResult = $this->synchronizeCartState($cart);
        $cart = $syncResult['cart'];

        session()->put('cart', $cart);

        $this->validateAppliedCoupon($cart);

        if (!empty($syncResult['messages'])) {
            session()->flash(
                'cart_warning',
                implode(' ', $syncResult['messages'])
            );
        }

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
    ): JsonResponse|RedirectResponse {
        $validated = $request->validate([
            'coupon_code' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return $this->couponError(
                $request,
                'You cannot apply a coupon to an empty cart.'
            );
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

            return $this->couponError(
                $request,
                'The coupon code is invalid or inactive.'
            );
        }

        $now = Carbon::now();

        /*
         * Your database uses start_date, not starts_at.
         */
        if (
            $coupon->start_date
            && $now->lt($coupon->start_date)
        ) {
            return $this->couponError(
                $request,
                'This coupon is not active yet.'
            );
        }

        /*
         * Your database uses end_date, not expires_at.
         */
        if (
            $coupon->end_date
            && $now->gt($coupon->end_date)
        ) {
            return $this->couponError(
                $request,
                'This coupon has expired.'
            );
        }

        $subtotal = $this->cartSubtotal($cart);

        $minimumOrderAmount = (float) (
            $coupon->minimum_order_amount ?? 0
        );

        if (
            $minimumOrderAmount > 0
            && $subtotal < $minimumOrderAmount
        ) {
            return $this->couponError(
                $request,
                'A minimum order amount of $'
                    . number_format(
                        $minimumOrderAmount,
                        2
                    )
                    . ' is required for this coupon.'
            );
        }

        $discount = $this->calculateCouponDiscount(
            $coupon,
            $subtotal
        );

        if ($discount <= 0) {
            return $this->couponError(
                $request,
                'This coupon does not provide a valid discount.'
            );
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

        if ($request->expectsJson()) {
            $total = max(0, $subtotal - $discount);

            return response()->json([
                'success' => true,
                'message' => 'Coupon applied successfully.',
                'coupon' => session('cart_coupon'),
                'cart_subtotal' => $subtotal,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => round($total, 2),
                'cart_count' => $this->cartCount($cart),
            ]);
        }

        return back()->with(
            'success',
            'Coupon applied successfully.'
        );
    }

    /**
     * Remove the applied coupon.
     */
    public function removeCoupon(
        Request $request
    ): JsonResponse|RedirectResponse {
        session()->forget('cart_coupon');

        if ($request->expectsJson()) {
            $cart = session()->get('cart', []);
            $subtotal = $this->cartSubtotal($cart);

            return response()->json([
                'success' => true,
                'message' => 'Coupon removed successfully.',
                'coupon' => null,
                'cart_subtotal' => $subtotal,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total' => $subtotal,
                'cart_count' => $this->cartCount($cart),
            ]);
        }

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
                fn($id) => (int) $id
            )
            ->values();

        $selectedOptionIds = collect(
            $selectedOptions
        )
            ->pluck('option_id')
            ->map(
                fn($id) => (int) $id
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
                'The requested quantity is not currently available.'
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
                    'The requested quantity is not currently available.'
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

            $product = Product::query()
                ->whereKey(
                    $cart[$cartKey]['product_id']
                )
                ->where('status', 'active')
                ->first();

            if (!$product) {
                return $this->cartError(
                    $request,
                    'This product no longer exists.',
                    404
                );
            }

            $variant = null;

            if (!empty($cart[$cartKey]['variant_id'])) {
                $variant = ProductVariant::query()
                    ->whereKey(
                        $cart[$cartKey]['variant_id']
                    )
                    ->where(
                        'product_id',
                        $product->id
                    )
                    ->first();

                if (!$variant) {
                    return $this->cartError(
                        $request,
                        'The selected product variant no longer exists.',
                        404
                    );
                }
            }

            $availableStock = $variant
                ? (int) $variant->stock
                : (int) $product->stock;

            if ($availableStock < 1) {
                return $this->cartError(
                    $request,
                    'This product is now out of stock.'
                );
            }

            if ($quantity > $availableStock) {
                return $this->cartError(
                    $request,
                    'The requested quantity is not currently available.'
                );
            }

            $cart[$cartKey]['quantity'] = $quantity;

            /*
 * Refresh session stock in case it changed.
 */
            $cart[$cartKey]['stock'] = $availableStock;

            session()->put(
                'cart',
                $cart
            );

            $this->validateAppliedCoupon($cart);

            $cartSubtotal = $this->cartSubtotal($cart);
            $appliedCoupon = session('cart_coupon', []);
            $discount = min(
                $cartSubtotal,
                max(
                    0,
                    (float) ($appliedCoupon['discount'] ?? 0)
                )
            );
            $total = max(
                0,
                $cartSubtotal - $discount
            );

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

                /*
                 * Keep both names for compatibility:
                 * - cart_subtotal is used by the header drawer.
                 * - subtotal / discount / total are used by the full cart page.
                 */
                'cart_subtotal' => $cartSubtotal,
                'subtotal' => $cartSubtotal,
                'discount' => round($discount, 2),
                'total' => round($total, 2),
                'coupon' => $appliedCoupon,
            ]);
        }

        $bulkQuantities =
            $validated['quantities'] ?? [];

        if (empty($bulkQuantities)) {
            return back()->withErrors([
                'cart' => 'No cart quantities were provided.',
            ]);
        }

        /*
         * Bulk Update Cart must follow the same inventory rules as the
         * AJAX quantity controls. Never silently delete an unavailable
         * product or variant from the customer's cart.
         */
        $syncResult = $this->synchronizeCartState($cart);
        $cart = $syncResult['cart'];

        $messages = $syncResult['messages'];

        foreach (
            $bulkQuantities
            as $cartKey => $requestedQuantity
        ) {
            if (!isset($cart[$cartKey])) {
                continue;
            }

            $availableStock = max(
                0,
                (int) ($cart[$cartKey]['stock'] ?? 0)
            );

            $isUnavailable =
                !empty($cart[$cartKey]['unavailable'])
                || $availableStock < 1;

            /*
             * Keep unavailable items visible at quantity 1. The cart page
             * will explain why checkout is blocked and lets the customer
             * explicitly remove the item.
             */
            if ($isUnavailable) {
                $cart[$cartKey]['quantity'] = max(
                    1,
                    (int) ($cart[$cartKey]['quantity'] ?? 1)
                );

                continue;
            }

            $requestedQuantity = max(
                1,
                (int) $requestedQuantity
            );

            if ($requestedQuantity > $availableStock) {
                $cart[$cartKey]['quantity'] =
                    $availableStock;

                $title =
                    $cart[$cartKey]['title']
                    ?? 'An item';

                $messages[] =
                    $title
                    . ' was adjusted to '
                    . $availableStock
                    . ' because that is the current available stock.';

                continue;
            }

            $cart[$cartKey]['quantity'] =
                $requestedQuantity;
        }

        session()->put(
            'cart',
            $cart
        );

        $this->validateAppliedCoupon($cart);

        $messages = array_values(
            array_unique(
                array_filter($messages)
            )
        );

        if (!empty($messages)) {
            return back()
                ->with(
                    'cart_warning',
                    implode(' ', $messages)
                )
                ->with(
                    'success',
                    'Cart updated with the latest stock availability.'
                );
        }

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
            $cartSubtotal = $this->cartSubtotal($cart);
            $appliedCoupon = session('cart_coupon', []);
            $discount = min(
                $cartSubtotal,
                max(
                    0,
                    (float) ($appliedCoupon['discount'] ?? 0)
                )
            );
            $total = max(
                0,
                $cartSubtotal - $discount
            );

            return response()->json([
                'success' => true,
                'message' =>
                'Product removed from cart.',
                'cart_key' => $cartKey,
                'cart_count' =>
                $this->cartCount($cart),
                'cart_subtotal' => $cartSubtotal,
                'subtotal' => $cartSubtotal,
                'discount' => round($discount, 2),
                'total' => round($total, 2),
                'cart_empty' =>
                empty($cart),
                'coupon' => $appliedCoupon,
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
                    $normalized[(string) $key] = (string) $option;
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
                $normalized[(string) $optionId] = (string) $valueId;
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
     * Refresh cart inventory, price and product details from the database.
     *
     * Unavailable items are deliberately kept in the cart with stock = 0
     * instead of being silently deleted. The cart page can then explain the
     * problem to the customer and prevent checkout until it is resolved.
     */
    private function synchronizeCartState(array $cart): array
    {
        $messages = [];
        $stockAdjusted = false;
        $availabilityChanged = false;
        $priceChanged = false;

        foreach ($cart as $cartKey => &$item) {
            $productId = (int) ($item['product_id'] ?? 0);

            $product = Product::query()
                ->whereKey($productId)
                ->where('status', 'active')
                ->first();

            if (!$product) {
                $item['stock'] = 0;
                $item['unavailable'] = true;
                $item['unavailable_reason'] =
                    'This product is no longer available.';

                $availabilityChanged = true;
                continue;
            }

            $variant = null;
            $variantId = (int) ($item['variant_id'] ?? 0);

            if ($variantId > 0) {
                $variant = ProductVariant::query()
                    ->whereKey($variantId)
                    ->where('product_id', $product->id)
                    ->first();

                if (!$variant) {
                    $item['stock'] = 0;
                    $item['unavailable'] = true;
                    $item['unavailable_reason'] =
                        'The selected product variation is no longer available.';

                    $availabilityChanged = true;
                    continue;
                }
            }

            $availableStock = $variant
                ? (int) $variant->stock
                : (int) $product->stock;

            $item['stock'] = max(0, $availableStock);

            if ($availableStock < 1) {
                $item['unavailable'] = true;
                $item['unavailable_reason'] =
                    'This item is currently out of stock.';

                $availabilityChanged = true;
            } else {
                unset(
                    $item['unavailable'],
                    $item['unavailable_reason']
                );

                $currentQuantity = max(
                    1,
                    (int) ($item['quantity'] ?? 1)
                );

                if ($currentQuantity > $availableStock) {
                    $item['quantity'] = $availableStock;
                    $stockAdjusted = true;
                } else {
                    $item['quantity'] = $currentQuantity;
                }
            }

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

            if (
                round((float) ($item['price'] ?? 0), 2)
                !== round($price, 2)
            ) {
                $priceChanged = true;
            }

            $item['title'] = $product->title;
            $item['slug'] = $product->slug;
            $item['sku'] = $variant?->sku ?: $product->sku;
            $item['image'] = (
                $variant
                && !empty($variant->image)
            )
                ? $variant->image
                : $product->featured_image;
            $item['regular_price'] = $regularPrice;
            $item['sale_price'] = $salePrice;
            $item['price'] = $price;
        }

        unset($item);

        if ($availabilityChanged) {
            $messages[] =
                'Some cart items are no longer available or are out of stock.';
        }

        if ($stockAdjusted) {
            $messages[] =
                'A quantity was adjusted to match the latest available stock.';
        }

        if ($priceChanged) {
            $messages[] =
                'One or more product prices were updated to the latest price.';
        }

        return [
            'cart' => $cart,
            'messages' => $messages,
        ];
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
     * Return coupon errors as JSON for AJAX or as normal Laravel
     * validation errors for non-JavaScript submissions.
     */
    private function couponError(
        Request $request,
        string $message,
        int $status = 422
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => [
                    'coupon_code' => [$message],
                ],
            ], $status);
        }

        return back()
            ->withInput()
            ->withErrors([
                'coupon_code' => $message,
            ]);
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
