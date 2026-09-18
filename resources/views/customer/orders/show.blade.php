@extends('customer.layouts.app')
@section('title', 'Order '.($order->order_number ?: '#'.$order->id))
@section('page-heading', 'Order Details')

@section('content')

@php
    $paymentMetadata = $order->payment_metadata ?? [];

    if (is_string($paymentMetadata)) {
        $decodedPaymentMetadata = json_decode($paymentMetadata, true);

        $paymentMetadata =
            json_last_error() === JSON_ERROR_NONE
            && is_array($decodedPaymentMetadata)
                ? $decodedPaymentMetadata
                : [];
    }

    if (!is_array($paymentMetadata)) {
        $paymentMetadata = [];
    }

    $isBankTransfer =
        ($order->payment_method ?? '') === 'bank_transfer';

    $bankName =
        $paymentMetadata['bank_name'] ?? null;

    $bankAccountName =
        $paymentMetadata['account_name'] ?? null;

    $bankAccountNumber =
        $paymentMetadata['account_number'] ?? null;

    $bankIban =
        $paymentMetadata['iban'] ?? null;

    $bankSwiftCode =
        $paymentMetadata['swift_code'] ?? null;

    $bankBranchName =
        $paymentMetadata['branch_name'] ?? null;

    $bankInstructions =
        $paymentMetadata['instructions'] ?? null;

    $paymentReference =
        $order->payment_reference
        ?: $order->order_number;
@endphp

<header class="customer-page-heading">
    <div>
        <span>Order details</span>

        <h2>
            {{ $order->order_number ?: 'Order #'.$order->id }}
        </h2>

        <p>
            Placed
            {{ $order->created_at?->format('d F Y \a\t g:i A') }}.
        </p>
    </div>

    <div class="customer-heading-actions">
        <a
            class="secondary"
            href="{{ route('customer.orders.invoice', $order->id) }}"
            target="_blank"
        >
            <i class="fa-regular fa-eye"></i>
            View invoice
        </a>

        <a
            href="{{ route('customer.orders.invoice.download', $order->id) }}"
        >
            <i class="fa-solid fa-download"></i>
            Download PDF
        </a>
    </div>
</header>

@if ($isBankTransfer)
<section class="customer-panel customer-bank-transfer-panel">
    <header class="customer-panel-heading">
        <div>
            <span>Payment instructions</span>
            <h3>Bank transfer details</h3>
        </div>

        <span class="customer-bank-icon">
            <i class="fa-solid fa-building-columns"></i>
        </span>
    </header>

    <div class="customer-bank-transfer-body">
        <div class="customer-bank-notice">
            <span>
                <i class="fa-solid fa-circle-info"></i>
            </span>

            <div>
                <strong>
                    Use your Order ID as the payment reference
                </strong>

                <p>
                    Transfer the exact order total using the bank details
                    below. Your order will remain pending until the payment
                    has been verified.
                </p>
            </div>
        </div>

        <div class="customer-bank-details-grid">

            @if ($bankName)
            <div class="customer-bank-detail">
                <small>Bank name</small>
                <strong>{{ $bankName }}</strong>
            </div>
            @endif

            @if ($bankAccountName)
            <div class="customer-bank-detail">
                <small>Account name / title</small>
                <strong>{{ $bankAccountName }}</strong>
            </div>
            @endif

            @if ($bankAccountNumber)
            <div class="customer-bank-detail">
                <small>Account number</small>
                <strong>{{ $bankAccountNumber }}</strong>
            </div>
            @endif

            @if ($bankIban)
            <div class="customer-bank-detail">
                <small>IBAN</small>
                <strong>{{ $bankIban }}</strong>
            </div>
            @endif

            @if ($bankSwiftCode)
            <div class="customer-bank-detail">
                <small>SWIFT / BIC</small>
                <strong>{{ $bankSwiftCode }}</strong>
            </div>
            @endif

            @if ($bankBranchName)
            <div class="customer-bank-detail">
                <small>Branch</small>
                <strong>{{ $bankBranchName }}</strong>
            </div>
            @endif

            <div class="customer-bank-detail customer-bank-reference">
                <small>Payment reference</small>
                <strong>{{ $paymentReference }}</strong>
            </div>

            <div class="customer-bank-detail">
                <small>Amount to transfer</small>
                <strong>
                    {{ strtoupper($order->currency ?: 'USD') }}
                    {{ number_format((float) $order->total, 2) }}
                </strong>
            </div>

        </div>

        @if ($bankInstructions)
        <div class="customer-bank-instructions">
            <span>
                <i class="fa-regular fa-note-sticky"></i>
            </span>

            <div>
                <strong>Payment instructions</strong>
                <p>{!! nl2br(e($bankInstructions)) !!}</p>
            </div>
        </div>
        @endif
    </div>
</section>
@endif

