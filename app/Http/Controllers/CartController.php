<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Display the shopping cart.
     */
    public function index(): View
    {
        $cart = session()->get('cart', []);

        return view('cart.index', compact('cart'));
    }

    /**
     * Add a product or variant to the cart.
     */
    public function add(Request $request): JsonResponse|RedirectResponse
    {
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
            ->findOrFail($validated['product_id']);

        $variant = null;

        if (!empty($validated['variant_id'])) {
            $variant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->findOrFail($validated['variant_id']);
        }

        $quantity = (int) $validated['quantity'];

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

        $selectedOptions = $this->prepareSelectedOptions(
            $product,
            $validated['product_options']
                ?? $validated['options']
                ?? []
        );

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

        $image = $variant && !empty($variant->image)
            ? $variant->image
            : $product->featured_image;

        $sku = $variant?->sku
            ?: $product->sku;

        $cart = session()->get('cart', []);

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

            $cart[$cartKey]['quantity'] = $newQuantity;
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

        session()->put('cart', $cart);

        $product->increment('cart_count');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart.',
                'cart_count' => $this->cartCount($cart),
                'cart_subtotal' => $this->cartSubtotal($cart),
                'cart' => $cart,
            ]);
        }

        if (!empty($validated['buy_now'])) {
            return redirect()
                ->route('checkout.index')
                ->with('success', 'Product added to cart.');
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Product added to cart.');
    }

    /**
     * Update one cart item or multiple cart items.
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

        $cart = session()->get('cart', []);

        /*
         * AJAX update for one cart item.
         */
        if (
            !empty($validated['cart_key'])
            && isset($validated['quantity'])
        ) {
            $cartKey = $validated['cart_key'];

            if (!isset($cart[$cartKey])) {
                return $this->cartError(
                    $request,
                    'Cart item was not found.',
                    404
                );
            }

            $quantity = (int) $validated['quantity'];

            $availableStock = (int) (
                $cart[$cartKey]['stock'] ?? 0
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

            $cart[$cartKey]['quantity'] = $quantity;

            session()->put('cart', $cart);

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
                'cart_count' => $this->cartCount($cart),
                'cart_subtotal' => $this->cartSubtotal($cart),
            ]);
        }

        /*
         * Standard form update for all cart items.
         */
        foreach (
            $validated['quantities'] ?? []
            as $cartKey => $quantity
        ) {
            if (!isset($cart[$cartKey])) {
                continue;
            }

            $quantity = max(1, (int) $quantity);

            $availableStock = (int) (
                $cart[$cartKey]['stock'] ?? 0
            );

            if (
                $availableStock > 0
                && $quantity > $availableStock
            ) {
                $quantity = $availableStock;
            }

            $cart[$cartKey]['quantity'] = $quantity;
        }

        session()->put('cart', $cart);

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

        $cart = session()->get('cart', []);

        $cartKey = $validated['cart_key'] ?? null;

        /*
         * Backward compatibility for old product_id remove buttons.
         */
        if (
            empty($cartKey)
            && !empty($validated['product_id'])
        ) {
            foreach ($cart as $key => $item) {
                if (
                    (int) ($item['product_id'] ?? 0)
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

        session()->put('cart', $cart);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product removed from cart.',
                'cart_key' => $cartKey,
                'cart_count' => $this->cartCount($cart),
                'cart_subtotal' => $this->cartSubtotal($cart),
                'cart_empty' => empty($cart),
            ]);
        }

        return back()->with(
            'success',
            'Product removed from cart.'
        );
    }

    /**
     * Convert submitted option value IDs into readable option data.
     */
    private function prepareSelectedOptions(
        Product $product,
        array $submittedOptions
    ): array {
        if (empty($submittedOptions)) {
            return [];
        }

        $selectedOptions = [];

        foreach ($submittedOptions as $optionId => $valueId) {
            $option = $product->options
                ->firstWhere('id', (int) $optionId);

            $value = $product->optionValues
                ->firstWhere('id', (int) $valueId);

            if (!$option || !$value) {
                continue;
            }

            $selectedOptions[] = [
                'option_id' => (int) $option->id,
                'option_name' => $option->name,
                'value_id' => (int) $value->id,
                'value_label' => $value->label
                    ?: $value->value,
            ];
        }

        return $selectedOptions;
    }

    /**
     * Generate a unique key for product, variant and selected options.
     */
    private function createCartKey(
        int $productId,
        ?int $variantId,
        array $options
    ): string {
        $optionValues = collect($options)
            ->sortBy('option_id')
            ->map(function ($option) {
                return $option['option_id']
                    . ':'
                    . $option['value_id'];
            })
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
     * Return the total quantity of all items.
     */
    private function cartCount(array $cart): int
    {
        return collect($cart)->sum(function ($item) {
            return (int) ($item['quantity'] ?? 0);
        });
    }

    /**
     * Return the cart subtotal.
     */
    private function cartSubtotal(array $cart): float
    {
        return round(
            collect($cart)->sum(function ($item) {
                return (float) ($item['price'] ?? 0)
                    * (int) ($item['quantity'] ?? 0);
            }),
            2
        );
    }

    /**
     * Return an AJAX or standard form error.
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