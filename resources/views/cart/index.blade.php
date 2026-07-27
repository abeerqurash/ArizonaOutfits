@extends('layouts.app')

@section('title', 'Shopping Cart')

@section(
'meta_description',
'Review your shopping cart, update product quantities, apply a coupon and continue securely to checkout.'
)

@section('content')

@php
$subtotal = collect($cart)->sum(function ($item) {
return
(float) ($item['price'] ?? 0)
* max(
1,
(int) ($item['quantity'] ?? 1)
);
});

$cartCount = collect($cart)->sum(function ($item) {
return max(
1,
(int) ($item['quantity'] ?? 1)
);
});

$coupon = session('cart_coupon', []);

$couponCode = $coupon['code'] ?? '';

$couponDiscount = min(
$subtotal,
max(
0,
(float) ($coupon['discount'] ?? 0)
)
);

$shipping = 0;
$tax = 0;

$total = max(
0,
$subtotal
- $couponDiscount
+ $shipping
+ $tax
);
@endphp

<div class="page-wrapper">

    <div class="strip-wrapper">
        <div class="wrapper">
            <div class="strip-container">
                <div class="pinstrip"></div>
                <div class="pinstrip"></div>
                <div class="pinstrip"></div>
                <div class="pinstrip"></div>
            </div>
        </div>
    </div>

    <div class="services">
        <div class="service-wrapper">
            <div class="container">
                <div class="cart-page" data-cart-container>
                    <ul class="bread-crumbs list-style-none fs-12 text-uppercase letter-space-4px mb-10px d-flex gap-10px">
                        <li>
                            <a
                                href="{{ route('home-page') }}"
                                class="text-decoration-none text-color-dark">
                                Home
                            </a>
                        </li>

                        <li aria-current="page">
                            Cart
                        </li>
                    </ul>

                    <div class="cart-page-header mb-20px">
                        <h1 class="fs-48 text-color-dark mb-10px">
                            Shopping Cart
                        </h1>

                        @if ($cartCount > 0)
                        <p class="fs-12 text-uppercase letter-space-4px">
                            {{ $cartCount }}
                            {{ $cartCount === 1 ? 'item' : 'items' }}
                            in your cart
                        </p>
                        @endif
                    </div>

                    {{-- Success message --}}
                    @if (session('success'))
                    <div class="alert alert-success mb-20px">
                        {{ session('success') }}
                    </div>
                    @endif

                    {{-- General error message --}}
                    @if (session('error'))
                    <div class="alert alert-danger mb-20px">
                        {{ session('error') }}
                    </div>
                    @endif

                    {{-- Cart error --}}
                    @if ($errors->has('cart'))
                    <div class="alert alert-danger mb-20px">
                        {{ $errors->first('cart') }}
                    </div>
                    @endif

                    {{-- Coupon error --}}
                    @if ($errors->has('coupon_code'))
                    <div class="alert alert-danger mb-20px">
                        {{ $errors->first('coupon_code') }}
                    </div>
                    @endif

                    <div class="cart-parent">

                        <div class="cart-products">

                            <form
                                action="{{ route('cart.update') }}"
                                method="POST"
                                id="cart-update-form">
                                @csrf

                                @forelse ($cart as $cartKey => $item)

                                @php
                                $productId = (int) ($item['product_id'] ?? 0);

                                $variantId = !empty($item['variant_id'])
                                ? (int) $item['variant_id']
                                : null;

                                $title = $item['title']
                                ?? 'Product';

                                $slug = $item['slug']
                                ?? null;

                                $sku = $item['sku']
                                ?? null;

                                $price = (float) ($item['price'] ?? 0);

                                $regularPrice = (float) (
                                $item['regular_price']
                                ?? $price
                                );

                                $salePrice = isset($item['sale_price'])
                                ? (float) $item['sale_price']
                                : null;

                                $quantity = max(
                                1,
                                (int) ($item['quantity'] ?? 1)
                                );

                                $stock = max(
                                0,
                                (int) ($item['stock'] ?? 0)
                                );

                                $lineTotal = $price * $quantity;

                                $hasDiscount =
                                $regularPrice > 0
                                && $price > 0
                                && $price < $regularPrice;

                                    $discountPercentage=$hasDiscount
                                    ? (int) round(
                                    (($regularPrice - $price) / $regularPrice) * 100
                                    )
                                    : 0;

                                    $options=is_array($item['options'] ?? null)
                                    ? $item['options']
                                    : [];

                                    $image=$item['image']
                                    ?? 'asset/images/no-image.jpg' ;

                                    if (
                                    !str_starts_with($image, 'http://' )
                                    && !str_starts_with($image, 'https://' )
                                    ) {
                                    $normalizedImage=ltrim(
                                    $image, '/'
                                    );

                                    if (
                                    str_starts_with(
                                    $normalizedImage, 'storage/'
                                    )
                                    ) {
                                    $image=asset(
                                    $normalizedImage
                                    );
                                    } elseif (
                                    file_exists(
                                    public_path(
                                    $normalizedImage
                                    )
                                    )
                                    ) {
                                    $image=asset(
                                    $normalizedImage
                                    );
                                    } else {
                                    $image=asset( 'storage/' . $normalizedImage
                                    );
                                    }
                                    }

                                    $productUrl=$slug
                                    ? route( 'products.show' ,
                                    $slug
                                    )
                                    : route('products.index');
                                    @endphp

                                    <article
                                    class="cart-item"
                                    data-cart-item
                                    data-cart-key="{{ $cartKey }}"
                                    data-product-id="{{ $productId }}"
                                    data-variant-id="{{ $variantId }}"
                                    data-unit-price="{{ $price }}">
                                    {{-- These values are informational only.
                                         The server must verify them from the database/session. --}}
                                    <input
                                        type="hidden"
                                        name="items[{{ $cartKey }}][product_id]"
                                        value="{{ $productId }}">

                                    <input
                                        type="hidden"
                                        name="items[{{ $cartKey }}][variant_id]"
                                        value="{{ $variantId }}">

                                    <input
                                        type="hidden"
                                        name="items[{{ $cartKey }}][cart_key]"
                                        value="{{ $cartKey }}">

                                    <a
                                        href="{{ $productUrl }}"
                                        class="cart-item-image d-flex align-items-start gap-10px mb-20px  text-decoration-none">
                                        <img
                                            src="{{ $image }}"
                                            width="110"
                                            height="130"
                                            loading="lazy"
                                            alt="{{ $title }}">

                                        @if ($discountPercentage > 0)
                                        <span class="cart-discount-badge fs-12 text-uppercase letter-space-4px">
                                            -{{ $discountPercentage }}%
                                        </span>
                                        @endif
                                    </a>

                                    <div class="cart-item-details d-flex gap-20px mb-20px">

                                        <div class="cart-item-heading w-100 d-flex flex-wrap gap-10px">

                                            <div class="w-100 d-flex flex-column gap-10px">
                                                <h3 class="fs-18 text-capitalize text-color-dark mb-5px">
                                                    <a
                                                        href="{{ $productUrl }}"
                                                        class="text-decoration-none text-color-dark">
                                                        {{ $title }}
                                                    </a>
                                                </h3>

                                                @if ($sku)
                                                <p class="cart-item-sku fs-12 text-uppercase letter-space-3px">
                                                    SKU: {{ $sku }}
                                                </p>
                                                @endif
                                            </div>

                                            <button
                                                type="submit"
                                                formaction="{{ route('cart.remove') }}"
                                                name="cart_key"
                                                value="{{ $cartKey }}"
                                                class="cart-remove-button w-100 btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                                                data-ajax="true"
                                                data-cart-key="{{ $cartKey }}"
                                                data-remove-url="{{ route('cart.remove') }}"
                                                aria-label="Remove {{ $title }} from cart">
                                                <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Remove</div>

                                            </button>

                                        </div>

                                        {{-- Selected product variant options --}}
                                        @if (!empty($options))
                                        <div class="cart-item-options w-100 mb-10px">

                                            @foreach ($options as $option)

                                            @php
                                            $optionName =
                                            $option['option_name']
                                            ?? $option['name']
                                            ?? 'Option';

                                            $optionValue =
                                            $option['value_label']
                                            ?? $option['value']
                                            ?? '';
                                            @endphp

                                            @if ($optionValue !== '')
                                            <p class="fs-12 text-uppercase letter-space-3px mb-10px">
                                                {{ $optionName }}:

                                                {{ $optionValue }}
                                            </p>
                                            @endif

                                            @endforeach

                                        </div>
                                        @endif

                                        <div class="cart-item-pricing w-100 d-flex gap-10px flex-column">

                                            <div class="cart-unit-price d-flex flex-column gap-10px">
                                                <span class="fs-12 text-uppercase letter-space-3px">
                                                    Unit price
                                                </span>

                                                <div class="fs-12 text-uppercase letter-space-3px">
                                                    <span class="cart-current-price ">
                                                        ${{ number_format($price, 2) }}
                                                    </span>

                                                    @if ($hasDiscount)
                                                    <del class="cart-regular-price">
                                                        ${{ number_format($regularPrice, 2) }}
                                                    </del>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="cart-stock-information fs-12 text-uppercase letter-space-3px d-flex flex-column gap-10px">
                                                @if ($stock > 0)
                                                <span class="cart-stock-badge in-stock">
                                                    In stock
                                                </span>

                                                <span>
                                                    {{ $stock }} available
                                                </span>
                                                @else
                                                <span class="cart-stock-badge out-of-stock">
                                                    Out of stock
                                                </span>
                                                @endif
                                            </div>

                                        </div>

                                        <div class="cart-item-actions w-100">

                                            <div
                                                class="quantity-box cart-quantity-box"
                                                data-cart-quantity-wrapper>
                                                <button
                                                    type="button"
                                                    class="quantity-button cart-quantity-minus btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                                                    data-cart-quantity-minus
                                                    aria-label="Decrease quantity">
                                                    <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                                            −
