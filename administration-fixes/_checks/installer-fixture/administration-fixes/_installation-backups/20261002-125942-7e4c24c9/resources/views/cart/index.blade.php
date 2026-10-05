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

$unavailableCartItems = collect($cart)->filter(function ($item) {
    return
        !empty($item['unavailable'])
        || (int) ($item['stock'] ?? 0) < 1;
});

$hasUnavailableCartItems = $unavailableCartItems->isNotEmpty();
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
                        <p class="fs-12 text-uppercase letter-space-4px cart-page-count">
                            {{ $cartCount }}
                            {{ $cartCount === 1 ? 'item' : 'items' }}
                            in your cart
                        </p>
                        @endif
                    </div>

                    {{-- Inventory / price synchronization warning --}}
                    @if (session('cart_warning'))
                    <div class="cart-availability-alert mb-20px" role="alert">
                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                        <div>
                            <strong class="text-uppercase letter-space-2px">
                                Your cart was updated
                            </strong>
                            <p>
                                {{ session('cart_warning') }}
                            </p>
                        </div>
                    </div>
                    @endif

                    @if ($hasUnavailableCartItems)
                    <div class="cart-availability-alert is-danger mb-20px" role="alert">
                        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                        <div>
                            <strong class="text-uppercase letter-space-2px">
                                Action required before checkout
                            </strong>
                            <p>
                                Remove unavailable items from your cart before continuing to checkout.
                            </p>
                        </div>
                    </div>
                    @endif

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

                                $isUnavailable =
                                !empty($item['unavailable'])
                                || $stock < 1;

                                $unavailableReason =
                                $item['unavailable_reason']
                                ?? 'This item is currently unavailable.';

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
                                    class="cart-item cart-product-card{{ $isUnavailable ? ' is-unavailable' : '' }}"
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
                                        class="cart-item-image d-flex align-items-start gap-10px mb-20px text-decoration-none">
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
                                                @if (!$isUnavailable)
                                                <span class="cart-stock-badge in-stock">
                                                    In stock
                                                </span>

                                                <span>
                                                    {{ $stock }} available
                                                </span>
                                                @else
                                                <span class="cart-stock-badge out-of-stock">
                                                    Unavailable
                                                </span>

                                                <span class="cart-unavailable-reason">
                                                    {{ $unavailableReason }}
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
                                                    aria-label="Decrease quantity"
                                                    @disabled($isUnavailable || $quantity <= 1)>
                                                    <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                                            −
</div>
                                                </button>

                                                <input
                                                    type="number"
                                                    name="quantities[{{ $cartKey }}]"
                                                    class="cart-quantity-input fs-16 text-uppercase letter-space-4px"
                                                    value="{{ $quantity }}"
                                                    min="1"
                                                    max="{{ max(1, $stock) }}"
                                                    step="1"
                                                    inputmode="numeric"
                                                    pattern="[0-9]*"
                                                    data-cart-key="{{ $cartKey }}"
                                                    data-unit-price="{{ $price }}"
                                                    data-update-url="{{ route('cart.update') }}"
                                                    aria-label="Quantity for {{ $title }}"
                                                    @disabled($isUnavailable)>

                                                <button
                                                    type="button"
                                                    class="quantity-button cart-quantity-plus btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer"
                                                    data-cart-quantity-plus
                                                    aria-label="Increase quantity"
                                                    @disabled($isUnavailable || $stock <= 0 || $quantity >= $stock)>
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
                                            class="cart-empty-shop btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer text-decoration-none">
                                            <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">
                                                Continue Shopping
                                            </div>
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

                            @if ($hasUnavailableCartItems)
                            <button
                                type="button"
                                class="checkout-button cart-checkout-blocked btn-style-2 fs-12 text-color-white justify-self-start"
                                disabled
                                aria-disabled="true">
                                <div class="button-text text-uppercase letter-space-3px">
                                    Resolve Cart Items First
                                </div>
                            </button>

                            <p class="cart-checkout-warning fs-12 text-uppercase letter-space-2px">
                                <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                                Remove unavailable items to continue.
                            </p>
                            @else
                            <a
                                href="{{ route('checkout.index') }}"
                                class="checkout-button text-decoration-none btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer">
                                <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Proceed To Checkout</div>
                            </a>
                            @endif

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


