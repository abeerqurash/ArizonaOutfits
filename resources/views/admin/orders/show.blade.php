@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
@php
    /*
    |--------------------------------------------------------------------------
    | Order values
    |--------------------------------------------------------------------------
    */

    $orderNumber =
        $order->order_number
        ?? $order->invoice_number
        ?? ('ORD-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT));

    $orderStatus = strtolower(
        $order->status
        ?? $order->order_status
        ?? 'pending'
    );

    $paymentStatus = strtolower(
        $order->payment_status
        ?? 'pending'
    );

    $customerName =
        $order->customer_name
        ?? trim(
            ($order->billing_first_name ?? '')
            . ' '
            . ($order->billing_last_name ?? '')
        )
        ?: $order->user?->name
        ?: 'Guest customer';

    $customerEmail =
        $order->customer_email
        ?? $order->billing_email
        ?? $order->email
        ?? $order->user?->email;

    $customerPhone =
        $order->customer_phone
        ?? $order->billing_phone
        ?? $order->phone;

    $currencyCode = strtoupper(
        $order->currency
        ?? $order->currency_code
        ?? 'PKR'
    );

    $currencySymbols = [
        'PKR' => 'Rs ',
        'USD' => '$',
        'GBP' => '£',
        'EUR' => '€',
        'AED' => 'AED ',
        'SAR' => 'SAR ',
        'CAD' => 'CA$',
        'AUD' => 'A$',
    ];

    $currencySymbol =
        $currencySymbols[$currencyCode]
        ?? ($currencyCode . ' ');

    $money = function ($amount) use ($currencySymbol) {
        return $currencySymbol . number_format((float) $amount, 2);
    };

    $subtotal = (float) (
        $order->subtotal
        ?? $order->sub_total
        ?? $order->items->sum(
            fn ($item) => (float) $item->subtotal
        )
    );

    $discount = (float) (
        $order->discount
        ?? $order->discount_amount
        ?? 0
    );

    $shipping = (float) (
        $order->shipping
        ?? $order->shipping_amount
        ?? $order->shipping_cost
        ?? 0
    );

    $tax = (float) (
        $order->tax
        ?? $order->tax_amount
        ?? 0
    );

    $grandTotal = (float) (
        $order->total
        ?? $order->grand_total
        ?? ($subtotal - $discount + $shipping + $tax)
    );

    $paidAmount = (float) (
        $order->paid_amount
        ?? (
            $paymentStatus === 'paid'
                ? $grandTotal
                : 0
        )
    );

    $balance = max(
        0,
        $grandTotal - $paidAmount
    );

    /*
    |--------------------------------------------------------------------------
    | Addresses
    |--------------------------------------------------------------------------
    */

    $shippingName = trim(
        ($order->shipping_first_name ?? '')
        . ' '
        . ($order->shipping_last_name ?? '')
    );

    $shippingName =
        $shippingName
        ?: $customerName;

    $shippingAddressLines = array_filter([
        $order->shipping_address
            ?? $order->shipping_address_line_1
            ?? $order->shipping_address1
            ?? null,

        $order->shipping_address_line_2
            ?? $order->shipping_address2
            ?? null,

        trim(
            ($order->shipping_city ?? '')
            . (
                !empty($order->shipping_state)
                    ? ', ' . $order->shipping_state
                    : ''
            )
        ),

        trim(
            ($order->shipping_postcode
                ?? $order->shipping_zip
                ?? '')
            . (
                !empty($order->shipping_country)
                    ? ', ' . $order->shipping_country
                    : ''
            )
        ),
    ]);

    $billingName = trim(
        ($order->billing_first_name ?? '')
        . ' '
        . ($order->billing_last_name ?? '')
    );

    $billingName =
        $billingName
        ?: $customerName;

    $billingAddressLines = array_filter([
        $order->billing_address
            ?? $order->billing_address_line_1
            ?? $order->billing_address1
            ?? null,

        $order->billing_address_line_2
            ?? $order->billing_address2
            ?? null,

        trim(
            ($order->billing_city ?? '')
            . (
                !empty($order->billing_state)
                    ? ', ' . $order->billing_state
                    : ''
            )
        ),

        trim(
            ($order->billing_postcode
                ?? $order->billing_zip
                ?? '')
            . (
                !empty($order->billing_country)
                    ? ', ' . $order->billing_country
                    : ''
            )
        ),
    ]);

    /*
    |--------------------------------------------------------------------------
    | Fulfilment progress
    |--------------------------------------------------------------------------
    */

    $fulfilmentSteps = [
        'pending' => 1,
        'confirmed' => 2,
        'processing' => 2,
        'preparing' => 2,
        'packed' => 3,
        'shipped' => 4,
        'out_for_delivery' => 5,
        'out for delivery' => 5,
        'delivered' => 6,
        'completed' => 6,
    ];

    $currentStep =
        $fulfilmentSteps[$orderStatus]
        ?? 1;

    $isCancelled = in_array(
        $orderStatus,
        [
            'cancelled',
            'canceled',
            'refunded',
            'failed',
        ],
        true
    );

    /*
    |--------------------------------------------------------------------------
    | Status classes
    |--------------------------------------------------------------------------
    */

    $statusClass = match ($orderStatus) {
        'delivered',
        'completed' => 'badge-success',

        'shipped',
        'out_for_delivery',
        'out for delivery' => 'badge-info',

        'processing',
        'preparing',
        'packed',
        'confirmed' => 'badge-warning',

        'cancelled',
        'canceled',
        'failed',
        'refunded' => 'badge-danger',

        default => 'badge-neutral',
    };

    $paymentClass = match ($paymentStatus) {
        'paid',
        'completed' => 'badge-success',

        'partially_paid',
        'partially paid' => 'badge-warning',

        'failed',
        'cancelled',
        'canceled',
        'refunded' => 'badge-danger',

        default => 'badge-neutral',
    };

    $trackingNumber =
        $order->tracking_number
        ?? $order->tracking_code
        ?? null;

    $courier =
        $order->courier
        ?? $order->courier_name
        ?? $order->shipping_provider
        ?? null;

    $paymentMethod =
        $order->payment_method
        ?? $order->payment_gateway
        ?? 'Not specified';

    $transactionId =
        $order->transaction_id
        ?? $order->payment_reference
        ?? null;
@endphp

<div class="premium-order-page">

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="order-alert order-alert-success">
            <span class="order-alert-icon">✓</span>

            <div>
                <strong>Success</strong>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="order-alert order-alert-danger">
            <span class="order-alert-icon">!</span>

            <div>
                <strong>Error</strong>
                <p>{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="order-alert order-alert-danger">
            <span class="order-alert-icon">!</span>

            <div>
                <strong>Please correct the following:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <section class="order-hero">
        <div class="order-hero-top">
            <div class="order-heading-area">
                <a
                    href="{{ route('admin.orders.index') }}"
                    class="order-back-link"
                >
                    <span>←</span>
                    Back to orders
                </a>

                <div class="order-title-line">
                    <h1>{{ $orderNumber }}</h1>

                    <span class="premium-badge {{ $statusClass }}">
                        <span class="badge-dot"></span>
                        {{ ucwords(str_replace('_', ' ', $orderStatus)) }}
                    </span>

                    <span class="premium-badge {{ $paymentClass }}">
                        <span class="badge-dot"></span>
                        Payment:
                        {{ ucwords(str_replace('_', ' ', $paymentStatus)) }}
                    </span>
                </div>

                <p class="order-created-text">
                    Placed
                    {{ optional($order->created_at)->format('d M Y \a\t h:i A') }}

                    @if ($order->created_at)
                        <span>
                            · {{ $order->created_at->diffForHumans() }}
                        </span>
                    @endif
                </p>
            </div>

            <div class="order-header-actions">
                <button
                    type="button"
                    class="premium-button premium-button-light"
                    onclick="window.print()"
                >
                    <span>🖨</span>
                    Print
                </button>

                @if (Route::has('admin.orders.invoice'))
                    <a
                        href="{{ route('admin.orders.invoice', $order) }}"
                        class="premium-button premium-button-light"
                    >
                        <span>↓</span>
                        Invoice
                    </a>
                @endif

                <button
                    type="button"
                    class="premium-button premium-button-menu"
                    id="orderMoreButton"
                >
                    More actions
                    <span>⌄</span>
                </button>

                <div
                    class="order-actions-menu"
                    id="orderActionsMenu"
                >
                    <button
                        type="button"
                        onclick="window.print()"
                    >
                        Print order
                    </button>

                    @if ($customerEmail)
                        <a href="mailto:{{ $customerEmail }}">
                            Email customer
                        </a>
                    @endif

                    <button
                        type="button"
                        class="danger-menu-action"
                        data-open-delete-modal
                    >
                        Delete order
                    </button>
                </div>
            </div>
        </div>

        <div class="order-hero-stats">
            <div class="hero-stat">
                <span class="hero-stat-label">
                    Customer
                </span>

                <strong>{{ $customerName }}</strong>

                <small>
                    {{ $customerEmail ?: 'No email provided' }}
                </small>
            </div>

            <div class="hero-stat">
                <span class="hero-stat-label">
                    Order total
                </span>

                <strong>{{ $money($grandTotal) }}</strong>

                <small>
                    {{ $order->items->sum('quantity') }}
                    item(s)
                </small>
            </div>

            <div class="hero-stat">
                <span class="hero-stat-label">
                    Payment
                </span>

                <strong>
                    {{ ucwords(str_replace('_', ' ', $paymentStatus)) }}
                </strong>

                <small>{{ $paymentMethod }}</small>
            </div>

            <div class="hero-stat">
                <span class="hero-stat-label">
                    Fulfilment
                </span>

                <strong>
                    {{ ucwords(str_replace('_', ' ', $orderStatus)) }}
                </strong>

                <small>
                    {{ $trackingNumber ?: 'No tracking number' }}
                </small>
            </div>
        </div>
    </section>

    {{-- Fulfilment progress --}}
    <section class="premium-panel fulfilment-panel">
        <div class="panel-heading">
            <div>
                <span class="panel-eyebrow">
                    Fulfilment
                </span>

                <h2>Order progress</h2>
            </div>

            @if ($isCancelled)
                <span class="premium-badge badge-danger">
                    Order closed
                </span>
            @else
                <span class="premium-badge {{ $statusClass }}">
                    Current:
                    {{ ucwords(str_replace('_', ' ', $orderStatus)) }}
                </span>
            @endif
        </div>

        @if ($isCancelled)
            <div class="cancelled-order-state">
                <span class="cancelled-state-icon">!</span>

                <div>
                    <strong>
                        This order is
                        {{ str_replace('_', ' ', $orderStatus) }}.
                    </strong>

                    <p>
                        The normal fulfilment process has been stopped.
                    </p>
                </div>
            </div>
        @else
            <div class="fulfilment-progress">
                @php
                    $steps = [
                        [
                            'number' => 1,
                            'label' => 'Order placed',
                            'icon' => '✓',
                        ],
                        [
                            'number' => 2,
                            'label' => 'Processing',
                            'icon' => '⚙',
                        ],
                        [
                            'number' => 3,
                            'label' => 'Packed',
                            'icon' => '□',
                        ],
                        [
                            'number' => 4,
                            'label' => 'Shipped',
                            'icon' => '→',
                        ],
                        [
                            'number' => 5,
                            'label' => 'Out for delivery',
                            'icon' => '⌖',
                        ],
                        [
                            'number' => 6,
                            'label' => 'Delivered',
                            'icon' => '✓',
                        ],
                    ];
                @endphp

                @foreach ($steps as $step)
                    @php
                        $stepClass =
                            $step['number'] < $currentStep
                                ? 'step-complete'
                                : (
                                    $step['number'] === $currentStep
                                        ? 'step-current'
                                        : 'step-pending'
                                );
                    @endphp

                    <div class="fulfilment-step {{ $stepClass }}">
                        <div class="step-marker">
                            {{ $step['number'] < $currentStep
                                ? '✓'
                                : $step['icon'] }}
                        </div>

                        <span>{{ $step['label'] }}</span>
                    </div>

                    @if (!$loop->last)
                        <div
                            class="step-line {{ $step['number'] < $currentStep ? 'line-complete' : '' }}"
                        ></div>
                    @endif
                @endforeach
            </div>
        @endif
    </section>

    <div class="order-layout">

        {{-- Main column --}}
        <main class="order-main-column">

            {{-- Products --}}
            <section class="premium-panel">
                <div class="panel-heading">
                    <div>
                        <span class="panel-eyebrow">
                            Order contents
                        </span>

                        <h2>
                            Products
                            <span class="heading-count">
                                {{ $order->items->count() }}
                            </span>
                        </h2>
                    </div>

                    <span class="panel-heading-meta">
                        {{ $order->items->sum('quantity') }}
                        unit(s)
                    </span>
                </div>

                <div class="premium-product-list">
                    @forelse ($order->items as $item)
                        @php
                            $options = $item->display_options ?? [];

                            $itemSubtotal =
                                $item->subtotal !== null
                                    ? (float) $item->subtotal
                                    : (
                                        (float) $item->price
                                        * (int) $item->quantity
                                    );

                            $productName =
                                $item->product_name
                                ?: $item->product?->title
                                ?: $item->product_title
                                ?: 'Deleted product';

                            $productImage =
                                $item->product?->featured_image_url;

                            $productUrl =
                                $item->product
                                && Route::has('admin.products.edit')
                                    ? route(
                                        'admin.products.edit',
                                        $item->product
                                    )
                                    : null;
                        @endphp

                        <article class="premium-product-card">
                            <div class="premium-product-image">
                                @if ($productImage)
                                    <img
                                        src="{{ $productImage }}"
                                        alt="{{ $productName }}"
                                        loading="lazy"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                    >

                                    <div
                                        class="product-image-placeholder"
                                        style="display:none;"
                                    >
                                        <span>▧</span>
                                    </div>
                                @else
                                    <div class="product-image-placeholder">
                                        <span>▧</span>
                                    </div>
                                @endif

                                <span class="product-quantity-badge">
                                    {{ $item->quantity }}
                                </span>
                            </div>

                            <div class="premium-product-content">
                                <div class="product-primary-info">
                                    @if ($productUrl)
                                        <a
                                            href="{{ $productUrl }}"
                                            class="product-name-link"
                                        >
                                            {{ $productName }}
                                        </a>
                                    @else
                                        <h3>{{ $productName }}</h3>
                                    @endif

                                    <div class="product-meta-row">
                                        <span>
                                            SKU:
                                            <strong>
                                                {{ $item->sku
                                                    ?: $item->product?->sku
                                                    ?: 'N/A' }}
                                            </strong>
                                        </span>

                                        @if ($item->variant_id)
                                            <span>
                                                Variant:
                                                <strong>
                                                    #{{ $item->variant_id }}
                                                </strong>
                                            </span>
                                        @endif
                                    </div>

                                    @if (!empty($options))
                                        <div class="premium-product-options">
                                            @foreach ($options as $option)
                                                <span class="product-option-chip">
                                                    <small>
                                                        {{ $option['name'] }}
                                                    </small>

                                                    <strong>
                                                        {{ $option['value'] }}
                                                    </strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="product-price-information">
                                    <div>
                                        <small>Unit price</small>
                                        <span>
                                            {{ $money($item->price) }}
                                        </span>
                                    </div>

                                    <span class="multiplication-symbol">
                                        ×
                                    </span>

                                    <div>
                                        <small>Quantity</small>
                                        <span>{{ $item->quantity }}</span>
                                    </div>

                                    <div class="product-line-total">
                                        <small>Total</small>
                                        <strong>
                                            {{ $money($itemSubtotal) }}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="premium-empty-state">
                            <span class="empty-state-icon">▧</span>
                            <h3>No products found</h3>
                            <p>
                                This order does not contain any product items.
                            </p>
                        </div>
                    @endforelse
                </div>

                {{-- Totals --}}
                <div class="order-totals-area">
                    <div class="totals-spacer"></div>

                    <div class="totals-card">
                        <div class="total-row">
                            <span>Subtotal</span>
                            <strong>{{ $money($subtotal) }}</strong>
                        </div>

                        <div class="total-row">
                            <span>
                                Discount

                                @if (!empty($order->coupon_code))
                                    <small class="coupon-code">
                                        {{ $order->coupon_code }}
                                    </small>
                                @endif
                            </span>

                            <strong class="{{ $discount > 0 ? 'discount-value' : '' }}">
                                {{ $discount > 0 ? '-' : '' }}
                                {{ $money($discount) }}
                            </strong>
                        </div>

                        <div class="total-row">
                            <span>Shipping</span>
                            <strong>{{ $money($shipping) }}</strong>
                        </div>

                        <div class="total-row">
                            <span>Tax</span>
                            <strong>{{ $money($tax) }}</strong>
                        </div>

                        <div class="total-divider"></div>

                        <div class="total-row grand-total-row">
                            <span>Total</span>

                            <div>
                                <small>{{ $currencyCode }}</small>
                                <strong>{{ $money($grandTotal) }}</strong>
                            </div>
                        </div>

                        @if ($paidAmount > 0)
                            <div class="total-row payment-total-row">
                                <span>Paid</span>
                                <strong>
                                    {{ $money($paidAmount) }}
                                </strong>
                            </div>
                        @endif

                        @if ($balance > 0)
                            <div class="total-row balance-total-row">
                                <span>Balance due</span>
                                <strong>
                                    {{ $money($balance) }}
                                </strong>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Customer and addresses --}}
            <div class="order-info-grid">
                <section class="premium-panel info-card">
                    <div class="panel-heading compact-heading">
                        <div>
                            <span class="panel-eyebrow">
                                Customer
                            </span>

                            <h2>Customer details</h2>
                        </div>

                        <span class="panel-icon">♙</span>
                    </div>

                    <div class="customer-profile">
                        <div class="customer-avatar">
                            {{ strtoupper(
                                mb_substr(
                                    $customerName,
                                    0,
                                    1
                                )
                            ) }}
                        </div>

                        <div>
                            <strong>{{ $customerName }}</strong>

                            <span>
                                {{ $order->user
                                    ? 'Registered customer'
                                    : 'Guest checkout' }}
                            </span>
                        </div>
                    </div>

                    <div class="information-list">
                        <div class="information-row">
                            <span>Email</span>

                            @if ($customerEmail)
                                <a href="mailto:{{ $customerEmail }}">
                                    {{ $customerEmail }}
                                </a>
                            @else
                                <strong>Not provided</strong>
                            @endif
                        </div>

                        <div class="information-row">
                            <span>Phone</span>

                            @if ($customerPhone)
                                <a href="tel:{{ $customerPhone }}">
                                    {{ $customerPhone }}
                                </a>
                            @else
                                <strong>Not provided</strong>
                            @endif
                        </div>

                        <div class="information-row">
                            <span>Customer ID</span>

                            <strong>
                                {{ $order->user_id
                                    ? '#' . $order->user_id
                                    : 'Guest' }}
                            </strong>
                        </div>
                    </div>
                </section>

                <section class="premium-panel info-card">
                    <div class="panel-heading compact-heading">
                        <div>
                            <span class="panel-eyebrow">
                                Delivery
                            </span>

                            <h2>Shipping address</h2>
                        </div>

                        <span class="panel-icon">⌖</span>
                    </div>

                    <address class="premium-address">
                        <strong>{{ $shippingName }}</strong>

                        @forelse ($shippingAddressLines as $line)
                            <span>{{ $line }}</span>
                        @empty
                            <span>No shipping address provided.</span>
                        @endforelse

                        @if (
                            $order->shipping_phone
                            && $order->shipping_phone !== $customerPhone
                        )
                            <a href="tel:{{ $order->shipping_phone }}">
                                {{ $order->shipping_phone }}
                            </a>
                        @endif
                    </address>
                </section>

                <section class="premium-panel info-card">
                    <div class="panel-heading compact-heading">
                        <div>
                            <span class="panel-eyebrow">
                                Billing
                            </span>

                            <h2>Billing address</h2>
                        </div>

                        <span class="panel-icon">▤</span>
                    </div>

                    <address class="premium-address">
                        <strong>{{ $billingName }}</strong>

                        @forelse ($billingAddressLines as $line)
                            <span>{{ $line }}</span>
                        @empty
                            <span>No billing address provided.</span>
                        @endforelse
                    </address>
                </section>

                <section class="premium-panel info-card">
                    <div class="panel-heading compact-heading">
                        <div>
                            <span class="panel-eyebrow">
                                Payment
                            </span>

                            <h2>Payment details</h2>
                        </div>

                        <span class="premium-badge {{ $paymentClass }}">
                            {{ ucwords(
                                str_replace(
                                    '_',
                                    ' ',
                                    $paymentStatus
                                )
                            ) }}
                        </span>
                    </div>

                    <div class="payment-method-card">
                        <span class="payment-method-icon">
                            ▣
                        </span>

                        <div>
                            <strong>
                                {{ ucwords(
                                    str_replace(
                                        ['_', '-'],
                                        ' ',
                                        $paymentMethod
                                    )
                                ) }}
                            </strong>

                            <span>
                                {{ $transactionId
                                    ? 'Transaction recorded'
                                    : 'No transaction ID' }}
                            </span>
                        </div>
                    </div>

                    <div class="information-list">
                        <div class="information-row">
                            <span>Transaction ID</span>

                            <strong class="breakable-value">
                                {{ $transactionId ?: 'N/A' }}
                            </strong>
                        </div>

                        <div class="information-row">
                            <span>Paid amount</span>

                            <strong>
                                {{ $money($paidAmount) }}
                            </strong>
                        </div>

                        <div class="information-row">
                            <span>Balance</span>

                            <strong>
                                {{ $money($balance) }}
                            </strong>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Notes --}}
            <section class="premium-panel">
                <div class="panel-heading">
                    <div>
                        <span class="panel-eyebrow">
                            Internal communication
                        </span>

                        <h2>
                            Order notes
                            <span class="heading-count">
                                {{ $order->notes?->count() ?? 0 }}
                            </span>
                        </h2>
                    </div>
                </div>

                <form
                    action="{{ route('admin.orders.notes.store', $order) }}"
                    method="POST"
                    class="note-form"
                    id="orderNoteForm"
                >
                    @csrf

                    <div class="note-compose">
                        <div class="note-avatar">
                            {{ strtoupper(
                                mb_substr(
                                    auth()->user()?->name
                                    ?? 'A',
                                    0,
                                    1
                                )
                            ) }}
                        </div>

                        <div class="note-input-wrapper">
                            <textarea
                                name="note"
                                id="orderNoteInput"
                                rows="3"
                                placeholder="Add a private note about this order..."
                                required
                            >{{ old('note') }}</textarea>

                            <div class="note-form-footer">
                                <small>
                                    Only administrators can see this note.
                                </small>

                                <button
                                    type="submit"
                                    class="premium-button premium-button-primary"
                                    id="addNoteButton"
                                >
                                    Add note
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div
                    class="notes-container"
                    id="orderNotesContainer"
                >
                    @forelse ($order->notes ?? [] as $note)
                        <article
                            class="note-message"
                            data-note-id="{{ $note->id }}"
                        >
                            <div class="note-avatar">
                                {{ strtoupper(
                                    mb_substr(
                                        $note->user?->name
                                        ?? 'A',
                                        0,
                                        1
                                    )
                                ) }}
                            </div>

                            <div class="note-message-content">
                                <div class="note-message-header">
                                    <div>
                                        <strong>
                                            {{ $note->user?->name
                                                ?? 'Administrator' }}
                                        </strong>

                                        <span>
                                            {{ optional($note->created_at)
                                                ->format('d M Y, h:i A') }}
                                        </span>
                                    </div>

                                    <form
                                        action="{{ route(
                                            'admin.orders.notes.destroy',
                                            [$order, $note]
                                        ) }}"
                                        method="POST"
                                        class="delete-note-form"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="delete-note-button"
                                            title="Delete note"
                                        >
                                            ×
                                        </button>
                                    </form>
                                </div>

                                <p>{{ $note->note }}</p>
                            </div>
                        </article>
                    @empty
                        <div
                            class="premium-empty-state small-empty-state"
                            id="notesEmptyState"
                        >
                            <span class="empty-state-icon">✎</span>

                            <h3>No notes yet</h3>

                            <p>
                                Add an internal note to keep your team informed.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Activity timeline --}}
            <section class="premium-panel">
                <div class="panel-heading">
                    <div>
                        <span class="panel-eyebrow">
                            Order history
                        </span>

                        <h2>Activity timeline</h2>
                    </div>

                    <span class="panel-heading-meta">
                        Latest first
                    </span>
                </div>

                <div class="activity-timeline">
                    @forelse ($order->activities ?? [] as $activity)
                        @php
                            $activityType = strtolower(
                                $activity->type
                                ?? $activity->event
                                ?? 'update'
                            );

                            $activityIcon = match (true) {
                                str_contains($activityType, 'payment') => '₨',
                                str_contains($activityType, 'status') => '↻',
                                str_contains($activityType, 'tracking') => '→',
                                str_contains($activityType, 'note') => '✎',
                                str_contains($activityType, 'create') => '+',
                                str_contains($activityType, 'delete') => '×',
                                default => '•',
                            };
                        @endphp

                        <article class="activity-item">
                            <div class="activity-marker">
                                {{ $activityIcon }}
                            </div>

                            <div class="activity-content">
                                <div class="activity-title-row">
                                    <strong>
                                        {{ $activity->description
                                            ?? $activity->message
                                            ?? ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $activityType
                                                )
                                            ) }}
                                    </strong>

                                    <span>
                                        {{ optional($activity->created_at)
                                            ->diffForHumans() }}
                                    </span>
                                </div>

                                @if (
                                    !empty($activity->old_value)
                                    || !empty($activity->new_value)
                                )
                                    <div class="activity-change">
                                        @if (!empty($activity->old_value))
                                            <span>
                                                {{ $activity->old_value }}
                                            </span>
                                        @endif

                                        @if (
                                            !empty($activity->old_value)
                                            && !empty($activity->new_value)
                                        )
                                            <b>→</b>
                                        @endif

                                        @if (!empty($activity->new_value))
                                            <span class="new-activity-value">
                                                {{ $activity->new_value }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <small>
                                    By
                                    {{ $activity->user?->name
                                        ?? 'System' }}

                                    ·

                                    {{ optional($activity->created_at)
                                        ->format('d M Y, h:i A') }}
                                </small>
                            </div>
                        </article>
                    @empty
                        <div class="premium-empty-state">
                            <span class="empty-state-icon">↻</span>

                            <h3>No activity recorded</h3>

                            <p>
                                Order changes will appear here.
                            </p>
                        </div>
                    @endforelse

                    <article class="activity-item activity-order-created">
                        <div class="activity-marker">
                            ✓
                        </div>

                        <div class="activity-content">
                            <div class="activity-title-row">
                                <strong>Order created</strong>

                                <span>
                                    {{ optional($order->created_at)
                                        ->diffForHumans() }}
                                </span>
                            </div>

                            <small>
                                {{ optional($order->created_at)
                                    ->format('d M Y, h:i A') }}
                            </small>
                        </div>
                    </article>
                </div>
            </section>
        </main>

        {{-- Sticky sidebar --}}
        <aside class="order-sidebar">
            <form
                action="{{ route('admin.orders.update', $order) }}"
                method="POST"
                class="premium-panel order-management-panel"
                id="orderManagementForm"
            >
                @csrf
                @method('PUT')

                <div class="panel-heading">
                    <div>
                        <span class="panel-eyebrow">
                            Management
                        </span>

                        <h2>Update order</h2>
                    </div>

                    <span class="panel-icon">⚙</span>
                </div>

                <div class="premium-form-group">
                    <label for="status">
                        Order status
                    </label>

                    <select
                        name="status"
                        id="status"
                        class="premium-select"
                    >
                        @foreach ([
                            'pending' => 'Pending',
                            'confirmed' => 'Confirmed',
                            'processing' => 'Processing',
                            'packed' => 'Packed',
                            'shipped' => 'Shipped',
                            'out_for_delivery' => 'Out for delivery',
                            'delivered' => 'Delivered',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled',
                            'refunded' => 'Refunded',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(
                                    old(
                                        'status',
                                        $orderStatus
                                    ) === $value
                                )
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="premium-form-group">
                    <label for="payment_status">
                        Payment status
                    </label>

                    <select
                        name="payment_status"
                        id="payment_status"
                        class="premium-select"
                    >
                        @foreach ([
                            'pending' => 'Pending',
                            'paid' => 'Paid',
                            'partially_paid' => 'Partially paid',
                            'failed' => 'Failed',
                            'refunded' => 'Refunded',
                            'cancelled' => 'Cancelled',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(
                                    old(
                                        'payment_status',
                                        $paymentStatus
                                    ) === $value
                                )
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-divider"></div>

                <div class="premium-form-group">
                    <label for="courier">
                        Courier
                    </label>

                    <input
                        type="text"
                        name="courier"
                        id="courier"
                        class="premium-input"
                        value="{{ old('courier', $courier) }}"
                        placeholder="For example, DHL"
                    >
                </div>

                <div class="premium-form-group">
                    <label for="tracking_number">
                        Tracking number
                    </label>

                    <div class="input-with-action">
                        <input
                            type="text"
                            name="tracking_number"
                            id="tracking_number"
                            class="premium-input"
                            value="{{ old(
                                'tracking_number',
                                $trackingNumber
                            ) }}"
                            placeholder="Enter tracking number"
                        >

                        <button
                            type="button"
                            id="copyTrackingButton"
                            title="Copy tracking number"
                        >
                            Copy
                        </button>
                    </div>
                </div>

                @if (
                    property_exists($order, 'admin_note')
                    || array_key_exists(
                        'admin_note',
                        $order->getAttributes()
                    )
                )
                    <div class="premium-form-group">
                        <label for="admin_note">
                            Admin message
                        </label>

                        <textarea
                            name="admin_note"
                            id="admin_note"
                            class="premium-textarea"
                            rows="4"
                            placeholder="Optional internal message"
                        >{{ old(
                            'admin_note',
                            $order->admin_note
                        ) }}</textarea>
                    </div>
                @endif

                <label class="premium-checkbox">
                    <input
                        type="checkbox"
                        name="notify_customer"
                        value="1"
                        @checked(old('notify_customer'))
                    >

                    <span class="custom-checkbox"></span>

                    <span>
                        Notify customer about this update
                    </span>
                </label>

                <button
                    type="submit"
                    class="premium-button premium-button-primary full-width-button"
                    id="saveOrderButton"
                >
                    Save changes
                </button>

                <small class="management-help-text">
                    Changes are recorded in the activity timeline.
                </small>
            </form>

            {{-- Shipment --}}
            <section class="premium-panel sidebar-summary-panel">
                <div class="panel-heading compact-heading">
                    <div>
                        <span class="panel-eyebrow">
                            Shipment
                        </span>

                        <h2>Tracking</h2>
                    </div>

                    <span class="panel-icon">→</span>
                </div>

                @if ($trackingNumber)
                    <div class="tracking-card">
                        <span>Tracking number</span>

                        <strong id="trackingDisplay">
                            {{ $trackingNumber }}
                        </strong>

                        <div class="tracking-card-footer">
                            <span>
                                {{ $courier ?: 'Courier not specified' }}
                            </span>

                            <button
                                type="button"
                                id="copyTrackingCardButton"
                            >
                                Copy
                            </button>
                        </div>
                    </div>
                @else
                    <div class="sidebar-empty-message">
                        <span>→</span>

                        <p>
                            Add a tracking number when the order ships.
                        </p>
                    </div>
                @endif
            </section>

            {{-- Payment summary --}}
            <section class="premium-panel sidebar-summary-panel">
                <div class="panel-heading compact-heading">
                    <div>
                        <span class="panel-eyebrow">
                            Financial
                        </span>

                        <h2>Payment summary</h2>
                    </div>

                    <span class="premium-badge {{ $paymentClass }}">
                        {{ ucwords(
                            str_replace(
                                '_',
                                ' ',
                                $paymentStatus
                            )
                        ) }}
                    </span>
                </div>

                <div class="sidebar-payment-summary">
                    <div>
                        <span>Order total</span>
                        <strong>{{ $money($grandTotal) }}</strong>
                    </div>

                    <div>
                        <span>Paid</span>
                        <strong>{{ $money($paidAmount) }}</strong>
                    </div>

                    <div class="sidebar-balance-row">
                        <span>Balance</span>
                        <strong>{{ $money($balance) }}</strong>
                    </div>
                </div>
            </section>

            {{-- Danger zone --}}
            <section class="premium-panel danger-zone-panel">
                <div>
                    <span class="panel-eyebrow danger-eyebrow">
                        Danger zone
                    </span>

                    <h2>Delete order</h2>

                    <p>
                        This permanently removes the order and its associated
                        records.
                    </p>
                </div>

                <button
                    type="button"
                    class="premium-button premium-button-danger full-width-button"
                    data-open-delete-modal
                >
                    Delete this order
                </button>
            </section>
        </aside>
    </div>
</div>

{{-- Delete confirmation modal --}}
<div
    class="premium-modal"
    id="deleteOrderModal"
    aria-hidden="true"
>
    <div
        class="premium-modal-backdrop"
        data-close-delete-modal
    ></div>

    <div
        class="premium-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="deleteOrderTitle"
    >
        <button
            type="button"
            class="modal-close-button"
            data-close-delete-modal
        >
            ×
        </button>

        <div class="modal-danger-icon">
            !
        </div>

        <h2 id="deleteOrderTitle">
            Delete {{ $orderNumber }}?
        </h2>

        <p>
            This action cannot be undone. The order, items, notes and activity
            records may be permanently removed.
        </p>

        <form
            action="{{ route('admin.orders.destroy', $order) }}"
            method="POST"
        >
            @csrf
            @method('DELETE')

            <div class="modal-actions">
                <button
                    type="button"
                    class="premium-button premium-button-light"
                    data-close-delete-modal
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="premium-button premium-button-danger"
                >
                    Delete order
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    :root {
        --order-bg: #f5f7fb;
        --order-card: #ffffff;
        --order-border: #e6e9f0;
        --order-text: #182033;
        --order-muted: #737b8c;
        --order-primary: #4f46e5;
        --order-primary-dark: #3730a3;
        --order-success: #15803d;
        --order-success-bg: #ecfdf3;
        --order-warning: #b45309;
        --order-warning-bg: #fff7ed;
        --order-danger: #dc2626;
        --order-danger-bg: #fef2f2;
        --order-info: #0369a1;
        --order-info-bg: #eff6ff;
        --order-shadow:
            0 12px 35px rgba(15, 23, 42, 0.06);
    }

    .premium-order-page {
        max-width: 1550px;
        margin: 0 auto;
        padding: 26px;
        color: var(--order-text);
    }

    .premium-order-page *,
    .premium-order-page *::before,
    .premium-order-page *::after {
        box-sizing: border-box;
    }

    .order-alert {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px 18px;
        margin-bottom: 20px;
        border: 1px solid;
        border-radius: 14px;
    }

    .order-alert p {
        margin: 3px 0 0;
    }

    .order-alert ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .order-alert-success {
        color: var(--order-success);
        background: var(--order-success-bg);
        border-color: #bbf7d0;
    }

    .order-alert-danger {
        color: var(--order-danger);
        background: var(--order-danger-bg);
        border-color: #fecaca;
    }

    .order-alert-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 26px;
        width: 26px;
        height: 26px;
        color: #fff;
        background: currentColor;
        border-radius: 50%;
        font-weight: 800;
    }

    .order-alert-icon::first-letter {
        color: #fff;
    }

    .order-hero {
        padding: 26px;
        margin-bottom: 20px;
        background:
            radial-gradient(
                circle at top right,
                rgba(99, 102, 241, 0.15),
                transparent 31%
            ),
            linear-gradient(145deg, #ffffff 0%, #f8f9ff 100%);
        border: 1px solid var(--order-border);
        border-radius: 22px;
        box-shadow: var(--order-shadow);
    }

    .order-hero-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 25px;
    }

    .order-back-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 13px;
        color: var(--order-muted);
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
    }

    .order-back-link:hover {
        color: var(--order-primary);
    }

    .order-title-line {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
    }

    .order-title-line h1 {
        margin: 0 7px 0 0;
        color: var(--order-text);
        font-size: clamp(28px, 4vw, 40px);
        line-height: 1.1;
        letter-spacing: -1.3px;
    }

    .order-created-text {
        margin: 12px 0 0;
        color: var(--order-muted);
        font-size: 14px;
    }

    .premium-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 30px;
        padding: 6px 11px;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
    }

    .badge-dot {
        width: 7px;
        height: 7px;
        background: currentColor;
        border-radius: 50%;
    }

    .badge-success {
        color: var(--order-success);
        background: var(--order-success-bg);
        border-color: #bbf7d0;
    }

    .badge-warning {
        color: var(--order-warning);
        background: var(--order-warning-bg);
        border-color: #fed7aa;
    }

    .badge-danger {
        color: var(--order-danger);
        background: var(--order-danger-bg);
        border-color: #fecaca;
    }

    .badge-info {
        color: var(--order-info);
        background: var(--order-info-bg);
        border-color: #bfdbfe;
    }

    .badge-neutral {
        color: #596174;
        background: #f4f6f8;
        border-color: #e3e6eb;
    }

    .order-header-actions {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        gap: 9px;
    }

    .premium-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 10px 15px;
        border: 1px solid transparent;
        border-radius: 11px;
        font: inherit;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease,
            background-color 0.2s ease;
    }

    .premium-button:hover {
        transform: translateY(-1px);
    }

    .premium-button-light {
        color: var(--order-text);
        background: #fff;
        border-color: var(--order-border);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    }

    .premium-button-light:hover {
        background: #f8fafc;
    }

    .premium-button-menu {
        color: #fff;
        background: #1f2937;
    }

    .premium-button-primary {
        color: #fff;
        background:
            linear-gradient(
                135deg,
                var(--order-primary),
                #6366f1
            );
        box-shadow:
            0 9px 20px rgba(79, 70, 229, 0.2);
    }

    .premium-button-primary:hover {
        background:
            linear-gradient(
                135deg,
                var(--order-primary-dark),
                var(--order-primary)
            );
    }

    .premium-button-danger {
        color: #fff;
        background: var(--order-danger);
    }

    .order-actions-menu {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        z-index: 20;
        display: none;
        min-width: 190px;
        padding: 7px;
        background: #fff;
        border: 1px solid var(--order-border);
        border-radius: 12px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.15);
    }

    .order-actions-menu.is-open {
        display: block;
    }

    .order-actions-menu a,
    .order-actions-menu button {
        display: block;
        width: 100%;
        padding: 10px 11px;
        color: var(--order-text);
        background: transparent;
        border: 0;
        border-radius: 8px;
        font: inherit;
        font-size: 13px;
        font-weight: 700;
        text-align: left;
        text-decoration: none;
        cursor: pointer;
    }

    .order-actions-menu a:hover,
    .order-actions-menu button:hover {
        background: #f5f7fb;
    }

    .order-actions-menu .danger-menu-action {
        color: var(--order-danger);
    }

    .order-hero-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-top: 25px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.75);
        border: 1px solid var(--order-border);
        border-radius: 15px;
    }

    .hero-stat {
        min-width: 0;
        padding: 17px 19px;
        border-right: 1px solid var(--order-border);
    }

    .hero-stat:last-child {
        border-right: 0;
    }

    .hero-stat-label,
    .hero-stat small {
        display: block;
        color: var(--order-muted);
        font-size: 12px;
    }

    .hero-stat strong {
        display: block;
        margin: 6px 0 5px;
        overflow: hidden;
        font-size: 15px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .premium-panel {
        padding: 22px;
        background: var(--order-card);
        border: 1px solid var(--order-border);
        border-radius: 18px;
        box-shadow: var(--order-shadow);
    }

    .fulfilment-panel {
        margin-bottom: 20px;
    }

    .panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 20px;
    }

    .compact-heading {
        margin-bottom: 17px;
    }

    .panel-heading h2 {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 3px 0 0;
        color: var(--order-text);
        font-size: 19px;
        line-height: 1.25;
    }

    .panel-eyebrow {
        display: block;
        color: var(--order-muted);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 1.1px;
        text-transform: uppercase;
    }

    .panel-heading-meta {
        color: var(--order-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .panel-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        color: var(--order-primary);
        background: #eef2ff;
        border-radius: 10px;
        font-weight: 900;
    }

    .heading-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 25px;
        height: 25px;
        padding: 0 7px;
        color: var(--order-primary);
        background: #eef2ff;
        border-radius: 999px;
        font-size: 11px;
    }

    .fulfilment-progress {
        display: flex;
        align-items: flex-start;
        padding: 8px 4px 2px;
        overflow-x: auto;
    }

    .fulfilment-step {
        flex: 0 0 105px;
        text-align: center;
    }

    .step-marker {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        margin: 0 auto 10px;
        border: 2px solid;
        border-radius: 50%;
        font-size: 13px;
        font-weight: 900;
    }

    .fulfilment-step span {
        display: block;
        color: var(--order-muted);
        font-size: 11px;
        font-weight: 800;
        line-height: 1.35;
    }

    .step-complete .step-marker {
        color: #fff;
        background: var(--order-success);
        border-color: var(--order-success);
    }

    .step-complete span {
        color: var(--order-success);
    }

    .step-current .step-marker {
        color: #fff;
        background: var(--order-primary);
        border-color: var(--order-primary);
        box-shadow:
            0 0 0 6px rgba(79, 70, 229, 0.1);
    }

    .step-current span {
        color: var(--order-primary);
    }

    .step-pending .step-marker {
        color: #a5adbb;
        background: #fff;
        border-color: #dfe3ea;
    }

    .step-line {
        flex: 1 0 30px;
        height: 2px;
        margin-top: 18px;
        background: #e6e9ef;
    }

    .step-line.line-complete {
        background: var(--order-success);
    }

    .cancelled-order-state {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px;
        color: var(--order-danger);
        background: var(--order-danger-bg);
        border: 1px solid #fecaca;
        border-radius: 14px;
    }

    .cancelled-order-state p {
        margin: 4px 0 0;
        color: #991b1b;
    }

    .cancelled-state-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        color: #fff;
        background: var(--order-danger);
        border-radius: 50%;
        font-weight: 900;
    }

    .order-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 355px;
        align-items: start;
        gap: 20px;
    }

    .order-main-column {
        display: grid;
        min-width: 0;
        gap: 20px;
    }

    .order-sidebar {
        position: sticky;
        top: 20px;
        display: grid;
        gap: 20px;
    }

    .premium-product-list {
        border: 1px solid var(--order-border);
        border-radius: 15px;
        overflow: hidden;
    }

    .premium-product-card {
        display: flex;
        gap: 17px;
        padding: 18px;
        border-bottom: 1px solid var(--order-border);
    }

    .premium-product-card:last-child {
        border-bottom: 0;
    }

    .premium-product-image {
        position: relative;
        flex: 0 0 86px;
        width: 86px;
        height: 96px;
    }

    .premium-product-image img,
    .product-image-placeholder {
        width: 100%;
        height: 100%;
        border: 1px solid var(--order-border);
        border-radius: 12px;
        object-fit: cover;
    }

    .product-image-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        background:
            linear-gradient(145deg, #f8fafc, #eef2f7);
        font-size: 24px;
    }

    .product-quantity-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 25px;
        height: 25px;
        padding: 0 7px;
        color: #fff;
        background: #1f2937;
        border: 2px solid #fff;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
    }

    .premium-product-content {
        display: flex;
        flex: 1;
        align-items: center;
        justify-content: space-between;
        min-width: 0;
        gap: 20px;
    }

    .product-primary-info {
        min-width: 0;
    }

    .product-primary-info h3,
    .product-name-link {
        display: block;
        margin: 0;
        color: var(--order-text);
        font-size: 15px;
        font-weight: 850;
        line-height: 1.4;
        text-decoration: none;
    }

    .product-name-link:hover {
        color: var(--order-primary);
    }

    .product-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 7px 15px;
        margin-top: 7px;
        color: var(--order-muted);
        font-size: 11px;
    }

    .premium-product-options {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 12px;
    }

    .product-option-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 9px;
        color: #4b5563;
        background: #f7f8fb;
        border: 1px solid #e8eaf0;
        border-radius: 8px;
        font-size: 11px;
    }

    .product-option-chip small {
        color: var(--order-muted);
        font-size: inherit;
    }

    .product-option-chip strong {
        color: var(--order-text);
    }

    .product-price-information {
        display: flex;
        align-items: center;
        gap: 14px;
        white-space: nowrap;
    }

    .product-price-information > div {
        display: grid;
        gap: 4px;
    }

    .product-price-information small {
        color: var(--order-muted);
        font-size: 10px;
    }

    .product-price-information span {
        color: #4b5563;
        font-size: 12px;
        font-weight: 700;
    }

    .product-price-information .multiplication-symbol {
        color: #b3b9c5;
    }

    .product-price-information .product-line-total {
        min-width: 100px;
        padding-left: 15px;
        border-left: 1px solid var(--order-border);
        text-align: right;
    }

    .product-line-total strong {
        color: var(--order-text);
        font-size: 15px;
    }

    .order-totals-area {
        display: grid;
        grid-template-columns: 1fr minmax(310px, 420px);
        gap: 30px;
        margin-top: 20px;
    }

    .totals-card {
        padding: 18px;
        background: #fafbfc;
        border: 1px solid var(--order-border);
        border-radius: 14px;
    }

    .total-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 8px 0;
        color: var(--order-muted);
        font-size: 13px;
    }

    .total-row strong {
        color: var(--order-text);
    }

    .coupon-code {
        display: inline-flex;
        padding: 3px 6px;
        margin-left: 5px;
        color: var(--order-primary);
        background: #eef2ff;
        border-radius: 5px;
        font-size: 9px;
        font-weight: 800;
    }

    .discount-value {
        color: var(--order-success) !important;
    }

    .total-divider {
        height: 1px;
        margin: 10px 0;
        background: var(--order-border);
    }

    .grand-total-row {
        align-items: flex-end;
        color: var(--order-text);
        font-size: 15px;
        font-weight: 850;
    }

    .grand-total-row > div {
        text-align: right;
    }

    .grand-total-row small {
        display: block;
        margin-bottom: 2px;
        color: var(--order-muted);
        font-size: 9px;
    }

    .grand-total-row strong {
        font-size: 22px;
    }

    .payment-total-row strong {
        color: var(--order-success);
    }

    .balance-total-row {
        color: var(--order-danger);
    }

    .balance-total-row strong {
        color: var(--order-danger);
    }

    .order-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .info-card {
        min-width: 0;
    }

    .customer-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 17px;
        margin-bottom: 7px;
        border-bottom: 1px solid var(--order-border);
    }

    .customer-avatar,
    .note-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        color: #fff;
        background:
            linear-gradient(
                135deg,
                var(--order-primary),
                #818cf8
            );
        border-radius: 12px;
        font-size: 15px;
        font-weight: 900;
    }

    .customer-profile strong,
    .customer-profile span {
        display: block;
    }

    .customer-profile span {
        margin-top: 3px;
        color: var(--order-muted);
        font-size: 11px;
    }

    .information-list {
        display: grid;
    }

    .information-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 10px 0;
        border-bottom: 1px solid #f0f1f4;
        font-size: 12px;
    }

    .information-row:last-child {
        border-bottom: 0;
    }

    .information-row > span {
        color: var(--order-muted);
    }

    .information-row a,
    .information-row strong {
        max-width: 65%;
        color: var(--order-text);
        font-weight: 750;
        text-align: right;
        text-decoration: none;
    }

    .information-row a:hover {
        color: var(--order-primary);
    }

    .breakable-value {
        overflow-wrap: anywhere;
    }

    .premium-address {
        display: grid;
        gap: 6px;
        margin: 0;
        color: var(--order-muted);
        font-size: 13px;
        font-style: normal;
        line-height: 1.5;
    }

    .premium-address strong {
        margin-bottom: 3px;
        color: var(--order-text);
        font-size: 14px;
    }

    .premium-address a {
        color: var(--order-primary);
        text-decoration: none;
    }

    .payment-method-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px;
        margin-bottom: 8px;
        background: #f8f9fc;
        border: 1px solid var(--order-border);
        border-radius: 11px;
    }

    .payment-method-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        color: var(--order-primary);
        background: #eef2ff;
        border-radius: 9px;
    }

    .payment-method-card strong,
    .payment-method-card span {
        display: block;
    }

    .payment-method-card > div > span {
        margin-top: 3px;
        color: var(--order-muted);
        font-size: 10px;
    }

    .note-form {
        margin-bottom: 22px;
    }

    .note-compose {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .note-input-wrapper {
        flex: 1;
        overflow: hidden;
        border: 1px solid var(--order-border);
        border-radius: 13px;
        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .note-input-wrapper:focus-within {
        border-color: #a5b4fc;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08);
    }

    .note-input-wrapper textarea {
        display: block;
        width: 100%;
        padding: 14px;
        color: var(--order-text);
        background: #fff;
        border: 0;
        outline: 0;
        font: inherit;
        font-size: 13px;
        resize: vertical;
    }

    .note-form-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 10px 12px;
        background: #fafbfc;
        border-top: 1px solid var(--order-border);
    }

    .note-form-footer small {
        color: var(--order-muted);
        font-size: 10px;
    }

    .notes-container {
        display: grid;
        gap: 15px;
    }

    .note-message {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .note-message-content {
        flex: 1;
        padding: 14px;
        background: #f8f9fc;
        border: 1px solid var(--order-border);
        border-radius: 4px 14px 14px 14px;
    }

    .note-message-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 8px;
    }

    .note-message-header strong,
    .note-message-header span {
        display: block;
    }

    .note-message-header span {
        margin-top: 3px;
        color: var(--order-muted);
        font-size: 10px;
    }

    .note-message-content p {
        margin: 0;
        color: #4b5563;
        font-size: 13px;
        line-height: 1.65;
        white-space: pre-line;
    }

    .delete-note-form {
        margin: 0;
    }

    .delete-note-button {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 27px;
        height: 27px;
        color: #9ca3af;
        background: transparent;
        border: 0;
        border-radius: 7px;
        font-size: 19px;
        cursor: pointer;
    }

    .delete-note-button:hover {
        color: var(--order-danger);
        background: var(--order-danger-bg);
    }

    .activity-timeline {
        position: relative;
        display: grid;
        gap: 0;
    }

    .activity-timeline::before {
        position: absolute;
        top: 19px;
        bottom: 19px;
        left: 18px;
        width: 2px;
        background: #e9ebf0;
        content: '';
    }

    .activity-item {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding-bottom: 22px;
    }

    .activity-item:last-child {
        padding-bottom: 0;
    }

    .activity-marker {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        color: var(--order-primary);
        background: #eef2ff;
        border: 4px solid #fff;
        border-radius: 50%;
        font-size: 12px;
        font-weight: 900;
        box-shadow: 0 0 0 1px var(--order-border);
    }

    .activity-order-created .activity-marker {
        color: var(--order-success);
        background: var(--order-success-bg);
    }

    .activity-content {
        flex: 1;
        min-width: 0;
        padding-top: 4px;
    }

    .activity-title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
    }

    .activity-title-row strong {
        font-size: 13px;
    }

    .activity-title-row > span {
        flex: 0 0 auto;
        color: var(--order-muted);
        font-size: 10px;
    }

    .activity-content > small {
        display: block;
        margin-top: 5px;
        color: var(--order-muted);
        font-size: 10px;
    }

    .activity-change {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
        margin-top: 8px;
    }

    .activity-change span {
        padding: 4px 7px;
        color: #5f6776;
        background: #f3f4f6;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
    }

    .activity-change .new-activity-value {
        color: var(--order-primary);
        background: #eef2ff;
    }

    .premium-empty-state {
        padding: 35px 20px;
        color: var(--order-muted);
        text-align: center;
    }

    .small-empty-state {
        padding: 25px 20px;
        background: #fafbfc;
        border: 1px dashed #d9dde5;
        border-radius: 13px;
    }

    .empty-state-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        color: var(--order-primary);
        background: #eef2ff;
        border-radius: 14px;
        font-size: 20px;
    }

    .premium-empty-state h3 {
        margin: 0 0 6px;
        color: var(--order-text);
        font-size: 15px;
    }

    .premium-empty-state p {
        margin: 0;
        font-size: 12px;
    }

    .order-management-panel {
        overflow: hidden;
    }

    .premium-form-group {
        display: grid;
        gap: 7px;
        margin-bottom: 15px;
    }

    .premium-form-group label {
        color: #4b5563;
        font-size: 11px;
        font-weight: 800;
    }

    .premium-input,
    .premium-select,
    .premium-textarea {
        display: block;
        width: 100%;
        min-height: 43px;
        padding: 10px 12px;
        color: var(--order-text);
        background: #fff;
        border: 1px solid #dfe3ea;
        border-radius: 10px;
        outline: 0;
        font: inherit;
        font-size: 12px;
        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .premium-textarea {
        min-height: 92px;
        resize: vertical;
    }

    .premium-input:focus,
    .premium-select:focus,
    .premium-textarea:focus {
        border-color: #a5b4fc;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08);
    }

    .form-divider {
        height: 1px;
        margin: 20px 0;
        background: var(--order-border);
    }

    .input-with-action {
        position: relative;
    }

    .input-with-action .premium-input {
        padding-right: 65px;
    }

    .input-with-action button {
        position: absolute;
        top: 50%;
        right: 6px;
        padding: 7px 9px;
        color: var(--order-primary);
        background: #eef2ff;
        border: 0;
        border-radius: 7px;
        font-size: 10px;
        font-weight: 850;
        transform: translateY(-50%);
        cursor: pointer;
    }

    .premium-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin: 18px 0;
        color: #4b5563;
        font-size: 11px;
        font-weight: 650;
        line-height: 1.45;
        cursor: pointer;
    }

    .premium-checkbox input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .custom-checkbox {
        position: relative;
        flex: 0 0 18px;
        width: 18px;
        height: 18px;
        background: #fff;
        border: 1px solid #cfd4dd;
        border-radius: 5px;
    }

    .premium-checkbox input:checked + .custom-checkbox {
        background: var(--order-primary);
        border-color: var(--order-primary);
    }

    .premium-checkbox input:checked + .custom-checkbox::after {
        position: absolute;
        top: 2px;
        left: 5px;
        width: 5px;
        height: 9px;
        border-right: 2px solid #fff;
        border-bottom: 2px solid #fff;
        content: '';
        transform: rotate(45deg);
    }

    .full-width-button {
        width: 100%;
    }

    .management-help-text {
        display: block;
        margin-top: 10px;
        color: var(--order-muted);
        font-size: 9px;
        text-align: center;
    }

    .sidebar-summary-panel {
        padding: 19px;
    }

    .tracking-card {
        padding: 14px;
        background: #f8f9fc;
        border: 1px solid var(--order-border);
        border-radius: 11px;
    }

    .tracking-card > span {
        display: block;
        color: var(--order-muted);
        font-size: 10px;
    }

    .tracking-card > strong {
        display: block;
        margin: 6px 0 12px;
        overflow-wrap: anywhere;
        font-size: 13px;
    }

    .tracking-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding-top: 10px;
        border-top: 1px solid var(--order-border);
    }

    .tracking-card-footer span {
        color: var(--order-muted);
        font-size: 10px;
    }

    .tracking-card-footer button {
        padding: 5px 8px;
        color: var(--order-primary);
        background: #eef2ff;
        border: 0;
        border-radius: 6px;
        font-size: 9px;
        font-weight: 850;
        cursor: pointer;
    }

    .sidebar-empty-message {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 13px;
        color: var(--order-muted);
        background: #fafbfc;
        border: 1px dashed #d9dde5;
        border-radius: 11px;
    }

    .sidebar-empty-message span {
        color: var(--order-primary);
        font-size: 18px;
    }

    .sidebar-empty-message p {
        margin: 0;
        font-size: 11px;
        line-height: 1.5;
    }

    .sidebar-payment-summary {
        display: grid;
        gap: 10px;
    }

    .sidebar-payment-summary > div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        color: var(--order-muted);
        font-size: 11px;
    }

    .sidebar-payment-summary strong {
        color: var(--order-text);
    }

    .sidebar-balance-row {
        padding-top: 11px;
        border-top: 1px solid var(--order-border);
    }

    .sidebar-balance-row strong {
        color: var(--order-danger);
        font-size: 14px;
    }

    .danger-zone-panel {
        border-color: #fecaca;
    }

    .danger-zone-panel h2 {
        margin: 5px 0 8px;
        font-size: 17px;
    }

    .danger-zone-panel p {
        margin: 0 0 17px;
        color: var(--order-muted);
        font-size: 11px;
        line-height: 1.55;
    }

    .danger-eyebrow {
        color: var(--order-danger);
    }

    .premium-modal {
        position: fixed;
        inset: 0;
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .premium-modal.is-open {
        display: flex;
    }

    .premium-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.62);
        backdrop-filter: blur(3px);
    }

    .premium-modal-dialog {
        position: relative;
        z-index: 1;
        width: min(100%, 440px);
        padding: 27px;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 30px 80px rgba(15, 23, 42, 0.28);
        text-align: center;
    }

    .modal-close-button {
        position: absolute;
        top: 12px;
        right: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 31px;
        height: 31px;
        color: #687182;
        background: #f4f5f7;
        border: 0;
        border-radius: 8px;
        font-size: 20px;
        cursor: pointer;
    }

    .modal-danger-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 53px;
        height: 53px;
        margin: 0 auto 15px;
        color: #fff;
        background: var(--order-danger);
        border-radius: 50%;
        font-size: 22px;
        font-weight: 900;
    }

    .premium-modal-dialog h2 {
        margin: 0 0 9px;
        font-size: 21px;
    }

    .premium-modal-dialog p {
        margin: 0;
        color: var(--order-muted);
        font-size: 12px;
        line-height: 1.65;
    }

    .modal-actions {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 23px;
    }

    @media (max-width: 1180px) {
        .order-layout {
            grid-template-columns: minmax(0, 1fr) 320px;
        }

        .order-hero-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .hero-stat:nth-child(2) {
            border-right: 0;
        }

        .hero-stat:nth-child(-n + 2) {
            border-bottom: 1px solid var(--order-border);
        }

        .premium-product-content {
            align-items: flex-start;
            flex-direction: column;
        }

        .product-price-information {
            width: 100%;
            justify-content: flex-end;
        }
    }

    @media (max-width: 920px) {
        .premium-order-page {
            padding: 18px;
        }

        .order-layout {
            grid-template-columns: 1fr;
        }

        .order-sidebar {
            position: static;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .order-management-panel,
        .danger-zone-panel {
            grid-column: 1 / -1;
        }

        .order-hero-top {
            flex-direction: column;
        }

        .order-header-actions {
            width: 100%;
        }

        .order-info-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 680px) {
        .premium-order-page {
            padding: 12px;
        }

        .order-hero,
        .premium-panel {
            padding: 17px;
            border-radius: 15px;
        }

        .order-title-line {
            align-items: flex-start;
        }

        .order-title-line h1 {
            width: 100%;
        }

        .order-header-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .order-header-actions .premium-button {
            width: 100%;
        }

        .order-hero-stats {
            grid-template-columns: 1fr;
        }

        .hero-stat,
        .hero-stat:nth-child(2) {
            border-right: 0;
            border-bottom: 1px solid var(--order-border);
        }

        .hero-stat:last-child {
            border-bottom: 0;
        }

        .fulfilment-step {
            flex-basis: 85px;
        }

        .premium-product-card {
            align-items: flex-start;
        }

        .premium-product-image {
            flex-basis: 68px;
            width: 68px;
            height: 78px;
        }

        .product-price-information {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 9px;
        }

        .product-price-information .multiplication-symbol {
            display: none;
        }

        .product-price-information .product-line-total {
            min-width: 0;
            padding-left: 0;
            border-left: 0;
        }

        .order-totals-area {
            grid-template-columns: 1fr;
        }

        .totals-spacer {
            display: none;
        }

        .order-sidebar {
            grid-template-columns: 1fr;
        }

        .order-management-panel,
        .danger-zone-panel {
            grid-column: auto;
        }

        .note-compose {
            flex-direction: column;
        }

        .note-input-wrapper {
            width: 100%;
        }

        .note-form-footer {
            align-items: flex-start;
            flex-direction: column;
        }

        .note-form-footer .premium-button {
            width: 100%;
        }

        .activity-title-row {
            flex-direction: column;
            gap: 4px;
        }
    }

    @media print {
        body {
            background: #fff !important;
        }

        .premium-order-page {
            max-width: none;
            padding: 0;
        }

        .order-header-actions,
        .order-sidebar,
        .note-form,
        .delete-note-button,
        .danger-zone-panel,
        .order-back-link {
            display: none !important;
        }

        .order-layout {
            display: block;
        }

        .order-hero,
        .premium-panel {
            break-inside: avoid;
            box-shadow: none;
        }

        .order-main-column {
            gap: 12px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const moreButton =
            document.getElementById('orderMoreButton');

        const actionsMenu =
            document.getElementById('orderActionsMenu');

        if (moreButton && actionsMenu) {
            moreButton.addEventListener('click', function (event) {
                event.stopPropagation();

                actionsMenu.classList.toggle('is-open');
            });

            document.addEventListener('click', function () {
                actionsMenu.classList.remove('is-open');
            });

            actionsMenu.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Tracking copy buttons
        |--------------------------------------------------------------------------
        */

        const trackingInput =
            document.getElementById('tracking_number');

        const copyTrackingButton =
            document.getElementById('copyTrackingButton');

        const copyTrackingCardButton =
            document.getElementById('copyTrackingCardButton');

        const trackingDisplay =
            document.getElementById('trackingDisplay');

        async function copyTrackingNumber(button, value) {
            if (!value) {
                return;
            }

            const originalText = button.textContent;

            try {
                await navigator.clipboard.writeText(value);

                button.textContent = 'Copied';

                setTimeout(function () {
                    button.textContent = originalText;
                }, 1400);
            } catch (error) {
                const temporaryInput =
                    document.createElement('textarea');

                temporaryInput.value = value;
                temporaryInput.style.position = 'fixed';
                temporaryInput.style.opacity = '0';

                document.body.appendChild(temporaryInput);

                temporaryInput.select();
                document.execCommand('copy');
                temporaryInput.remove();

                button.textContent = 'Copied';

                setTimeout(function () {
                    button.textContent = originalText;
                }, 1400);
            }
        }

        if (copyTrackingButton && trackingInput) {
            copyTrackingButton.addEventListener(
                'click',
                function () {
                    copyTrackingNumber(
                        copyTrackingButton,
                        trackingInput.value.trim()
                    );
                }
            );
        }

        if (
            copyTrackingCardButton
            && trackingDisplay
        ) {
            copyTrackingCardButton.addEventListener(
                'click',
                function () {
                    copyTrackingNumber(
                        copyTrackingCardButton,
                        trackingDisplay.textContent.trim()
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Delete modal
        |--------------------------------------------------------------------------
        */

        const deleteModal =
            document.getElementById('deleteOrderModal');

        const openDeleteButtons =
            document.querySelectorAll(
                '[data-open-delete-modal]'
            );

        const closeDeleteButtons =
            document.querySelectorAll(
                '[data-close-delete-modal]'
            );

        function openDeleteModal() {
            if (!deleteModal) {
                return;
            }

            deleteModal.classList.add('is-open');
            deleteModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            if (!deleteModal) {
                return;
            }

            deleteModal.classList.remove('is-open');
            deleteModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        openDeleteButtons.forEach(function (button) {
            button.addEventListener(
                'click',
                openDeleteModal
            );
        });

        closeDeleteButtons.forEach(function (button) {
            button.addEventListener(
                'click',
                closeDeleteModal
            );
        });

        document.addEventListener(
            'keydown',
            function (event) {
                if (event.key === 'Escape') {
                    closeDeleteModal();
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate submissions
        |--------------------------------------------------------------------------
        */

        const managementForm =
            document.getElementById('orderManagementForm');

        const saveOrderButton =
            document.getElementById('saveOrderButton');

        if (managementForm && saveOrderButton) {
            managementForm.addEventListener(
                'submit',
                function () {
                    saveOrderButton.disabled = true;
                    saveOrderButton.textContent =
                        'Saving changes...';
                }
            );
        }

        const noteForm =
            document.getElementById('orderNoteForm');

        const addNoteButton =
            document.getElementById('addNoteButton');

        if (noteForm && addNoteButton) {
            noteForm.addEventListener(
                'submit',
                function () {
                    addNoteButton.disabled = true;
                    addNoteButton.textContent =
                        'Adding note...';
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Note deletion confirmation
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll('.delete-note-form')
            .forEach(function (form) {
                form.addEventListener(
                    'submit',
                    function (event) {
                        const confirmed = window.confirm(
                            'Are you sure you want to delete this note?'
                        );

                        if (!confirmed) {
                            event.preventDefault();
                        }
                    }
                );
            });
    });
</script>
@endsection