@extends('layouts.app')

@section('title', 'Order Thank You')

@section(
'meta_description',
'Thank you for your order. Review your order, payment, and delivery details.'
)

@section('content')

@php
$paymentMethod = (string) ($order->payment_method ?? '');
$paymentProvider = (string) ($order->payment_provider ?? '');
$resolvedPaymentProvider = $paymentProvider !== ''
    ? $paymentProvider
    : $paymentMethod;

$paymentStatus = (string) ($order->payment_status ?? 'pending');
$orderStatus = (string) ($order->order_status ?? 'pending');

$isStripe = $resolvedPaymentProvider === 'stripe'
    || $paymentMethod === 'stripe';

$isBankTransfer = $resolvedPaymentProvider === 'bank_transfer'
    || $paymentMethod === 'bank_transfer';

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

$paymentMetadata = $order->payment_metadata ?? [];

if (is_string($paymentMetadata)) {
    $decodedPaymentMetadata = json_decode(
        $paymentMetadata,
        true
    );

    $paymentMetadata =
        json_last_error() === JSON_ERROR_NONE
            && is_array($decodedPaymentMetadata)
            ? $decodedPaymentMetadata
            : [];
}

if (!is_array($paymentMetadata)) {
    $paymentMetadata = [];
}

$bankName = $paymentMetadata['bank_name'] ?? null;
$bankAccountName = $paymentMetadata['account_name'] ?? null;
$bankAccountNumber = $paymentMetadata['account_number'] ?? null;
$bankIban = $paymentMetadata['iban'] ?? null;
$bankSwiftCode = $paymentMetadata['swift_code'] ?? null;
$bankBranchName = $paymentMetadata['branch_name'] ?? null;
$bankInstructions = $paymentMetadata['instructions'] ?? null;
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

                <div class="order-confirmation-journey" aria-label="Order progress">
                    <div class="order-journey-item is-complete">
                        <span class="order-journey-icon">
                            <i class="fa-solid fa-check"></i>
                        </span>
                        <div>
                            <strong>Order placed</strong>
                            <small>{{ $order->created_at?->format('d M Y, h:i A') ?: 'Confirmed' }}</small>
                        </div>
                    </div>

                    <span class="order-journey-line"></span>

                    <div class="order-journey-item {{ $isPaid ? 'is-complete' : 'is-current' }}">
                        <span class="order-journey-icon">
                            <i class="fa-solid {{ $isBankTransfer ? 'fa-building-columns' : 'fa-credit-card' }}"></i>
                        </span>
                        <div>
                            <strong>{{ $isPaid ? 'Payment confirmed' : ($isBankTransfer ? 'Transfer verification' : 'Payment confirmation') }}</strong>
                            <small>{{ $isPaid ? 'Successfully received' : ($isBankTransfer ? 'Waiting for bank transfer review' : 'Provider confirmation') }}</small>
                        </div>
                    </div>

                    <span class="order-journey-line"></span>

                    <div class="order-journey-item">
                        <span class="order-journey-icon">
                            <i class="fa-solid fa-box"></i>
                        </span>
                        <div>
                            <strong>Preparing order</strong>
                            <small>We’ll update you as it moves</small>
                        </div>
                    </div>
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
                                Please transfer the exact order total using your Order ID as the payment reference.
                            </p>

                            <div class="bank-transfer-callout">
                                <span class="bank-transfer-callout-icon">
                                    <i class="fa-solid fa-circle-info"></i>
                                </span>
                                <div>
                                    <strong>What happens next?</strong>
                                    <span>Complete the transfer using the details below. Your order stays reserved while the payment is awaiting verification.</span>
                                </div>
                            </div>

                            <div class="bank-transfer-details">

                                @if ($bankName)
                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>Bank:</strong>
                                    {{ $bankName }}
                                </p>
                                @endif

                                @if ($bankAccountName)
                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>Account name:</strong>
                                    {{ $bankAccountName }}
                                </p>
                                @endif

                                @if ($bankAccountNumber)
                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>Account number:</strong>
                                    {{ $bankAccountNumber }}
                                </p>
                                @endif

                                @if ($bankIban)
                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>IBAN:</strong>
                                    {{ $bankIban }}
                                </p>
                                @endif

                                @if ($bankSwiftCode)
                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>SWIFT code:</strong>
                                    {{ $bankSwiftCode }}
                                </p>
                                @endif

                                @if ($bankBranchName)
                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>Branch:</strong>
                                    {{ $bankBranchName }}
                                </p>
                                @endif

                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>Payment reference:</strong>
                                    {{ $order->payment_reference ?: $order->order_number }}
                                </p>

                                <p class="fs-12 text-uppercase letter-space-2px d-flex justify-content-between mb-10px">
                                    <strong>Amount:</strong>
                                    ${{ number_format((float) $order->total, 2) }}
                                </p>

                            </div>

                            @if ($bankInstructions)
                            <div class="bank-transfer-note fs-15 text-color-body mb-10px">
                                {!! nl2br(e($bankInstructions)) !!}
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

                        <div class="order-next-update">
                            <span class="order-next-update-icon">
                                <i class="fa-regular fa-envelope"></i>
                            </span>
                            <div>
                                <strong>We’ll keep you updated</strong>
                                <span>Order and payment updates will be sent to {{ $order->billing_email ?: 'your email address' }}.</span>
                            </div>
                        </div>

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


