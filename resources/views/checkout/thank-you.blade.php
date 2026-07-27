@extends('layouts.app')

@section('title', 'Order Thank You')

@section(
'meta_description',
'Thank you for your order. Review your order, payment, and delivery details.'
)

@section('content')

@php
$paymentMethod = $order->payment_method ?? '';
$paymentStatus = $order->payment_status ?? 'pending';
$orderStatus = $order->order_status ?? 'pending';

$isStripe = $paymentMethod === 'stripe';
$isBankTransfer = $paymentMethod === 'bank_transfer';

$isPaid = in_array($paymentStatus, [
'paid',
'succeeded',
], true);

$isProcessing = in_array($paymentStatus, [
'processing',
'pending',
'requires_action',
], true);

$isFailed = in_array($paymentStatus, [
'failed',
'cancelled',
'canceled',
], true);
@endphp

<div class="page-wrapper order-thank-you-page">

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
                    class="bread-crumbs list-style-none fs-12 text-uppercase letter-space-4px mb-20px d-flex gap-10px">
                    <li>
                        <a
                            href="{{ route('home-page') }}"
                            class="text-decoration-none text-color-dark">
                            Home
                        </a>
                    </li>

                    <li aria-current="page">
                        Order confirmation
                    </li>
                </ul>
                <div class="order-thank-you-header mb-20px">

                    @if ($isPaid)
                    <div class="d-flex gap-10px align-items-center mb-10px">
                        <div class="order-status-icon order-status-success">
                            <i class="fa-solid fa-check"></i>
                        </div>

                        <h1 class="fs-42 text-color-dark">
                            Thank you for your order
                        </h1>
                    </div>
                    <p class="fs-15 text-color-body">
                        Your payment was successful and your order has been received.
                    </p>

                    @elseif ($isBankTransfer)
                    <div class="d-flex gap-10px align-items-center mb-10px">
                        <div class="order-status-icon order-status-pending">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <h1 class="fs-42 text-color-dark">
                            Your order has been placed
                        </h1>
                    </div>
                    <p class="fs-15 text-color-body">
                        Your order will remain pending until your bank transfer is verified.
                    </p>

                    @elseif ($isProcessing)
                    <div class="d-flex gap-10px align-items-center mb-10px">
                        <div class="order-status-icon order-status-pending">
                            <i class="fa-solid fa-clock"></i>
                        </div>

                        <h1 class="fs-42 text-color-dark">
                            Your payment is processing
                        </h1>
                    </div>
                    <p class="fs-15 text-color-body">
                        We are waiting for confirmation from the payment provider.
                    </p>

                    @elseif ($isFailed)
                    <div class="d-flex gap-10px align-items-center mb-10px">
                        <div class="order-status-icon order-status-failed">
                            <i class="fa-solid fa-xmark"></i>
                        </div>

                        <h1 class="fs-42 text-color-dark">
                            Payment was not completed
                        </h1>
                    </div>

                    <p class="fs-15 text-color-body">
                        Your order was created, but the payment was unsuccessful.
                    </p>

                    @else
                    <div class="d-flex gap-10px align-items-center mb-10px">
                        <div class="order-status-icon order-status-pending">
                            <i class="fa-solid fa-receipt"></i>
                        </div>

                        <h1 class="fs-42 text-color-dark">
                            Thank you for your order
                        </h1>
                    </div>
                    <p class="fs-15 text-color-body">
                        Your order has been received.
                    </p>
                    @endif

                </div>
                <div class="order-thank-you-card">

                    <div class="order-thankyou-card left-ctr">
                        <div class="order-reference-grid">

                            <div class="order-reference-item fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                <span>Order number</span>

                                <strong>
                                    {{ $order->order_number }}
                                </strong>
                            </div>

                            <div class="order-reference-item fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                <span>Tracking number</span>

                                <strong>
                                    {{ $order->tracking_number ?: 'Will be assigned soon' }}
                                </strong>
                            </div>

                            <div class="order-reference-item fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                <span>Order status</span>

                                <strong>
                                    {{ ucfirst(str_replace('_', ' ', $orderStatus)) }}
                                </strong>
                            </div>

                            <div class="order-reference-item fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                <span>Payment status</span>

                                <strong>
                                    {{ ucfirst(str_replace('_', ' ', $paymentStatus)) }}
                                </strong>
                            </div>

                            <div class="order-reference-item fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                <span>Payment method</span>

                                <strong>
                                    @if ($isStripe)
                                    Credit or debit card
                                    @elseif ($isBankTransfer)
                                    Direct bank transfer
                                    @else
                                    {{ ucfirst(str_replace('_', ' ', $paymentMethod)) }}
                                    @endif
                                </strong>
                            </div>

                            <div class="order-reference-item fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                <span>Order total</span>

                                <strong>
                                    ${{ number_format((float) $order->total, 2) }}
                                </strong>
                            </div>

                        </div>
                        @if ($isBankTransfer)
                        <section class="order-section bank-transfer-instructions">

                            <h2 class="fs-24 text-color-dark mb-10px">
                                Bank transfer instructions
                            </h2>

                            <p class="fs-15 text-color-body mb-10px">
                                Please transfer the exact order total using your order number as the payment reference.
                            </p>

                            <div class="bank-transfer-details">

                                @if (config('payments.bank_transfer.bank_name'))
                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>Bank:</strong>
                                    {{ config('payments.bank_transfer.bank_name') }}
                                </p>
                                @endif

                                @if (config('payments.bank_transfer.account_name'))
                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>Account name:</strong>
                                    {{ config('payments.bank_transfer.account_name') }}
                                </p>
                                @endif

                                @if (config('payments.bank_transfer.account_number'))
                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>Account number:</strong>
                                    {{ config('payments.bank_transfer.account_number') }}
                                </p>
                                @endif

                                @if (config('payments.bank_transfer.iban'))
                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>IBAN:</strong>
                                    {{ config('payments.bank_transfer.iban') }}
                                </p>
                                @endif

                                @if (config('payments.bank_transfer.swift_code'))
                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>SWIFT code:</strong>
                                    {{ config('payments.bank_transfer.swift_code') }}
                                </p>
                                @endif

                                @if (config('payments.bank_transfer.branch_name'))
                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>Branch:</strong>
                                    {{ config('payments.bank_transfer.branch_name') }}
                                </p>
                                @endif

                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>Payment reference:</strong>
                                    {{ $order->order_number }}
                                </p>

                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>Amount:</strong>
                                    ${{ number_format((float) $order->total, 2) }}
                                </p>

                            </div>

                            @if (config('payments.bank_transfer.instructions'))
                            <div class="bank-transfer-note fs-15 text-color-body mb-10px">
                                {{ config('payments.bank_transfer.instructions') }}
                            </div>
                            @endif

                        </section>
                        @endif

                        @if ($isFailed)
                        <section class="order-section payment-failed-section">

                            <h2 class="fs-24 text-color-dark mb-10px">
                                Try the payment again
                            </h2>

                            <p class="fs-14 text-color-body mb-20px">
                                Return to checkout and use another card or choose direct bank transfer.
                            </p>

                            <a
                                href="{{ route('checkout.index') }}"
                                class="btn-style-1 text-decoration-none">
                                Return to checkout
                            </a>

                        </section>
                        @endif
                        <div class="order-details-grid">

                            <section class="order-section">

                                <h2 class="fs-24 text-color-dark mb-10px">
                                    Billing details
                                </h2>

                                <div class="order-address">

                                    <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                        <strong>{{ $order->billing_name }}</strong>
                                    </p>

                                    <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">{{ $order->billing_email }}</p>
                                    <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">{{ $order->billing_phone }}</p>

                                    <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                        {{ $order->billing_address }}
                                    </p>

                                    <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                        {{ $order->billing_city }}
                                        @if ($order->billing_state)
                                        , {{ $order->billing_state }}
                                        @endif
                                    </p>

                                    <p class="fs-16 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                        {{ $order->billing_country }}
                                        {{ $order->billing_zip }}
                                    </p>

                                </div>

                            </section>

                            <section class="order-section">

                                <h2 class="fs-24 text-color-dark mb-10px">
                                    Shipping details
                                </h2>

                                <div class="order-address">

                                    <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                        <strong>
                                            {{ $order->shipping_name ?: $order->billing_name }}
                                        </strong>
                                    </p>

                                    @if ($order->shipping_email)
                                    <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">{{ $order->shipping_email }}</p>
                                    @endif

                                    <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                        {{ $order->shipping_phone ?: $order->billing_phone }}
                                    </p>

                                    <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                        {{ $order->shipping_address ?: $order->billing_address }}
                                    </p>

                                    <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                        {{ $order->shipping_city ?: $order->billing_city }}

                                        @if ($order->shipping_state ?: $order->billing_state)
                                        ,
                                        {{ $order->shipping_state ?: $order->billing_state }}
                                        @endif
                                    </p>

                                    <p class="fs-16 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                        {{ $order->shipping_country ?: $order->billing_country }}

                                        {{ $order->shipping_zip ?: $order->billing_zip }}
                                    </p>

                                </div>

                            </section>

                        </div>
                    </div>
                    <div class="order-thankyou-card right-ctr">
                        <section class="order-section mb-20px">

                            <h2 class="fs-24 text-color-dark mb-10px">
                                Products
                            </h2>

                            <div class="order-products">

                                @forelse ($order->items as $item)

                                <article class="order-product-item">

                                    <div class="order-product-info">

                                        <h3 class="fs-18 text-capitalize text-color-dark mb-10px">
                                            {{ $item->product_title }}
                                        </h3>

                                        @if ($item->sku)
                                        <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                            SKU: {{ $item->sku }}
                                        </p>
                                        @endif

                                        <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                            Quantity: {{ $item->quantity }}
                                        </p>

                                        @php
                                        $savedOptions = $item->getRawOriginal('options');

                                        if (is_string($savedOptions)) {
                                        $decodedOptions = json_decode(
                                        $savedOptions,
                                        true
                                        );

                                        if (
                                        json_last_error()
                                        === JSON_ERROR_NONE
                                        ) {
                                        $savedOptions = $decodedOptions;
                                        }
                                        }

                                        if (is_string($savedOptions)) {
                                        $decodedAgain = json_decode(
                                        $savedOptions,
                                        true
                                        );

                                        if (
                                        json_last_error()
                                        === JSON_ERROR_NONE
                                        ) {
                                        $savedOptions = $decodedAgain;
                                        }
                                        }

                                        if (!is_array($savedOptions)) {
                                        $savedOptions = [];
                                        }
                                        @endphp

                                        @if (!empty($savedOptions))
                                        <div class="order-product-options">

                                            @foreach ($savedOptions as $key => $option)
                                            @php
                                            $optionName = '';
                                            $optionValue = '';

                                            if (is_array($option)) {
                                            $optionName =
                                            $option['option_name']
                                            ?? $option['name']
                                            ?? $option['attribute_name']
                                            ?? '';

                                            $optionValue =
                                            $option['value_label']
                                            ?? $option['label']
                                            ?? $option['value']
                                            ?? $option['attribute_value']
                                            ?? '';
                                            } elseif (
                                            is_string($key)
                                            && !is_numeric($key)
                                            ) {
                                            $optionName = $key;
                                            $optionValue = $option;
                                            }

                                            $optionName =
                                            is_scalar($optionName)
                                            ? trim(
                                            (string) $optionName
                                            )
                                            : '';

                                            $optionValue =
                                            is_scalar($optionValue)
                                            ? trim(
                                            (string) $optionValue
                                            )
                                            : '';
                                            @endphp

                                            @if (
                                            $optionName !== ''
                                            && $optionValue !== ''
                                            )
                                            <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                                <strong>
                                                    {{ $optionName }}:
                                                </strong>

                                                <span>
                                                    {{ $optionValue }}
                                                </span>
                                            </p>
                                            @endif
                                            @endforeach

                                        </div>
                                        @endif

                                    </div>

                                    <strong class="order-product-total fs-18 text-capitalize text-color-dark">
                                        ${{ number_format(
                (float) $item->total,
                2
            ) }}
                                    </strong>

                                </article>

                                @empty

                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    No order items were found.
                                </p>

                                @endforelse

                            </div>

                        </section>

                        <section class="order-section order-totals-section mb-20px">

                            <h2 class="fs-24 text-color-dark mb-10px">
                                Order summary
                            </h2>

                            <div class="order-total-row fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                <span>Subtotal</span>

                                <strong>
                                    ${{ number_format((float) $order->subtotal, 2) }}
                                </strong>
                            </div>

                            @if ((float) $order->discount > 0)
                            <div class="order-total-row fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                <span>Discount</span>

                                <strong>
                                    -${{ number_format((float) $order->discount, 2) }}
                                </strong>
                            </div>
                            @endif

                            <div class="order-total-row fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                <span>Shipping</span>

                                <strong>
                                    @if ((float) $order->shipping > 0)
                                    ${{ number_format((float) $order->shipping, 2) }}
                                    @else
                                    Free
                                    @endif
                                </strong>
                            </div>

                            <div class="order-total-row order-grand-total fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                <span>Total</span>

                                <strong>
                                    ${{ number_format((float) $order->total, 2) }}
                                </strong>
                            </div>

                        </section>

                        @if ($order->order_notes)
                        <section class="order-section">

                            <h2 class="fs-24 text-color-dark mb-10px">
                                Order notes
                            </h2>

                            <p class="fs-15 text-color-body">
                                {{ $order->order_notes }}
                            </p>

                        </section>
                        @endif

                        <div class="order-thank-you-actions d-flex flex-column gap-20px">

                            <a
                                href="{{ route('products.index') }}"
                                class="btn-style-2 text-decoration-none fs-12 text-color-white justify-self-start cursor-pointer">
                                <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Continue shopping</div>
                            </a>

                            <a
                                href="{{ route('home-page') }}"
                                class="order-home-link btn-style-2 fs-12 text-color-white justify-self-start cursor-pointer">
                                <div class="button-text text-uppercase letter-space-3px" style="transform: translate3d(0px, 0px, 0px) scale(1);">Return home</div>
                            </a>

                        </div>
                    </div>











                </div>

            </div>
        </div>
    </div>

</div>

@endsection