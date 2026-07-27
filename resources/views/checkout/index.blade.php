@extends('layouts.app')

@section('title', 'Checkout')

@section(
'meta_description',
'Review your order, enter your billing and shipping details, and complete your purchase.'
)

@section('content')

@php
$cartItemCount = collect($cart)->sum(function ($item) {
return max(
1,
(int) ($item['quantity'] ?? 1)
);
});

$coupon = session('cart_coupon', []);
$couponCode = $coupon['code'] ?? '';

$customer = auth()->user();

$oldBillingCountry = old(
'billing_country',
''
);

$oldBillingState = old(
'billing_state',
''
);

$oldBillingCity = old(
'billing_city',
''
);

$oldShippingCountry = old(
'shipping_country',
''
);

$oldShippingState = old(
'shipping_state',
''
);

$oldShippingCity = old(
'shipping_city',
''
);
@endphp

<div
    class="page-wrapper checkout-page"
    data-shipping-quote-url="{{ route('checkout.shipping-quote') }}"
    data-stripe-key="{{ config('payments.stripe.key') }}"
    data-stripe-intent-url="{{ route('checkout.stripe.intent') }}"
    data-old-billing-country="{{ $oldBillingCountry }}"
    data-old-billing-state="{{ $oldBillingState }}"
    data-old-billing-city="{{ $oldBillingCity }}"
    data-old-shipping-country="{{ $oldShippingCountry }}"
    data-old-shipping-state="{{ $oldShippingState }}"
    data-old-shipping-city="{{ $oldShippingCity }}">
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

                <ul
                    class="bread-crumbs list-style-none fs-12 text-uppercase letter-space-4px mb-10px d-flex gap-10px">
                    <li>
                        <a
                            href="{{ route('home-page') }}"
                            class="text-decoration-none text-color-dark">
                            Home
                        </a>
                    </li>

                    <li>
                        <a
                            href="{{ route('cart.index') }}"
                            class="text-decoration-none text-color-dark">
                            Cart
                        </a>
                    </li>

                    <li aria-current="page">
                        Checkout
                    </li>
                </ul>

                <div class="checkout-page-header mb-30px">
                    <h1 class="fs-48 text-color-dark mb-10px">
                        Checkout
                    </h1>

                    <p class="fs-14 text-color-body mb-10px">
                        Review your order and enter your delivery details.
                    </p>
                </div>

                @guest
                <div class="checkout-login-notice mb-20px">
                    <p class="bread-crumbs text-decoration-none fs-12 text-uppercase letter-space-4px d-flex gap-10px align-items-center">
                        Already have an account?

                        <a href="{{ route('login') }}" class="btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer">
                            Login
                        </a>
                    </p>
                </div>
                @endguest

                @if (session('success'))
                <div class="alert alert-success mb-20px">
                    {{ session('success') }}
                </div>
                @endif

                @if (session('error'))
                <div class="alert alert-danger mb-20px">
                    {{ session('error') }}
                </div>
                @endif

                @if ($errors->any())
                <div class="alert alert-danger mb-20px">
                    <ul class="mb-0 list-style-none fs-12 letter-space-4px text-uppercase">
                        @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div
                    id="checkout-location-error"
                    class="checkout-location-error"
                    hidden></div>

                <div class="checkout-layout">

                    <div class="checkout-left">

                        <form
                            action="{{ route('checkout.place') }}"
                            method="POST"
                            id="checkout-form"
                            class="checkout-form">
                            @csrf

                            <section class="checkout-section billing-section mb-20px">

                                <h2 class="fs-24 text-color-dark mb-20px">
                                    Billing Details
                                </h2>

                                <div class="checkout-fields-grid">

                                    <div class="checkout-field checkout-field-full d-flex flex-column gap-10px">
                                        <label for="billing_name" class="text-decoration-none fs-12 text-uppercase letter-space-4px">
                                            Full name
                                            <span class="required-aesteric">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="billing_name"
                                            id="billing_name"
                                            value="{{ old('billing_name', $customer?->name) }}"
                                            placeholder="Full name"
                                            autocomplete="name"
                                            required>
                                    </div>

                                    <div class="checkout-field d-flex flex-column gap-10px">
                                        <label for="billing_email" class="text-decoration-none fs-12 text-uppercase letter-space-4px">
                                            Email address
                                            <span class="required-aesteric">*</span>
                                        </label>

                                        <input
                                            type="email"
                                            name="billing_email"
                                            id="billing_email"
                                            value="{{ old('billing_email', $customer?->email) }}"
                                            placeholder="Email address"
                                            autocomplete="email"
                                            required>
                                    </div>

                                    <div class="checkout-field d-flex flex-column gap-10px">
                                        <label for="billing_phone" class="text-decoration-none fs-12 text-uppercase letter-space-4px">
                                            Phone number
                                            <span class="required-aesteric">*</span>
                                        </label>

                                        <input
                                            type="tel"
                                            name="billing_phone"
                                            id="billing_phone"
                                            value="{{ old('billing_phone') }}"
                                            placeholder="Phone number"
                                            autocomplete="tel"
                                            required>
                                    </div>

                                    <div class="checkout-field checkout-field-full d-flex flex-column gap-10px">
                                        <label for="billing_address" class="text-decoration-none fs-12 text-uppercase letter-space-4px">
                                            Street address
                                            <span class="required-aesteric">*</span>
                                        </label>

                                        <textarea
                                            name="billing_address"
                                            id="billing_address"
                                            rows="3"
                                            placeholder="House number and street address"
                                            autocomplete="street-address"
                                            required>{{ old('billing_address') }}</textarea>
                                    </div>

                                    <div class="checkout-field d-flex flex-column gap-10px">
                                        <label for="billing_country" class="text-decoration-none fs-12 text-uppercase letter-space-4px">
                                            Country
                                            <span class="required-aesteric">*</span>
                                        </label>

                                        <div class="checkout-select-wrapper">
                                            <span
                                                class="checkout-select-flag"
                                                data-flag-for="billing_country"></span>

                                            <select
                                                name="billing_country"
                                                id="billing_country"
                                                class="checkout-location-select"
                                                data-location-country
                                                required>
                                                <option value="">
                                                    Loading countries...
                                                </option>
                                            </select>

                                            <i class="fa-solid fa-chevron-down"></i>
                                        </div>
                                    </div>

                                    <div class="checkout-field d-flex flex-column gap-10px">
                                        <label for="billing_state" class="text-decoration-none fs-12 text-uppercase letter-space-4px">
                                            State / Province
                                            <span class="required-aesteric">*</span>
                                        </label>

                                        <div class="checkout-select-wrapper">
                                            <select
                                                name="billing_state"
                                                id="billing_state"
                                                class="checkout-location-select"
                                                data-location-state
                                                disabled
                                                required>
                                                <option value="">
                                                    Select country first
                                                </option>
                                            </select>

                                            <i class="fa-solid fa-chevron-down"></i>
                                        </div>
                                    </div>

                                    <div class="checkout-field d-flex flex-column gap-10px">
                                        <label for="billing_city" class="text-decoration-none fs-12 text-uppercase letter-space-4px">
                                            City
                                            <span class="required-aesteric">*</span>
                                        </label>

                                        <div class="checkout-select-wrapper">
                                            <select
                                                name="billing_city"
                                                id="billing_city"
                                                class="checkout-location-select"
                                                data-location-city
                                                disabled
                                                required>
                                                <option value="">
                                                    Select state first
                                                </option>
                                            </select>

                                            <i class="fa-solid fa-chevron-down"></i>
                                        </div>
                                    </div>

                                    <div class="checkout-field d-flex flex-column gap-10px">
                                        <label for="billing_zip" class="text-decoration-none fs-12 text-uppercase letter-space-4px">
                                            ZIP / Postal code
                                            <span class="required-aesteric">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="billing_zip"
                                            id="billing_zip"
                                            value="{{ old('billing_zip') }}"
                                            placeholder="ZIP or postal code"
                                            autocomplete="postal-code"
                                            required>
                                    </div>

                                </div>
                            </section>

                            <section class="checkout-section shipping-section mb-20px">

                                <label
                                    for="different-shipping"
                                    class="different-shipping-label text-decoration-none fs-12 text-uppercase letter-space-4px d-flex align-items-center">
                                    <input
                                        type="checkbox"
                                        name="ship_to_different_address"
                                        id="different-shipping"
                                        value="1"
                                        @checked(old('ship_to_different_address'))>

                                    <span>
                                        Ship to a different address?
                                    </span>
                                </label>

                                <div
                                    id="shipping-fields"
                                    class="shipping-fields mt-20px"
                                    @if (!old('ship_to_different_address'))
                                    hidden
                                    @endif>
                                    <h2 class="fs-24 text-color-dark mb-20px">
                                        Shipping Details
                                    </h2>

                                    <div class="checkout-fields-grid">

                                        <div class="checkout-field checkout-field-full d-flex flex-column gap-10px">
                                            <label for="shipping_name" class="fs-12 text-uppercase letter-space-4px d-flex align-items-center">
                                                Full name
                                                <span class="required-aesteric">*</span>
                                            </label>

                                            <input
                                                type="text"
                                                name="shipping_name"
                                                id="shipping_name"
                                                value="{{ old('shipping_name') }}"
                                                placeholder="Full name"
                                                autocomplete="shipping name"
                                                data-shipping-required>
                                        </div>

                                        <div class="checkout-field d-flex flex-column gap-10px">
                                            <label for="shipping_email" class="fs-12 text-uppercase letter-space-4px d-flex align-items-center">
                                                Email address
                                            </label>

                                            <input
                                                type="email"
                                                name="shipping_email"
                                                id="shipping_email"
                                                value="{{ old('shipping_email') }}"
                                                placeholder="Email address"
                                                autocomplete="shipping email">
                                        </div>

                                        <div class="checkout-field d-flex flex-column gap-10px">
                                            <label for="shipping_phone" class="fs-12 text-uppercase letter-space-4px d-flex align-items-center">
                                                Phone number
                                                <span class="required-aesteric">*</span>
                                            </label>

                                            <input
                                                type="tel"
                                                name="shipping_phone"
                                                id="shipping_phone"
                                                value="{{ old('shipping_phone') }}"
                                                placeholder="Phone number"
                                                autocomplete="shipping tel"
                                                data-shipping-required>
                                        </div>

                                        <div class="checkout-field checkout-field-full d-flex flex-column gap-10px">
                                            <label for="shipping_address" class="fs-12 text-uppercase letter-space-4px d-flex align-items-center">
                                                Street address
                                                <span class="required-aesteric">*</span>
                                            </label>

                                            <textarea
                                                name="shipping_address"
                                                id="shipping_address"
                                                rows="3"
                                                placeholder="House number and street address"
                                                autocomplete="shipping street-address"
                                                data-shipping-required>{{ old('shipping_address') }}</textarea>
                                        </div>

                                        <div class="checkout-field d-flex flex-column gap-10px">
                                            <label for="shipping_country" class="fs-12 text-uppercase letter-space-4px d-flex align-items-center">
                                                Country
                                                <span class="required-aesteric">*</span>
                                            </label>

                                            <div class="checkout-select-wrapper">
                                                <span
                                                    class="checkout-select-flag"
                                                    data-flag-for="shipping_country"></span>

                                                <select
                                                    name="shipping_country"
                                                    id="shipping_country"
                                                    class="checkout-location-select"
                                                    data-location-country
                                                    data-shipping-required>
                                                    <option value="">
                                                        Loading countries...
                                                    </option>
                                                </select>

                                                <i class="fa-solid fa-chevron-down"></i>
                                            </div>
                                        </div>

                                        <div class="checkout-field d-flex flex-column gap-10px">
                                            <label for="shipping_state" class="fs-12 text-uppercase letter-space-4px d-flex align-items-center">
                                                State / Province
                                                <span class="required-aesteric">*</span>
                                            </label>

                                            <div class="checkout-select-wrapper">
                                                <select
                                                    name="shipping_state"
                                                    id="shipping_state"
                                                    class="checkout-location-select"
                                                    data-location-state
                                                    data-shipping-required
                                                    disabled>
                                                    <option value="">
                                                        Select country first
                                                    </option>
                                                </select>

                                                <i class="fa-solid fa-chevron-down"></i>
                                            </div>
                                        </div>

                                        <div class="checkout-field d-flex flex-column gap-10px">
                                            <label for="shipping_city" class="fs-12 text-uppercase letter-space-4px d-flex align-items-center">
                                                City
                                                <span class="required-aesteric">*</span>
                                            </label>

                                            <div class="checkout-select-wrapper">
                                                <select
                                                    name="shipping_city"
                                                    id="shipping_city"
                                                    class="checkout-location-select"
                                                    data-location-city
                                                    data-shipping-required
                                                    disabled>
                                                    <option value="">
                                                        Select state first
                                                    </option>
                                                </select>

                                                <i class="fa-solid fa-chevron-down"></i>
                                            </div>
                                        </div>

                                        <div class="checkout-field d-flex flex-column gap-10px">
                                            <label for="shipping_zip" class="fs-12 text-uppercase letter-space-4px d-flex align-items-center">
                                                ZIP / Postal code
                                                <span class="required-aesteric">*</span>
                                            </label>

                                            <input
                                                type="text"
                                                name="shipping_zip"
                                                id="shipping_zip"
                                                value="{{ old('shipping_zip') }}"
                                                placeholder="ZIP or postal code"
                                                autocomplete="shipping postal-code"
                                                data-shipping-required>
                                        </div>

                                    </div>
                                </div>
                            </section>

                            <section class="checkout-section order-notes-section mb-20px">

                                <h2 class="fs-24 text-color-dark mb-20px">
                                    Additional Information
                                </h2>

                                <div class="checkout-field checkout-field-full d-flex flex-column gap-10px">
                                    <label for="order_notes">
                                        Order notes
                                    </label>

                                    <textarea
                                        name="order_notes"
                                        id="order_notes"
                                        rows="4"
                                        placeholder="Delivery instructions or other information">{{ old('order_notes') }}</textarea>
                                </div>
                            </section>

                            <section class="checkout-section payment-section mt-30px">

                                <h2 class="fs-24 text-color-dark mb-20px">
                                    Payment Method
                                </h2>

                                <div class="checkout-payment-methods d-flex flex-column gap-5px">

                                    @if (config('payments.stripe.enabled'))
                                    <label class="checkout-payment-option">
                                        <input
                                            type="radio"
                                            name="payment_method"
                                            value="stripe"
                                            @checked(old('payment_method', 'stripe' )==='stripe' )
                                            required>

                                        <span class="checkout-payment-option-content text-decoration-none fs-16 text-uppercase letter-space-2px">
                                            <strong>
                                                Credit or debit card
                                            </strong>

                                            <small class="text-decoration-none fs-12 text-uppercase letter-space-3px">
                                                Card, Apple Pay, Google Pay and supported wallets
                                            </small>
                                        </span>
                                    </label>

                                    <div
                                        id="stripe-payment-container"
                                        class="online-payment-container">
                                        <div id="stripe-payment-element"></div>

                                        <p
                                            id="stripe-payment-error"
                                            class="payment-error"
                                            hidden></p>
                                    </div>
                                    @endif

                                    <!-- @if (config('payments.paypal.enabled'))
                                    <label class="checkout-payment-option">
                                        <input
                                            type="radio"
                                            name="payment_method"
                                            value="paypal"
                                            @checked(old('payment_method')==='paypal' )
                                            required>

                                        <span class="checkout-payment-option-content">
                                            <strong>
                                                PayPal
                                            </strong>

                                            <small>
                                                Pay using your PayPal account
                                            </small>
                                        </span>
                                    </label>

                                    <div
                                        id="paypal-payment-container"
                                        class="online-payment-container"
                                        hidden>
                                        <div id="paypal-button-container"></div>
                                    </div>
                                    @endif -->

                                    <!-- @if (config('payments.cod.enabled'))
                                    <label class="checkout-payment-option">
                                        <input
                                            type="radio"
                                            name="payment_method"
                                            value="cash_on_delivery"
                                            @checked(old('payment_method')==='cash_on_delivery' )
                                            required>

                                        <span class="checkout-payment-option-content">
                                            <strong>
                                                Cash on delivery
                                            </strong>

                                            <small>
                                                Pay when your order arrives
                                            </small>
                                        </span>
                                    </label>
                                    @endif -->

                                    @if (config('payments.bank_transfer.enabled'))
                                    <label class="checkout-payment-option mb-20px">
                                        <input
                                            type="radio"
                                            name="payment_method"
                                            value="bank_transfer"
                                            @checked(old('payment_method')==='bank_transfer' )
                                            required>

                                        <span class="checkout-payment-option-content text-decoration-none fs-16 text-uppercase letter-space-2px">
                                            <strong>
                                                Direct bank transfer
                                            </strong>

                                            <small class="fs-12 text-uppercase letter-space-3px">
                                                Your order will remain pending until payment is verified
                                            </small>
                                        </span>
                                    </label>

                                    <div
                                        id="bank-transfer-details"
                                        class="online-payment-container"
                                        hidden>

                                        <h3 class="fs-18 text-color-dark mb-10px fs-24 text-color-dark mb-20px">
                                            Bank transfer instructions
                                        </h3>

                                        <p class="fs-14 text-color-body mb-10px">
                                            Place your order first, then transfer the exact order total
                                            to the bank account below. Your order will remain pending
                                            until the payment has been verified.
                                        </p>

                                        <div class="bank-transfer-information text-decoration-none fs-16 text-uppercase">

                                            <p>
                                                <strong>Bank name:</strong>
                                                {{ config('payments.bank_transfer.bank_name') ?: 'Not configured' }}
                                            </p>

                                            <p>
                                                <strong>Account title:</strong>
                                                {{ config('payments.bank_transfer.account_name') ?: 'Not configured' }}
                                            </p>

                                            <p>
                                                <strong>Account number:</strong>
                                                {{ config('payments.bank_transfer.account_number') ?: 'Not configured' }}
                                            </p>

                                            @if (config('payments.bank_transfer.iban'))
                                            <p>
                                                <strong>IBAN:</strong>
                                                {{ config('payments.bank_transfer.iban') }}
                                            </p>
                                            @endif

                                            @if (config('payments.bank_transfer.swift_code'))
                                            <p>
                                                <strong>SWIFT/BIC:</strong>
                                                {{ config('payments.bank_transfer.swift_code') }}
                                            </p>
                                            @endif

                                        </div>

                                        <div class="bank-transfer-notice mt-10px text-decoration-none fs-16">
                                            <p class="">
                                                <strong class="text-uppercase">Payment reference:</strong>
                                                Your order number will be provided after placing the order.
                                            </p>

                                            <p>
                                                Please include the order number in your bank transfer reference
                                                so the payment can be matched with your order.
                                            </p>
                                        </div>
                                    </div>
                                    @endif

                                </div>

                            </section>

                            <section class="checkout-section terms-section mt-20px">

                                <label class="checkout-terms-label fs-12 text-uppercase letter-space-3px d-flex align-items-center">
                                    <input
                                        type="checkbox"
                                        name="terms"
                                        value="1"
                                        @checked(old('terms'))
                                        required>

                                    <span>
                                        I agree to the

                                        <a
                                            href="{{ route('terms-and-conditions-page') }}"
                                            target="_blank"
                                            rel="noopener" class="text-color-body text-decoration-none">
                                            terms and conditions
                                        </a>.
                                    </span>
                                </label>
                            </section>

                        </form>
                    </div>

                    <aside class="checkout-right">
                        <div class="checkout-order-review">

                            <div class="checkout-order-heading mb-20px">
                                <div class="mb-10px">
                                    <h2 class="fs-24 text-color-dark mb-20px">
                                        Review Your Order
                                    </h2>

                                    <p class="text-decoration-none fs-12 text-uppercase letter-space-4px">
                                        {{ $cartItemCount }}
                                        {{ $cartItemCount === 1 ? 'item' : 'items' }}
                                    </p>
                                </div>

                                <a
                                    href="{{ route('cart.index') }}"
                                    class="checkout-edit-cart-link text-decoration-none fs-12 text-uppercase letter-space-4px text-color-body">
                                    Edit cart
                                </a>
                            </div>

                            <div class="checkout-products">
                                @foreach ($cart as $item)
                                @php
                                $title = $item['title'] ?? 'Product';

                                $quantity = max(
                                1,
                                (int) ($item['quantity'] ?? 1)
                                );

                                $price = (float) (
                                $item['price'] ?? 0
                                );

                                $lineTotal = $price * $quantity;

                                $sku = $item['sku'] ?? null;

                                $options = is_array(
                                $item['options'] ?? null
                                )
                                ? $item['options']
                                : [];
                                @endphp

                                <article class="checkout-product-item">
                                    <div class="checkout-product-details">

                                        <div class="checkout-product-title-row mb-10px d-flex justify-content-between">
                                            <h3 class="fs-18 text-color-dark text-capitalize">
                                                {{ $title }}
                                            </h3>

                                            <strong class="checkout-product-total">
                                                ${{ number_format($lineTotal, 2) }}
                                            </strong>
                                        </div>

                                        @if ($sku)
                                        <p class="checkout-product-sku fs-12 letter-space-3px text-uppercase mb-10px">
                                            SKU: {{ $sku }}
                                        </p>
                                        @endif

                                        @if (!empty($options))
                                        <div class="checkout-product-options fs-12 letter-space-3px text-uppercase justify-content-between d-flex mb-10px">
                                            @foreach ($options as $key => $option)
                                            @php
                                            if (is_array($option)) {
                                            $optionName =
                                            $option['option_name']
                                            ?? $option['name']
                                            ?? $key;

                                            $optionValue =
                                            $option['value_label']
                                            ?? $option['label']
                                            ?? $option['value']
                                            ?? '';
                                            } else {
                                            $optionName = $key;
                                            $optionValue = $option;
                                            }
                                            @endphp

                                            @if ($optionValue !== '')
                                            <p class="">
                                                <strong>
                                                    {{ $optionName }}:
                                                </strong>

                                                {{ $optionValue }}
                                            </p>
                                            @endif
                                            @endforeach
                                        </div>
                                        @endif

                                        <p class="checkout-product-price fs-12 letter-space-3px text-uppercase mb-10px">
                                            {{ $quantity }}
                                            ×
                                            ${{ number_format($price, 2) }}
                                        </p>
                                    </div>
                                </article>
                                @endforeach
                            </div>

                            <div class="checkout-coupon-section mb-20px">

                                <h3 class="fs-16 text-color-dark letter-space-3px text-uppercase mb-10px">
                                    Coupon code
                                </h3>

                                @if ($couponCode !== '')
                                <div class="applied-coupon">
                                    <div class="mb-10px d-flex justify-content-between">
                                        <span class="fs-12 letter-space-3px text-uppercase ">
                                            Applied coupon
                                        </span>

                                        <strong>
                                            {{ strtoupper($couponCode) }}
                                        </strong>
                                    </div>

                                    <form
                                        action="{{ route('cart.coupon.remove') }}"
                                        method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="remove-coupon-button btn-style-2 fs-12 text-color-white cursor-pointer w-100">
                                            <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Remove</div>
                                        </button>
                                    </form>
                                </div>
                                @else
                                <form
                                    action="{{ route('cart.coupon.apply') }}"
                                    method="POST"
                                    class="checkout-coupon-form">
                                    @csrf

                                    <input
                                        type="text"
                                        name="coupon_code"
                                        value="{{ old('coupon_code') }}"
                                        placeholder="Enter coupon code"
                                        maxlength="100"
                                        autocomplete="off" class="mb-10px "
                                        required>

                                    <button
                                        type="submit"
                                        class="apply-coupon-button btn-style-2 fs-12 text-color-white cursor-pointer w-100">
                                        <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Apply</div>
                                        
                                    </button>
                                </form>
                                @endif

                            </div>

                            <div class="checkout-summary-lines">

                                <div class="checkout-summary-row d-flex justify-content-between fs-12 letter-space-3px text-uppercase mb-10px">
                                    <span>
                                        Cart items
                                    </span>

                                    <strong>
                                        {{ $cartItemCount }}
                                    </strong>
                                </div>

                                <div class="checkout-summary-row d-flex justify-content-between fs-12 letter-space-3px text-uppercase mb-10px">
                                    <span>
                                        Subtotal
                                    </span>

                                    <strong>
                                        ${{ number_format($subtotal, 2) }}
                                    </strong>
                                </div>

                                @if ($discount > 0)
                                <div class="checkout-summary-row checkout-summary-discount d-flex justify-content-between fs-12 letter-space-3px text-uppercase mb-10px">
                                    <span>
                                        Coupon discount
                                    </span>

                                    <strong>
                                        -${{ number_format($discount, 2) }}
                                    </strong>
                                </div>
                                @endif

                                <div class="checkout-summary-row d-flex justify-content-between fs-12 letter-space-3px text-uppercase mb-10px">
                                    <span>
                                        Shipping
                                    </span>

                                    <strong id="checkout-shipping-amount">
                                        @if ($shipping > 0)
                                        ${{ number_format($shipping, 2) }}
                                        @else
                                        Free
                                        @endif
                                    </strong>
                                </div>

                                <div class="checkout-summary-row checkout-summary-total d-flex justify-content-between fs-16 letter-space-3px text-uppercase mb-10px">
                                    <span>
                                        Total
                                    </span>

                                    <strong id="checkout-total-amount">
                                        ${{ number_format($total, 2) }}
                                    </strong>
                                </div>

                                <p
                                    id="checkout-shipping-status"
                                    class="checkout-shipping-status fs-14 text-color-body mb-10px">
                                    Select a delivery country to update shipping.
                                </p>
                            </div>

                            <button
                                type="submit"
                                form="checkout-form"
                                class="checkout-place-order-button mb-10px btn-style-2 fs-12 text-color-white cursor-pointer w-100"
                                id="checkout-place-order-button">
                                <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(-9.61719px, 2.46484px, 0px) scale(1.12);">Place Order</div>
                                
                            </button>

                            <div class="checkout-security-note fs-14 letter-space-3px text-uppercase mb-10px">
                                <i class="fa-solid fa-lock"></i>

                                <span>
                                    Secure checkout
                                </span>
                            </div>

                        </div>
                    </aside>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        const checkoutPage = document.querySelector('.checkout-page');

        if (!checkoutPage) {
            return;
        }

        const locationsApi =
            'https://countriesnow.space/api/v0.1/countries';

        const shippingQuoteUrl =
            checkoutPage.dataset.shippingQuoteUrl;

        const csrfToken =
            document.querySelector(
                'meta[name="csrf-token"]'
            )?.getAttribute('content') || '';

        const differentShippingCheckbox =
            document.getElementById(
                'different-shipping'
            );

        const shippingFields =
            document.getElementById(
                'shipping-fields'
            );

        const shippingAmount =
            document.getElementById(
                'checkout-shipping-amount'
            );

        const totalAmount =
            document.getElementById(
                'checkout-total-amount'
            );

        const shippingStatus =
            document.getElementById(
                'checkout-shipping-status'
            );

        const locationError =
            document.getElementById(
                'checkout-location-error'
            );

        const placeOrderButton =
            document.getElementById(
                'checkout-place-order-button'
            );

        const locationGroups = {
            billing: {
                country: document.getElementById(
                    'billing_country'
                ),

                state: document.getElementById(
                    'billing_state'
                ),

                city: document.getElementById(
                    'billing_city'
                ),

                oldCountry: checkoutPage.dataset.oldBillingCountry ||
                    '',

                oldState: checkoutPage.dataset.oldBillingState ||
                    '',

                oldCity: checkoutPage.dataset.oldBillingCity ||
                    '',
            },

            shipping: {
                country: document.getElementById(
                    'shipping_country'
                ),

                state: document.getElementById(
                    'shipping_state'
                ),

                city: document.getElementById(
                    'shipping_city'
                ),

                oldCountry: checkoutPage.dataset.oldShippingCountry ||
                    '',

                oldState: checkoutPage.dataset.oldShippingState ||
                    '',

                oldCity: checkoutPage.dataset.oldShippingCity ||
                    '',
            },
        };

        let countries = [];
        let shippingRequestController = null;

        function showLocationError(message) {
            if (!locationError) {
                return;
            }

            locationError.textContent = message;
            locationError.hidden = false;
        }

        function clearLocationError() {
            if (!locationError) {
                return;
            }

            locationError.textContent = '';
            locationError.hidden = true;
        }

        function flagFromCode(countryCode) {
            if (!countryCode) {
                return '';
            }

            return countryCode
                .toUpperCase()
                .split('')
                .map(function(letter) {
                    return String.fromCodePoint(
                        127397 + letter.charCodeAt()
                    );
                })
                .join('');
        }

        function updateFlag(select) {
            if (!select) {
                return;
            }

            const flagElement =
                document.querySelector(
                    '[data-flag-for="' +
                    select.id +
                    '"]'
                );

            if (!flagElement) {
                return;
            }

            flagElement.textContent =
                flagFromCode(select.value);
        }

        function setSelectMessage(
            select,
            message,
            disabled = true
        ) {
            if (!select) {
                return;
            }

            select.innerHTML = '';

            const option =
                document.createElement('option');

            option.value = '';
            option.textContent = message;

            select.appendChild(option);
            select.disabled = disabled;
        }

        function appendOption(
            select,
            value,
            label,
            selectedValue = ''
        ) {
            const option =
                document.createElement('option');

            option.value = value;
            option.textContent = label;

            if (
                String(value) ===
                String(selectedValue)
            ) {
                option.selected = true;
            }

            select.appendChild(option);
        }

        function sortByName(items) {
            return [...items].sort(
                function(first, second) {
                    return String(first.name)
                        .localeCompare(
                            String(second.name)
                        );
                }
            );
        }

        function getSelectedCountryName(select) {
            return select
                ?.selectedOptions?.[0]
                ?.dataset?.countryName ||
                '';
        }

        function populateCountries(
            group,
            selectedCountry = ''
        ) {
            const countrySelect =
                group.country;

            if (!countrySelect) {
                return;
            }

            countrySelect.innerHTML = '';

            appendOption(
                countrySelect,
                '',
                'Select country'
            );

            sortByName(countries).forEach(
                function(country) {
                    const option =
                        document.createElement(
                            'option'
                        );

                    const countryCode =
                        String(
                            country.iso2 || ''
                        ).toUpperCase();

                    option.value = countryCode;

                    option.dataset.countryName =
                        country.name;

                    option.textContent =
                        flagFromCode(countryCode) +
                        ' ' +
                        country.name;

                    if (
                        countryCode ===
                        String(
                            selectedCountry
                        ).toUpperCase()
                    ) {
                        option.selected = true;
                    }

                    countrySelect.appendChild(
                        option
                    );
                }
            );

            countrySelect.disabled = false;
            updateFlag(countrySelect);
        }

        function findCountry(countryCode) {
            return countries.find(
                function(country) {
                    return String(
                            country.iso2 || ''
                        ).toUpperCase() ===
                        String(
                            countryCode || ''
                        ).toUpperCase();
                }
            );
        }

        function populateStates(
            group,
            selectedState = ''
        ) {
            const selectedCountry =
                findCountry(
                    group.country.value
                );

            if (
                !selectedCountry ||
                !Array.isArray(
                    selectedCountry.states
                )
            ) {
                setSelectMessage(
                    group.state,
                    'No states available',
                    false
                );

                setSelectMessage(
                    group.city,
                    'Select state first'
                );

                return;
            }

            group.state.innerHTML = '';

            appendOption(
                group.state,
                '',
                'Select state / province'
            );

            sortByName(
                selectedCountry.states
            ).forEach(function(state) {
                appendOption(
                    group.state,
                    state.name,
                    state.name,
                    selectedState
                );
            });

            group.state.disabled = false;

            setSelectMessage(
                group.city,
                'Select state first'
            );
        }

        async function populateCities(
            group,
            selectedCity = ''
        ) {
            const countryName =
                getSelectedCountryName(
                    group.country
                );

            const stateName =
                group.state.value;

            if (
                !countryName ||
                !stateName
            ) {
                setSelectMessage(
                    group.city,
                    'Select state first'
                );

                return;
            }

            setSelectMessage(
                group.city,
                'Loading cities...'
            );

            try {
                const response = await fetch(
                    locationsApi +
                    '/state/cities', {
                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/json',

                            Accept: 'application/json',
                        },

                        body: JSON.stringify({
                            country: countryName,
                            state: stateName,
                        }),
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        'Unable to load cities.'
                    );
                }

                const payload =
                    await response.json();

                const cities =
                    Array.isArray(payload.data) ?
                    payload.data : [];

                group.city.innerHTML = '';

                appendOption(
                    group.city,
                    '',
                    cities.length ?
                    'Select city' :
                    'No cities available'
                );

                [...cities]
                .sort(function(first, second) {
                        return String(first)
                            .localeCompare(
                                String(second)
                            );
                    })
                    .forEach(function(city) {
                        appendOption(
                            group.city,
                            city,
                            city,
                            selectedCity
                        );
                    });

                group.city.disabled = false;
            } catch (error) {
                console.error(error);

                setSelectMessage(
                    group.city,
                    'Unable to load cities',
                    false
                );

                showLocationError(
                    'Cities could not be loaded. Please refresh the page and try again.'
                );
            }
        }

        async function initialiseGroup(group) {
            populateCountries(
                group,
                group.oldCountry
            );

            if (!group.oldCountry) {
                return;
            }

            populateStates(
                group,
                group.oldState
            );

            if (
                group.oldState &&
                group.state.value
            ) {
                await populateCities(
                    group,
                    group.oldCity
                );
            }
        }

        function updateShippingFields() {
            const isDifferent =
                Boolean(
                    differentShippingCheckbox
                    ?.checked
                );

            if (shippingFields) {
                shippingFields.hidden = !isDifferent;
            }

            if (shippingFields) {
                shippingFields
                    .querySelectorAll(
                        '[data-shipping-required]'
                    )
                    .forEach(function(field) {
                        field.required =
                            isDifferent;
                    });
            }

            updateShippingQuote();
        }

        function getDeliveryCountry() {
            if (
                differentShippingCheckbox
                ?.checked
            ) {
                return locationGroups
                    .shipping
                    .country
                    ?.value || '';
            }

            return locationGroups
                .billing
                .country
                ?.value || '';
        }

        async function updateShippingQuote() {
            const countryCode =
                getDeliveryCountry();

            if (!countryCode) {
                if (shippingStatus) {
                    shippingStatus.textContent =
                        'Select a delivery country to update shipping.';
                }

                return;
            }

            if (shippingRequestController) {
                shippingRequestController.abort();
            }

            shippingRequestController =
                new AbortController();

            if (shippingStatus) {
                shippingStatus.textContent =
                    'Updating shipping...';
            }

            if (placeOrderButton) {
                placeOrderButton.disabled = true;
            }

            try {
                const response = await fetch(
                    shippingQuoteUrl, {
                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/json',

                            Accept: 'application/json',

                            'X-CSRF-TOKEN': csrfToken,

                            'X-Requested-With': 'XMLHttpRequest',
                        },

                        body: JSON.stringify({
                            country_code: countryCode,
                        }),

                        signal: shippingRequestController
                            .signal,
                    }
                );

                const payload =
                    await response.json();

                if (
                    !response.ok ||
                    !payload.success
                ) {
                    throw new Error(
                        payload.message ||
                        'Shipping could not be updated.'
                    );
                }

                if (shippingAmount) {
                    shippingAmount.textContent =
                        payload.formatted_shipping;
                }

                if (totalAmount) {
                    totalAmount.textContent =
                        payload.formatted_total;
                }

                if (shippingStatus) {
                    shippingStatus.textContent =
                        payload.shipping > 0 ?
                        'Shipping updated for the selected country.' :
                        'Your order qualifies for free shipping.';
                }
            } catch (error) {
                if (
                    error.name ===
                    'AbortError'
                ) {
                    return;
                }

                console.error(error);

                if (shippingStatus) {
                    shippingStatus.textContent =
                        'Shipping could not be updated. Please try again.';
                }
            } finally {
                if (placeOrderButton) {
                    placeOrderButton.disabled =
                        false;
                }
            }
        }

        async function loadCountries() {
            clearLocationError();

            Object.values(
                locationGroups
            ).forEach(function(group) {
                setSelectMessage(
                    group.country,
                    'Loading countries...'
                );
            });

            try {
                const response = await fetch(
                    locationsApi + '/states', {
                        method: 'GET',

                        headers: {
                            Accept: 'application/json',
                        },
                    }
                );

                if (!response.ok) {
                    throw new Error(
                        'Unable to load countries.'
                    );
                }

                const payload =
                    await response.json();

                countries =
                    Array.isArray(payload.data) ?
                    payload.data : [];

                if (!countries.length) {
                    throw new Error(
                        'No country data was returned.'
                    );
                }

                await initialiseGroup(
                    locationGroups.billing
                );

                await initialiseGroup(
                    locationGroups.shipping
                );

                updateShippingQuote();
            } catch (error) {
                console.error(error);

                Object.values(
                    locationGroups
                ).forEach(function(group) {
                    setSelectMessage(
                        group.country,
                        'Unable to load countries',
                        false
                    );
                });

                showLocationError(
                    'Countries could not be loaded. Check your internet connection and refresh the page.'
                );
            }
        }

        Object.values(
            locationGroups
        ).forEach(function(group) {
            group.country.addEventListener(
                'change',
                function() {
                    updateFlag(
                        group.country
                    );

                    populateStates(group);

                    if (
                        group ===
                        locationGroups.billing ||
                        differentShippingCheckbox
                        ?.checked
                    ) {
                        updateShippingQuote();
                    }
                }
            );

            group.state.addEventListener(
                'change',
                function() {
                    populateCities(group);
                }
            );
        });

        differentShippingCheckbox
            ?.addEventListener(
                'change',
                updateShippingFields
            );

        updateShippingFields();
        loadCountries();
    });
</script>
<script src="https://js.stripe.com/v3/"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const paymentRadios = document.querySelectorAll(
            'input[name="payment_method"]'
        );

        const stripeContainer = document.getElementById(
            'stripe-payment-container'
        );

        const bankTransferContainer = document.getElementById(
            'bank-transfer-details'
        );

        function updatePaymentMethodDisplay() {

            const selected =
                document.querySelector(
                    'input[name="payment_method"]:checked'
                )?.value || '';

            if (stripeContainer) {
                stripeContainer.hidden = selected !== 'stripe';
            }

            if (bankTransferContainer) {
                bankTransferContainer.hidden =
                    selected !== 'bank_transfer';
            }

            if (selected === 'stripe') {
                document.dispatchEvent(
                    new CustomEvent('checkout:stripe-selected')
                );
            }
        }

        paymentRadios.forEach(function(radio) {
            radio.addEventListener(
                'change',
                updatePaymentMethodDisplay
            );
        });

        updatePaymentMethodDisplay();

    });
</script>

<script src="{{ asset('asset/js/checkout-stripe.js') }}"></script>
@endpush