<style>
/* ============================================================
   ARIZONA OUTFITS — ORDER THANK YOU / CONFIRMATION
   Responsive storefront visual system
============================================================ */

.order-thank-you-page {
    background: #f5f7fb;
    color: #172033;
}

.order-thank-you-page .strip-wrapper {
    background: #fff;
}

.order-thank-you-page .services {
    padding: 34px 0 56px;
}

.order-thank-you-page .service-wrapper > .container {
    width: min(1180px, calc(100% - 40px));
    max-width: 1180px;
    margin: 0 auto;
}

.order-thank-you-page .bread-crumbs {
    margin-bottom: 18px !important;
    color: #8a93a4;
    font-size: 9px !important;
    font-weight: 700;
    letter-spacing: .16em !important;
}

.order-thank-you-page .bread-crumbs a {
    color: #635bff !important;
}

.order-thank-you-header {
    margin-bottom: 22px !important;
    padding: 24px 26px;
    border: 1px solid #e4e8f0;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .03);
}

.order-thank-you-header > .d-flex {
    gap: 13px !important;
    margin-bottom: 8px !important;
}

.order-thank-you-header h1 {
    margin: 0;
    color: #172033 !important;
    font-size: clamp(25px, 3vw, 36px) !important;
    font-weight: 800;
    line-height: 1.1;
    letter-spacing: -.035em;
}

.order-thank-you-header > p {
    max-width: 720px;
    margin: 0;
    color: #7b8497 !important;
    font-size: 13px !important;
    line-height: 1.65;
}

.order-status-icon {
    display: inline-flex;
    flex: 0 0 42px;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 12px;
    font-size: 15px;
}

.order-status-success {
    background: #eaf8f1;
    color: #13875b;
}

.order-status-pending {
    background: #fff4df;
    color: #d88716;
}

.order-status-failed {
    background: #ffeded;
    color: #c13b3b;
}

.order-thank-you-card {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(330px, .65fr);
    gap: 18px;
    align-items: start;
}

.order-thankyou-card {
    min-width: 0;
    overflow: hidden;
    border: 1px solid #e4e8f0;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .03);
}

.order-thankyou-card.left-ctr {
    padding: 0;
}

.order-thankyou-card.right-ctr {
    position: sticky;
    top: 20px;
    padding: 22px;
}

.order-reference-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    border-bottom: 1px solid #edf0f5;
}

.order-reference-item {
    display: flex !important;
    align-items: flex-start;
    flex-direction: column;
    justify-content: flex-start !important;
    gap: 5px;
    min-width: 0;
    margin: 0 !important;
    padding: 17px 20px;
    border-right: 1px solid #edf0f5;
    border-bottom: 1px solid #edf0f5;
    color: #8a93a4;
    font-size: 9px !important;
    font-weight: 700;
    letter-spacing: .11em !important;
}