@push('page-styles')
<style>
    .cart-page{
        padding:70px 0 90px;
    }

    .cart-page-header{
        padding-bottom:24px;
        border-bottom:1px solid #e5e7eb;
    }

    .cart-page-header h1{
        line-height:1.05;
        letter-spacing:-.03em;
    }

    .cart-page-count{
        color:#6b7280;
    }

    .cart-availability-alert{
        display:flex;
        align-items:flex-start;
        gap:14px;
        padding:16px 18px;
        border:1px solid #f1d58a;
        border-radius:14px;
        background:#fffaf0;
        color:#7a5510;
    }

    .cart-availability-alert.is-danger{
        border-color:#fecaca;
        background:#fff5f5;
        color:#991b1b;
    }

    .cart-availability-alert > i{
        margin-top:2px;
        font-size:18px;
    }

    .cart-availability-alert strong{
        display:block;
        margin-bottom:5px;
        font-size:12px;
    }

    .cart-availability-alert p{
        margin:0;
        font-size:13px;
        line-height:1.55;
    }

    .cart-product-card.is-unavailable{
        opacity:.82;
    }

    .cart-product-card.is-unavailable .cart-item-image img{
        filter:grayscale(.7);
    }

    .cart-unavailable-reason{
        max-width:340px;
        color:#991b1b;
        line-height:1.55;
    }

    .cart-checkout-blocked{
        width:100%;
        border-radius:100px;
        cursor:not-allowed !important;
        opacity:.5;
    }

    .cart-checkout-warning{
        display:flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        margin:12px 0 0;
        color:#991b1b;
        text-align:center;
        line-height:1.5;
    }

    .cart-parent{
        display:grid;
        grid-template-columns:minmax(0,1.65fr) minmax(320px,.75fr);
        gap:42px;
        align-items:start;
    }

    .cart-products{
        min-width:0;
    }

    #cart-update-form{
        display:grid;
        gap:0;
    }

    .cart-product-card{
        position:relative;
        display:grid;
        grid-template-columns:170px minmax(0,1fr);
        gap:26px;
        padding:28px 0;
        border-bottom:1px solid #e5e7eb;
        background:#fff;
    }

    .cart-item-image{
        position:relative;
        width:170px;
        height:205px;
        margin:0 !important;
        overflow:hidden;
        background:#f2f5fb;
    }

    .cart-item-image img{
        width:100%;
        height:100%;
        object-fit:cover;
    }

    .cart-discount-badge{
        position:absolute;
        top:12px;
        left:12px;
        padding:8px 10px;
        background:#991b1b;
        color:#fff;
        line-height:1;
    }

    .cart-item-details{
        display:grid !important;
        grid-template-columns:minmax(0,1fr) auto;
        gap:18px 28px !important;
        align-content:start;
        margin:0 !important;
    }

    .cart-item-heading{
        grid-column:1/-1;
        display:grid !important;
        grid-template-columns:minmax(0,1fr) auto;
        flex-wrap:initial !important;
        gap:18px !important;
        align-items:start;
    }

    .cart-item-heading h3{
        margin:0;
        font-size:22px;
        line-height:1.25;
    }

    .cart-item-sku{
        margin:0;
        color:#7b8494;
    }

    .cart-remove-button{
        width:auto !important;
        min-width:112px;
        min-height:42px;
        padding:0 18px;
        border:0;
        background:#080d20;
        overflow:hidden;
    }

    .cart-remove-button .button-text{
        min-height:42px;
        display:flex;
        align-items:center;
        justify-content:center;
        pointer-events:none;
    }

    .cart-item-options{
        grid-column:1/-1;
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin:0 !important;
    }

    .cart-item-options p{
        margin:0 !important;
        padding:7px 10px;
        border:1px solid #e1e5ec;
        background:#f7f8fb;
        color:#4b5563;
    }

    .cart-item-pricing{
        gap:16px !important;
    }

    .cart-unit-price{
        gap:7px !important;
    }

    .cart-current-price{
        color:#080d20;
        font-weight:800;
    }

    .cart-regular-price{
        margin-left:7px;
        color:#9299a6;
    }

    .cart-stock-information{
        gap:6px !important;
        color:#6b7280;
    }

    .cart-stock-badge{
        width:max-content;
        padding:5px 8px;
        font-size:10px;
        font-weight:800;
    }

    .cart-stock-badge.in-stock{
        background:#ecfdf5;
        color:#047857;
    }

    .cart-stock-badge.out-of-stock{
        background:#fff1f2;
        color:#be123c;
    }

    .cart-item-actions{
        width:230px !important;
        justify-self:end;
    }

    .cart-quantity-box{
        display:grid;
        grid-template-columns:48px 62px 48px;
        width:max-content;
        min-height:48px;
        margin-left:auto;
        border:1px solid #dfe3eb;
        border-radius:100px;
        background:#fff;
        overflow:hidden;
    }

    .cart-quantity-box .quantity-button{
        position:relative;
        width:48px;
        height:48px;
        min-height:48px;
        padding:0;
        border:0;
        background:#080d20;
        color:#fff;
        overflow:hidden;
    }

    .cart-quantity-box .quantity-button .button-text{
        width:100%;
        height:100%;
        display:flex;
        align-items:center;
        justify-content:center;
        pointer-events:none;
    }

    .cart-quantity-box .quantity-button:disabled{
        background:#858994;
        opacity:.55;
        cursor:not-allowed;
    }

    .cart-quantity-input{
        width:62px;
        height:48px;
        padding:0 5px;
        border:0;
        border-left:8px solid #fff;
        border-right:8px solid #fff;
        background:#f2f5fb;
        color:#080d20;
        font-weight:700;
        text-align:center;
        outline:none;
        appearance:textfield;
        -moz-appearance:textfield;
    }

    .cart-quantity-input::-webkit-inner-spin-button,
    .cart-quantity-input::-webkit-outer-spin-button{
        margin:0;
        -webkit-appearance:none;
    }

    .cart-line-total{
        gap:14px;
        padding-top:12px;
        border-top:1px solid #eef0f4;
    }

    .cart-item-subtotal{
        color:#080d20;
        white-space:nowrap;
    }

    .cart-form-actions{
        gap:14px;
        padding-top:28px;
    }

    .continue-shopping-button,
    .update-cart-button,
    .cart-empty-shop{
        position:relative;
        min-height:48px;
        padding:0 24px;
        border:0;
        background:#080d20;
        color:#fff;
        overflow:hidden;
    }

    .cart-remove-button,
    .continue-shopping-button,
    .update-cart-button,
    .cart-empty-shop,
    .apply-coupon-button,
    .remove-coupon-button,
    .checkout-button{
        border-radius:100px;
    }

    .continue-shopping-button .button-text,
    .update-cart-button .button-text,
    .cart-empty-shop .button-text{
        min-height:48px;
        display:flex;
        align-items:center;
        justify-content:center;
        pointer-events:none;
    }

    .cart-summary{
        position:sticky;
        top:110px;
        padding:28px;
        border:1px solid #e1e5ec;
        background:#f7f8fb;
    }

    .cart-summary h2{
        margin:0;
        padding-bottom:18px;
        border-bottom:1px solid #dfe3eb;
        font-size:25px;
        line-height:1.2;
    }

    .cart-coupon-section{
        padding-bottom:20px;
        border-bottom:1px solid #dfe3eb;
    }

    .cart-coupon-section h3{
        margin:0 !important;
        font-size:12px;
    }

    .coupon-form .input-type-field{
        width:100%;
        min-height:48px;
        box-sizing:border-box;
        padding:0 14px;
        border:1px solid #d5dae3;
        border-radius:100px;
        background:#fff;
        color:#172033;
        text-transform:uppercase;
        letter-spacing:2px;
        outline:none;
    }

    .coupon-form .input-type-field:focus{
        border-color:#080d20;
    }

    .apply-coupon-button,
    .remove-coupon-button,
    .checkout-button{
        position:relative;
        min-height:48px;
        border:0;
        background:#080d20;
        color:#fff;
        overflow:hidden;
    }

    .apply-coupon-button .button-text,
    .remove-coupon-button .button-text,
    .checkout-button .button-text{
        min-height:48px;
        display:flex;
        align-items:center;
        justify-content:center;
        pointer-events:none;
    }

    .applied-coupon{
        padding:14px;
        border:1px solid #d7dce5;
        background:#fff;
    }

    .applied-coupon>div{
        display:flex;
        justify-content:space-between;
        gap:12px;
        align-items:center;
    }

    .cart-summary-lines{
        gap:0 !important;
    }

    .cart-summary-row{
        padding:11px 0;
        border-bottom:1px solid #e2e6ed;
        gap:20px;
    }

    .cart-summary-discount{
        color:#047857;
    }

    .cart-summary-total{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:20px;
        padding:18px 0 6px;
        border-bottom:0;
    }

    .cart-summary-note{
        margin:0;
        color:#6b7280;
        line-height:1.7;
    }

    .checkout-button{
        width:100%;
        display:flex;
        align-items:center;
        justify-content:center;
        text-decoration:none;
    }

    .cart-security-note{
        display:flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        color:#4b5563;
    }

    .cart-security-note i{
        font-size:12px;
    }

    .empty-cart{
        min-height:360px;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        padding:45px 20px;
        border:1px solid #e1e5ec;
        background:#f7f8fb;
        text-align:center;
    }

    .alert{
        padding:14px 16px;
        border:1px solid transparent;
        font-size:12px;
        line-height:1.55;
    }

    .alert-success{
        border-color:#a7f3d0;
        background:#ecfdf5;
        color:#047857;
    }

    .alert-danger{
        border-color:#fecdd3;
        background:#fff1f2;
        color:#be123c;
    }

    .cart-item.is-updating,
    .cart-item.is-removing{
        opacity:.55;
        pointer-events:none;
    }

    @media(max-width:1100px){
        .cart-parent{
            grid-template-columns:minmax(0,1fr) 320px;
            gap:26px;
        }

        .cart-product-card{
            grid-template-columns:140px minmax(0,1fr);
            gap:20px;
        }

        .cart-item-image{
            width:140px;
            height:175px;
        }

        .cart-item-details{
            grid-template-columns:1fr;
        }

        .cart-item-pricing,
        .cart-item-actions{
            grid-column:1/-1;
        }

        .cart-item-actions{
            width:100% !important;
            justify-self:stretch;
        }

        .cart-quantity-box{
            margin-left:0;
        }
    }

    @media(max-width:850px){
        .cart-page{
            padding:50px 0 70px;
        }

        .cart-parent{
            grid-template-columns:1fr;
        }

        .cart-summary{
            position:static;
        }
    }

    @media(max-width:620px){
        .cart-page{
            padding:38px 0 55px;
        }

        .cart-page-header h1{
            font-size:36px;
        }

        .cart-product-card{
            grid-template-columns:96px minmax(0,1fr);
            gap:15px;
            padding:22px 0;
        }

        .cart-item-image{
            width:96px;
            height:122px;
        }

        .cart-item-details{
            gap:14px !important;
        }

        .cart-item-heading{
            grid-template-columns:1fr;
            gap:12px !important;
        }

        .cart-remove-button{
            min-width:0;
            width:100% !important;
        }

        .cart-item-options,
        .cart-item-pricing,
        .cart-item-actions{
            grid-column:1/-1;
        }

        .cart-form-actions{
            flex-direction:column;
        }

        .continue-shopping-button,
        .update-cart-button{
            width:100%;
        }

        .cart-summary{
            padding:22px 18px;
        }
    }

    @media(max-width:420px){
        .cart-product-card{
            grid-template-columns:1fr;
        }

        .cart-item-image{
            width:100%;
            height:280px;
        }

        .cart-quantity-box{
            grid-template-columns:46px 58px 46px;
        }

        .cart-quantity-box .quantity-button{
            width:46px;
        }
    }
</style>
@endpush

@endsection