<div class="customer-order-grid">

    <section class="customer-panel">
        <header class="customer-panel-heading">
            <div>
                <span>Purchased products</span>
                <h3>Order items</h3>
            </div>

            <span class="customer-status {{ $order->order_status }}">
                {{ str($order->order_status ?: 'pending')->headline() }}
            </span>
        </header>

        <div class="customer-table-wrap">
            <table class="customer-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($order->items as $item)
                        @php
                            $itemName =
                                $item->product_name
                                ?? $item->name
                                ?? $item->product?->name
                                ?? 'Product';

                            $itemPrice =
                                (float) (
                                    $item->unit_price
                                    ?? $item->price
                                    ?? 0
                                );

                            $itemTotal =
                                (float) (
                                    $item->total
                                    ?? $item->subtotal
                                    ?? (
                                        $itemPrice
                                        * (int) $item->quantity
                                    )
                                );
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $itemName }}</strong>

                                @if($item->variant)
                                    <small>
                                        {{ $item->variant->name
                                            ?? $item->variant->sku
                                            ?? 'Variant' }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                {{ number_format((int) $item->quantity) }}
                            </td>

                            <td>
                                {{ strtoupper($order->currency ?: 'USD') }}
                                {{ number_format($itemPrice, 2) }}
                            </td>

                            <td>
                                <strong>
                                    {{ strtoupper($order->currency ?: 'USD') }}
                                    {{ number_format($itemTotal, 2) }}
                                </strong>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="customer-totals">
            <div>
                <small>Subtotal</small>
                <strong>
                    {{ strtoupper($order->currency ?: 'USD') }}
                    {{ number_format((float) $order->subtotal, 2) }}
                </strong>
            </div>

            <div>
                <small>Discount</small>
                <strong>
                    {{ strtoupper($order->currency ?: 'USD') }}
                    {{ number_format((float) $order->discount, 2) }}
                </strong>
            </div>

            <div>
                <small>Shipping</small>
                <strong>
                    {{ strtoupper($order->currency ?: 'USD') }}
                    {{ number_format(
                        (float) (
                            $order->shipping_price
                            ?? $order->shipping
                        ),
                        2
                    ) }}
                </strong>
            </div>

            <div class="grand-total">
                <small>Order total</small>
                <strong>
                    {{ strtoupper($order->currency ?: 'USD') }}
                    {{ number_format((float) $order->total, 2) }}
                </strong>
            </div>
        </div>
    </section>

    <aside class="customer-panel customer-delivery-card">
        <header class="customer-panel-heading">
            <div>
                <span>Shipment</span>
                <h3>Delivery details</h3>
            </div>
        </header>

        <div class="customer-address">
            <span class="customer-address-icon">
                <i class="fa-solid fa-location-dot"></i>
            </span>

            <p>
                <strong>
                    {{ $order->shipping_name ?: $order->billing_name }}
                </strong>
                <br>

                {{ $order->shipping_address ?: $order->billing_address }}
                <br>

                {{ $order->shipping_city ?: $order->billing_city }}
                {{ $order->shipping_state ?: $order->billing_state }}
                {{ $order->shipping_zip ?: $order->billing_zip }}
                <br>

                {{ $order->shipping_country ?: $order->billing_country }}
            </p>
        </div>

        <dl class="customer-detail-list">
            <div>
                <dt>Payment</dt>
                <dd>
                    {{ str(
                        $order->payment_status ?: 'pending'
                    )->headline() }}
                </dd>
            </div>

            <div>
                <dt>Method</dt>
                <dd>
                    {{ str(
                        $order->payment_method ?: 'Not available'
                    )->headline() }}
                </dd>
            </div>

            @if ($isBankTransfer)
            <div>
                <dt>Payment reference</dt>
                <dd>{{ $paymentReference }}</dd>
            </div>
            @endif

            <div>
                <dt>Tracking</dt>
                <dd>
                    {{ $order->tracking_number ?: 'Pending' }}
                </dd>
            </div>

            <div>
                <dt>Estimated delivery</dt>
                <dd>
                    {{ $order->estimated_delivery ?: 'To be confirmed' }}
                </dd>
            </div>
        </dl>

        @if($order->notes->isNotEmpty())
        <div class="customer-order-updates">
            <h4>Order updates</h4>

            @foreach($order->notes as $note)
                <p>{{ $note->note }}</p>
            @endforeach
        </div>
        @endif
    </aside>

</div>

<style>
.customer-bank-transfer-panel {
    margin-bottom: 20px;
    overflow: hidden;
}

.customer-bank-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    border-radius: 10px;
    background: #eef0ff;
    color: #635bff;
    font-size: 14px;
}

.customer-bank-transfer-body {
    padding: 18px;
}

.customer-bank-notice {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 16px;
    padding: 13px 14px;
    border: 1px solid #e1e4ff;
    border-radius: 10px;
    background: #f7f7ff;
}

.customer-bank-notice > span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    flex: 0 0 30px;
    border-radius: 8px;
    background: #eef0ff;
    color: #635bff;
}

.customer-bank-notice strong,
.customer-bank-instructions strong {
    display: block;
    color: #172033;
    font-size: 12px;
}

.customer-bank-notice p,
.customer-bank-instructions p {
    margin: 4px 0 0;
    color: #727b8c;
    font-size: 11px;
    line-height: 1.6;
}

.customer-bank-details-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.customer-bank-detail {
    min-width: 0;
    padding: 12px 13px;
    border: 1px solid #e8ebf0;
    border-radius: 9px;
    background: #fff;
}

.customer-bank-detail small {
    display: block;
    margin-bottom: 5px;
    color: #8992a2;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
}

.customer-bank-detail strong {
    display: block;
    overflow-wrap: anywhere;
    color: #172033;
    font-size: 11px;
}

.customer-bank-reference {
    border-color: #dcdfff;
    background: #fafaff;
}

.customer-bank-reference strong {
    color: #5149dc;
}

.customer-bank-instructions {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    margin-top: 14px;
    padding: 13px 14px;
    border: 1px solid #e8ebf0;
    border-radius: 9px;
    background: #fafbfc;
}

.customer-bank-instructions > span {
    color: #635bff;
    font-size: 14px;
}

@media (max-width: 650px) {
    .customer-bank-details-grid {
        grid-template-columns: 1fr;
    }

    .customer-bank-transfer-body {
        padding: 14px;
    }
}
</style>

@endsection