.order-reference-item:nth-child(2n) {
    border-right: 0;
}

.order-reference-item strong {
    max-width: 100%;
    overflow-wrap: anywhere;
    color: #172033;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .04em;
}

.order-section {
    padding: 22px;
    border-bottom: 1px solid #edf0f5;
}

.right-ctr .order-section {
    padding: 0 0 20px;
    border-bottom: 1px solid #edf0f5;
}

.right-ctr .order-section:last-of-type {
    border-bottom: 0;
}

.order-section h2 {
    margin: 0 0 14px !important;
    color: #172033 !important;
    font-size: 16px !important;
    font-weight: 800;
    letter-spacing: -.015em;
}

.bank-transfer-instructions {
    margin: 18px;
    border: 1px solid #f1d99d;
    border-radius: 12px;
    background: #fffbf0;
}

.bank-transfer-instructions > p {
    margin: 0 0 16px !important;
    color: #6f7684 !important;
    font-size: 12px !important;
    line-height: 1.6;
}

.bank-transfer-details {
    overflow: hidden;
    border: 1px solid #eee3c8;
    border-radius: 9px;
    background: #fff;
}

.bank-transfer-details p {
    display: grid !important;
    grid-template-columns: minmax(125px, .7fr) minmax(0, 1.3fr);
    gap: 15px;
    margin: 0 !important;
    padding: 10px 12px;
    border-bottom: 1px solid #f0eadb;
    color: #4b5565;
    font-size: 10px !important;
    letter-spacing: .06em !important;
}

.bank-transfer-details p:last-child {
    border-bottom: 0;
}

.bank-transfer-details p strong {
    color: #7b8497;
    font-size: 9px;
}

.bank-transfer-details p {
    overflow-wrap: anywhere;
}

.bank-transfer-note {
    margin: 13px 0 0 !important;
    padding: 11px 12px;
    border-radius: 8px;
    background: #fff6dc;
    color: #7b6428 !important;
    font-size: 11px !important;
    line-height: 1.55;
}

.payment-failed-section {
    margin: 18px;
    border: 1px solid #f3c8c8;
    border-radius: 12px;
    background: #fff7f7;
}

.order-details-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.order-details-grid > .order-section {
    min-width: 0;
    border-right: 1px solid #edf0f5;
    border-bottom: 0;
}

.order-details-grid > .order-section:last-child {
    border-right: 0;
}

.order-address p {
    display: block !important;
    margin: 0 0 7px !important;
    color: #667085;
    font-size: 10px !important;
    line-height: 1.55;
    letter-spacing: .07em !important;
    overflow-wrap: anywhere;
}

.order-address p:first-child {
    margin-bottom: 9px !important;
}

.order-address strong {
    color: #172033;
    font-size: 11px;
}