</div>
                                                </button>

                                                <input
                                                    type="number"
                                                    name="quantities[{{ $cartKey }}]"
                                                    class="cart-quantity-input"
                                                    value="{{ $quantity }}"
                                                    min="1"
                                                    max="{{ max(1, $stock) }}"
                                                    data-cart-key="{{ $cartKey }}"
                                                    data-unit-price="{{ $price }}"
                                                    data-update-url="{{ route('cart.update') }}"
                                                    aria-label="Quantity for {{ $title }}">

                                                <button
                                                    type="button"
                                                    class="quantity-button cart-quantity-plus btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                                                    data-cart-quantity-plus
                                                    aria-label="Increase quantity"
                                                    @disabled($stock <=0)>
                                                    <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                                            +
</div>
                                                    
                                                </button>
                                            </div>

                                            <div class="cart-line-total mt-10px d-flex justify-content-between align-items-center">
                                                <span class="fs-12 text-uppercase letter-space-3px">
                                                    Item total
                                                </span>

                                                <strong
                                                    class="cart-item-subtotal fs-18 text-uppercase letter-space-3px"
                                                    data-item-subtotal>
                                                    <span data-item-subtotal-value>
                                                        ${{ number_format($lineTotal, 2) }}
                                                    </span>
                                                </strong>
                                            </div>

                                        </div>

                                    </div>
                                    </article>

                                    @empty

                                    <div
                                        class="empty-cart"
                                        data-empty-cart>
                                        <h2 class="fs-24 text-color-dark mb-10px">
                                            Your cart is empty
                                        </h2>

                                        <p class="fs-14 text-color-body mb-20px">
                                            Add products to your cart before proceeding to checkout.
                                        </p>

                                        <a
                                            href="{{ route('products.index') }}"
                                            class="filter-btn text-decoration-none">
                                            Continue Shopping
                                        </a>
                                    </div>

                                    @endforelse

                                    @if (count($cart))
                                    <div class="cart-form-actions d-flex justify-content-between">
                                        <a
                                            href="{{ route('products.index') }}"
                                            class="continue-shopping-button text-decoration-none btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer">
                                            <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Continue Shopping</div>

                                        </a>

                                        <button
                                            type="submit"
                                            class="update-cart-button btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer">
                                            <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Update Cart</div>

                                        </button>
                                    </div>
                                    @endif

                            </form>

                        </div>

                        @if (count($cart))



                        <aside
                            class="cart-summary d-flex flex-column gap-20px"
                            data-cart-summary>
                            <h2 class="fs-24 text-color-dark">
                                Order Summary
                            </h2>

                            {{-- Coupon --}}
                            <div class="cart-coupon-section gap-10px d-flex flex-column">

                                <h3 class="fs-16 text-color-dark mb-10px text-uppercase letter-space-4px">
                                    Coupon code
                                </h3>

                                @if ($couponCode !== '')

                                <div class="applied-coupon gap-10px d-flex flex-column">
                                    <div>
                                        <span class="fs-12 text-uppercase letter-space-3px">
                                            Applied coupon
                                        </span>

                                        <strong class="fs-16">
                                            {{ strtoupper($couponCode) }}
                                        </strong>
                                    </div>

                                    @if (Route::has('cart.coupon.remove'))
                                    <form
                                        action="{{ route('cart.coupon.remove') }}"
                                        method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="remove-coupon-button btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer w-100">
                                            <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Remove</div>
                                            
                                        </button>
                                    </form>
                                    @endif
                                </div>

                                @else

                                @if (Route::has('cart.coupon.apply'))
                                <form
                                    action="{{ route('cart.coupon.apply') }}"
                                    method="POST"
                                    class="coupon-form d-flex flex-column gap-10px">
                                    @csrf

                                    <input
                                        type="text"
                                        name="coupon_code"
                                        value="{{ old('coupon_code') }}"
                                        placeholder="Enter coupon code"
                                        maxlength="100"
                                        autocomplete="off"
                                        class="input-type-field fs-12"
                                        required>

                                    <button
                                        type="submit"
                                        class="apply-coupon-button btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer">
                                        <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Apply</div>
                                    </button>
                                </form>
                                @else
                                <p class="fs-12 text-color-body">
                                    Add the coupon routes to enable coupon codes.
                                </p>
                                @endif

                                @endif

                            </div>

                            <div class="cart-summary-lines d-flex flex-column gap-10px">

                                <div class="cart-summary-row d-flex justify-content-between fs-12 text-uppercase letter-space-3px">
                                    <span>
                                        Subtotal
                                    </span>

                                    <span data-cart-subtotal>
                                        ${{ number_format($subtotal, 2) }}
                                    </span>
                                </div>

                                @if ($couponDiscount > 0)
                                <div class="cart-summary-row cart-summary-discount d-flex justify-content-between fs-12 text-uppercase letter-space-3px">
                                    <span>
                                        Coupon discount
                                    </span>

                                    <span data-cart-discount>
                                        -${{ number_format($couponDiscount, 2) }}
                                    </span>
                                </div>
                                @endif

                                <div class="cart-summary-row d-flex justify-content-between fs-12 text-uppercase letter-space-3px">
                                    <span>
                                        Shipping
                                    </span>

                                    <span data-cart-shipping>
                                        @if ($shipping > 0)
                                        ${{ number_format($shipping, 2) }}
                                        @else
                                        Calculated at checkout
                                        @endif
                                    </span>
                                </div>

                                <div class="cart-summary-row d-flex justify-content-between fs-12 text-uppercase letter-space-3px">
                                    <span>
                                        Tax
                                    </span>

                                    <span data-cart-tax>
                                        Calculated at checkout
                                    </span>
                                </div>

                                <div class="cart-summary-row cart-summary-total fs-16 text-capitalize text-color-dark">
                                    <strong>
                                        Estimated total
                                    </strong>

                                    <strong data-cart-total>
                                        ${{ number_format($total, 2) }}
                                    </strong>
                                </div>

                            </div>

                            <p class="cart-summary-note  fs-12 text-uppercase letter-space-2px">
                                Shipping charges and applicable taxes will be confirmed during checkout.
                            </p>

                            <a
                                href="{{ route('checkout.index') }}"
                                class="checkout-button text-decoration-none btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer">
                                <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Proceed To Checkout</div>
                            </a>

                            <div class="cart-security-note fs-14 text-uppercase letter-space-2px">
                                <i class="fa-solid fa-lock"></i>

                                <span>
                                    Secure checkout
                                </span>
                            </div>

                        </aside>

                        @endif

                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

@endsection