.order-products {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.order-product-item {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 16px;
    padding: 14px 0;
    border-bottom: 1px solid #edf0f5;
}

.order-product-item:first-child {
    padding-top: 2px;
}

.order-product-item:last-child {
    border-bottom: 0;
}

.order-product-info {
    min-width: 0;
}

.order-product-info h3 {
    margin: 0 0 8px !important;
    color: #172033 !important;
    font-size: 13px !important;
    font-weight: 800;
}

.order-product-info p,
.order-product-options p {
    display: flex !important;
    justify-content: flex-start !important;
    gap: 6px;
    margin: 0 0 5px !important;
    color: #7b8497;
    font-size: 9px !important;
    letter-spacing: .06em !important;
}

.order-product-options p strong {
    color: #596273;
}

.order-product-total {
    color: #172033 !important;
    font-size: 12px !important;
    font-weight: 800;
    white-space: nowrap;
}

.order-totals-section {
    margin-bottom: 20px !important;
}

.order-total-row {
    margin: 0 !important;
    padding: 8px 0;
    color: #7b8497;
    font-size: 9px !important;
    letter-spacing: .08em !important;
}

.order-total-row strong {
    color: #172033;
    font-size: 10px;
}

.order-grand-total {
    margin-top: 6px !important;
    padding-top: 13px;
    border-top: 1px solid #e6eaf1;
}

.order-grand-total span,
.order-grand-total strong {
    color: #172033;
    font-size: 13px;
    font-weight: 800;
}

.right-ctr > .order-section:not(.order-totals-section) > p {
    margin: 0;
    color: #667085 !important;
    font-size: 11px !important;
    line-height: 1.6;
}

.order-thank-you-actions {
    gap: 10px !important;
    margin-top: 20px;
}

.order-thank-you-actions .btn-style-2,
.payment-failed-section .btn-style-1 {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 42px;
    margin: 0;
    border-radius: 100px;
    text-align: center;
}

.order-thank-you-actions .button-text {
    font-size: 9px;
    letter-spacing: .16em !important;
}

@media (max-width: 991px) {
    .order-thank-you-page .services {
        padding: 26px 0 42px;
    }

    .order-thank-you-page .service-wrapper > .container {
        width: min(100% - 28px, 820px);
    }

    .order-thank-you-card {
        grid-template-columns: 1fr;
    }

    .order-thankyou-card.right-ctr {
        position: static;
    }
}

@media (max-width: 640px) {
    .order-thank-you-page .services {
        padding: 18px 0 32px;
    }

    .order-thank-you-page .service-wrapper > .container {
        width: min(100% - 20px, 620px);
    }

    .order-thank-you-page .bread-crumbs {
        margin-bottom: 12px !important;
        font-size: 8px !important;
        letter-spacing: .12em !important;
    }

    .order-thank-you-header {
        padding: 18px;
        border-radius: 11px;
    }

    .order-thank-you-header > .d-flex {
        align-items: flex-start !important;
    }

    .order-thank-you-header h1 {
        font-size: 23px !important;
    }

    .order-thank-you-header > p {
        font-size: 11px !important;
    }

    .order-status-icon {
        flex-basis: 36px;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        font-size: 13px;
    }

    .order-thankyou-card {
        border-radius: 11px;
    }

    .order-thankyou-card.right-ctr {
        padding: 17px;
    }

    .order-reference-grid {
        grid-template-columns: 1fr;
    }

    .order-reference-item,
    .order-reference-item:nth-child(2n) {
        padding: 13px 16px;
        border-right: 0;
    }

    .order-section {
        padding: 17px;
    }

    .bank-transfer-instructions,
    .payment-failed-section {
        margin: 14px;
    }

    .bank-transfer-details p {
        grid-template-columns: 1fr;
        gap: 4px;
        padding: 9px 10px;
    }

    .order-details-grid {
        grid-template-columns: 1fr;
    }

    .order-details-grid > .order-section {
        border-right: 0;
        border-bottom: 1px solid #edf0f5;
    }

    .order-details-grid > .order-section:last-child {
        border-bottom: 0;
    }

    .order-product-item {
        grid-template-columns: 1fr;
        gap: 7px;
    }

    .order-product-total {
        justify-self: start;
    }

    .order-thank-you-actions .btn-style-2 {
        min-height: 40px;
    }
}

/* ============================================================
   STEP 26CQ — ENGAGED ORDER CONFIRMATION EXPERIENCE
============================================================ */

.order-thank-you-page {
    background:
        radial-gradient(circle at 8% 0%, rgba(99, 91, 255, .08), transparent 26rem),
        linear-gradient(180deg, #f8f9fc 0%, #f4f6fa 100%);
}

.order-thank-you-header {
    position: relative;
    overflow: hidden;
    padding: 30px 32px;
}

.order-thank-you-header::after {
    position: absolute;
    top: -85px;
    right: -70px;
    width: 210px;
    height: 210px;
    border-radius: 50%;
    background: rgba(99, 91, 255, .06);
    content: "";
    pointer-events: none;
}

.order-thank-you-header > * {
    position: relative;
    z-index: 1;
}

.order-status-icon {
    box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .04);
}

.order-confirmation-journey {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 54px minmax(0, 1fr) 54px minmax(0, 1fr);
    align-items: center;
    gap: 8px;
    margin: 0 0 18px;
    padding: 18px 22px;
    border: 1px solid #e4e8f0;
    border-radius: 14px;
    background: rgba(255, 255, 255, .92);
    box-shadow: 0 1px 2px rgba(15, 23, 42, .03);
}

.order-journey-item {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.order-journey-icon {
    display: inline-flex;
    flex: 0 0 34px;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #f0f2f6;
    color: #7c8596;
    font-size: 11px;
}

.order-journey-item.is-complete .order-journey-icon {
    background: #e8f8ef;
    color: #148457;
}

.order-journey-item.is-current .order-journey-icon {
    background: #eef0ff;
    color: #635bff;
    box-shadow: 0 0 0 4px rgba(99, 91, 255, .08);
}

.order-journey-item strong,
.order-journey-item small {
    display: block;
}

.order-journey-item strong {
    color: #172033;
    font-size: 10px;
    font-weight: 800;
}

.order-journey-item small {
    margin-top: 3px;
    color: #8a93a4;
    font-size: 8px;
    line-height: 1.35;
}

.order-journey-line {
    width: 100%;
    height: 1px;
    background: #e1e5ec;
}

.order-reference-grid {
    background: linear-gradient(180deg, #fff 0%, #fcfcfe 100%);
}

.order-reference-item strong {
    font-size: 13px;
}

.bank-transfer-instructions {
    position: relative;
    overflow: hidden;
    padding: 24px;
}

.bank-transfer-instructions::before {
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #d88716;
    content: "";
}

.bank-transfer-callout {
    display: flex;
    gap: 10px;
    margin: 0 0 14px;
    padding: 11px 12px;
    border-radius: 9px;
    background: rgba(216, 135, 22, .08);
}

.bank-transfer-callout-icon {
    display: inline-flex;
    flex: 0 0 27px;
    align-items: center;
    justify-content: center;
    width: 27px;
    height: 27px;
    border-radius: 8px;
    background: #fff;
    color: #d88716;
    font-size: 11px;
}

.bank-transfer-callout strong,
.bank-transfer-callout span {
    display: block;
}

.bank-transfer-callout strong {
    margin-bottom: 2px;
    color: #55451e;
    font-size: 10px;
}

.bank-transfer-callout span {
    color: #7b6b43;
    font-size: 9px;
    line-height: 1.5;
}

.order-product-item {
    transition: transform .18s ease, background .18s ease;
}

.order-product-item:hover {
    transform: translateX(2px);
}

.order-next-update {
    display: flex;
    gap: 10px;
    margin-top: 18px;
    padding: 12px;
    border: 1px solid #e6e9f7;
    border-radius: 10px;
    background: #f8f8ff;
}

.order-next-update-icon {
    display: inline-flex;
    flex: 0 0 30px;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: #eef0ff;
    color: #635bff;
    font-size: 11px;
}

.order-next-update strong,
.order-next-update span {
    display: block;
}

.order-next-update strong {
    color: #172033;
    font-size: 10px;
}

.order-next-update span {
    margin-top: 3px;
    color: #7b8497;
    font-size: 8px;
    line-height: 1.45;
    overflow-wrap: anywhere;
}

.order-thank-you-actions .btn-style-2:first-child {
    box-shadow: 0 8px 18px rgba(15, 23, 42, .08);
}

@media (max-width: 760px) {
    .order-confirmation-journey {
        grid-template-columns: 1fr;
        gap: 0;
        padding: 16px;
    }

    .order-journey-item {
        align-items: flex-start;
    }

    .order-journey-line {
        width: 1px;
        height: 20px;
        margin: 3px 0 3px 16px;
    }
}

@media (max-width: 640px) {
    .order-thank-you-header {
        padding: 20px;
    }

    .bank-transfer-instructions {
        padding: 18px;
    }

    .order-confirmation-journey {
        border-radius: 11px;
    }

    .order-journey-item strong {
        font-size: 10px;
    }

    .order-journey-item small {
        font-size: 8px;
    }
}

</style>

@endsection