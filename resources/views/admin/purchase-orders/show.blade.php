@extends('admin.layouts.app')

@section('title', $purchaseOrder->reference)

@section('content')

<div class="purchase-order-show-page">

    {{-- Success Message --}}
    @if (session('success'))

    <div class="purchase-order-alert success">

        <i class="fa-solid fa-circle-check"></i>

        <span>
            {{ session('success') }}
        </span>

    </div>

    @endif

    {{-- Error Message --}}
    @if (session('error'))

    <div class="purchase-order-alert error">

        <i class="fa-solid fa-circle-exclamation"></i>

        <span>
            {{ session('error') }}
        </span>

    </div>

    @endif

    {{-- Page Header --}}
    <section class="purchase-order-show-header">

        <div class="purchase-order-heading">

            <div class="purchase-order-heading-icon">

                <i class="fa-solid fa-file-invoice-dollar"></i>

            </div>

            <div>

                <span class="purchase-order-eyebrow">
                    Purchase order
                </span>

                <div class="purchase-order-title-row">

                    <h1>
                        {{ $purchaseOrder->reference }}
                    </h1>

                    <span
                        class="purchase-order-status {{
                            $purchaseOrder->status
                        }}">

                        {{ $purchaseOrder->status_label }}

                    </span>

                </div>

                <p>
                    Created
                    {{ $purchaseOrder->created_at->format('d M Y \a\t H:i') }}

                    @if ($purchaseOrder->creator)

                    by

                    <strong>
                        {{
                                $purchaseOrder->creator->name
                                ?? $purchaseOrder->creator->email
                            }}
                    </strong>

                    @endif
                </p>

            </div>

        </div>

        <div class="purchase-order-header-actions">

            <a
                href="{{ route(
            'admin.purchase-orders.index'
        ) }}"
                class="purchase-order-button secondary">

                <i class="fa-solid fa-arrow-left"></i>

                All Purchase Orders

            </a>

            <a
                href="{{ route(
            'admin.reorder-dashboard.index'
        ) }}"
                class="purchase-order-button reorder">

                <i class="fa-solid fa-cart-flatbed"></i>

                Reorder Dashboard

            </a>

            @include('admin.purchase-orders.partials.draft-edit-button')
            @include('admin.purchase-orders.partials.receiving-returns-button')

            @if ($purchaseOrder->isDraft())

            <form
                method="POST"
                action="{{ route(
                'admin.purchase-orders.mark-ordered',
                $purchaseOrder
            ) }}"
                class="purchase-order-action-form"
                data-confirm-message="Mark this purchase order as ordered?">

                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="purchase-order-button ordered-action">

                    <i class="fa-solid fa-paper-plane"></i>

                    Mark as Ordered

                </button>

            </form>

            @endif

            @if (
            !$purchaseOrder->isCancelled()
            && !$purchaseOrder->isReceived()
            && !$purchaseOrder->isPartiallyReceived()
            )

            <form
                method="POST"
                action="{{ route(
                'admin.purchase-orders.cancel',
                $purchaseOrder
            ) }}"
                class="purchase-order-action-form"
                data-confirm-message="Cancel this purchase order? This action will prevent inventory from being received against it.">

                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="purchase-order-button cancel-action">

                    <i class="fa-solid fa-ban"></i>

                    Cancel Order

                </button>

            </form>

            @endif

            <a
                href="{{ route(
        'admin.purchase-orders.pdf',
        $purchaseOrder
    ) }}"
                class="purchase-order-button pdf-action">

                <i class="fa-solid fa-file-pdf"></i>

                Download PDF

            </a>

            <a
                href="{{ route(
        'admin.purchase-orders.excel',
        $purchaseOrder
    ) }}"
                class="purchase-order-button excel-action">

                <i class="fa-solid fa-file-excel"></i>

                Download Excel

            </a>

            <button
                type="button"
                class="purchase-order-button primary"
                onclick="window.print()">

                <i class="fa-solid fa-print"></i>

                Print Purchase Order

            </button>

        </div>

    </section>

    {{-- Summary Cards --}}
    <section class="purchase-order-summary-grid">

        <article class="purchase-order-summary-card">

            <span class="summary-card-icon ordered">

                <i class="fa-solid fa-boxes-stacked"></i>

            </span>

            <div>

                <span class="summary-card-label">
                    Ordered Quantity
                </span>

                <strong>
                    {{ number_format($totalOrderedQuantity) }}
                </strong>

                <small>
                    Total units ordered
                </small>

            </div>

        </article>

        <article class="purchase-order-summary-card">

            <span class="summary-card-icon received">

                <i class="fa-solid fa-box-circle-check"></i>

            </span>

            <div>

                <span class="summary-card-label">
                    Received Quantity
                </span>

                <strong>
                    {{ number_format($totalReceivedQuantity) }}
                </strong>

                <small>
                    Units received so far
                </small>

            </div>

        </article>

        <article class="purchase-order-summary-card">

            <span class="summary-card-icon remaining">

                <i class="fa-solid fa-hourglass-half"></i>

            </span>

            <div>

                <span class="summary-card-label">
                    Remaining Quantity
                </span>

                <strong>
                    {{ number_format($remainingQuantity) }}
                </strong>

                <small>
                    Units still expected
                </small>

            </div>

        </article>

        <article class="purchase-order-summary-card">

            <span class="summary-card-icon total">

                <i class="fa-solid fa-sterling-sign"></i>

            </span>

            <div>

                <span class="summary-card-label">
                    Purchase Order Total
                </span>

                <strong>
                    £{{ number_format(
                        $purchaseOrder->total_amount,
                        2
                    ) }}
                </strong>

                <small>
                    Including tax and shipping
                </small>

            </div>

        </article>

    </section>

    {{-- Receiving Progress --}}
    <section class="purchase-order-progress-panel">

        <div class="purchase-order-progress-header">

            <div>

                <span class="purchase-order-eyebrow">
                    Receiving progress
                </span>

                <h2>
                    Inventory Receiving Status
                </h2>

                <p>
                    Track how much of this purchase order has been received.
                </p>

            </div>

            <strong>
                {{ $receivingPercentage }}%
            </strong>

        </div>

        <div class="purchase-order-progress-track">

            <span
                style="width: {{ $receivingPercentage }}%;">
            </span>

        </div>

        <div class="purchase-order-progress-details">

            <span>
                {{ number_format($totalReceivedQuantity) }}
                received
            </span>

            <span>
                {{ number_format($remainingQuantity) }}
                remaining
            </span>

            <span>
                {{ number_format($totalOrderedQuantity) }}
                ordered
            </span>

        </div>

    </section>

    <section class="purchase-order-information-grid">

        {{-- Supplier Information --}}
        <article class="purchase-order-information-card">

            <div class="information-card-header">

                <span class="information-card-icon supplier">

                    <i class="fa-solid fa-building"></i>

                </span>

                <div>

                    <span class="purchase-order-eyebrow">
                        Supplier
                    </span>

                    <h2>
                        Supplier Information
                    </h2>

                </div>

            </div>

            <div class="information-list">

                <div>

                    <span>
                        Supplier Name
                    </span>

                    <strong>
                        {{
                            $purchaseOrder->supplier_name
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

                <div>

                    <span>
                        Email Address
                    </span>

                    @if ($purchaseOrder->supplier_email)

                    <a
                        href="mailto:{{
                                $purchaseOrder->supplier_email
                            }}">

                        {{ $purchaseOrder->supplier_email }}

                    </a>

                    @else

                    <strong>
                        Not assigned
                    </strong>

                    @endif

                </div>

                <div>

                    <span>
                        Phone Number
                    </span>

                    <strong>
                        {{
                            $purchaseOrder->supplier_phone
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

                <div class="information-list-wide">

                    <span>
                        Supplier Address
                    </span>

                    <strong class="address-value">
                        {{
                            $purchaseOrder->supplier_address
                            ?: 'Not assigned'
                        }}
                    </strong>

                </div>

            </div>

        </article>

        {{-- Order Information --}}
        <article class="purchase-order-information-card">

            <div class="information-card-header">

                <span class="information-card-icon calendar">

                    <i class="fa-solid fa-calendar-days"></i>

                </span>

                <div>

                    <span class="purchase-order-eyebrow">
                        Order details
                    </span>

                    <h2>
                        Purchase Information
                    </h2>

                </div>

            </div>

            <div class="information-list">

                <div>

                    <span>
                        Reference
                    </span>

                    <strong>
                        {{ $purchaseOrder->reference }}
                    </strong>

                </div>

                <div>

                    <span>
                        Status
                    </span>

                    <strong>
                        {{ $purchaseOrder->status_label }}
                    </strong>

                </div>

                <div>

                    <span>
                        Order Date
                    </span>

                    <strong>
                        {{
                            optional(
                                $purchaseOrder->order_date
                            )->format('d M Y')
                            ?: 'Not set'
                        }}
                    </strong>

                </div>

                <div>

                    <span>
                        Expected Delivery
                    </span>

                    <strong class="{{
                        $purchaseOrder->expected_date
                        && $purchaseOrder->expected_date->isPast()
                        && !$purchaseOrder->isReceived()
                        && !$purchaseOrder->isCancelled()
                            ? 'expected-overdue'
                            : ''
                    }}">

                        {{
                            optional(
                                $purchaseOrder->expected_date
                            )->format('d M Y')
                            ?: 'Not set'
                        }}

                    </strong>

                </div>

                <div>

                    <span>
                        Currency
                    </span>

                    <strong>
                        {{ $purchaseOrder->currency }}
                    </strong>

                </div>

                <div>

                    <span>
                        Total Items
                    </span>

                    <strong>
                        {{ number_format(
                            $purchaseOrder->items->count()
                        ) }}
                    </strong>

                </div>

                <div>

                    <span>
                        Ordered At
                    </span>

                    <strong>
                        {{
            optional(
                $purchaseOrder->ordered_at
            )->format('d M Y H:i')
            ?: 'Not ordered yet'
        }}
                    </strong>

                </div>

                <div>

                    <span>
                        Received At
                    </span>

                    <strong>
                        {{
            optional(
                $purchaseOrder->received_at
            )->format('d M Y H:i')
            ?: 'Not received yet'
        }}
                    </strong>

                </div>

                @if ($purchaseOrder->isCancelled())

                <div>

                    <span>
                        Cancelled At
                    </span>

                    <strong class="cancelled-value">
                        {{
                optional(
                    $purchaseOrder->cancelled_at
                )->format('d M Y H:i')
                ?: 'Cancelled'
            }}
                    </strong>

                </div>

                @endif

            </div>

        </article>

    </section>

    @if (
    !$purchaseOrder->isDraft()
    && !$purchaseOrder->isCancelled()
    && !$purchaseOrder->isReceived()
    )

    <section class="receive-inventory-panel">

        <div class="receive-inventory-header">

            <div class="receive-inventory-heading">

                <span class="receive-inventory-icon">

                    <i class="fa-solid fa-boxes-packing"></i>

                </span>

                <div>

                    <span class="purchase-order-eyebrow">
                        Stock receiving
                    </span>

                    <h2>
                        Receive Inventory
                    </h2>

                    <p>
                        Enter the quantity received for each outstanding item.
                        Product or variant stock will increase automatically.
                    </p>

                </div>

            </div>

            <button
                type="button"
                class="receive-all-button"
                id="receiveAllRemainingButton">

                <i class="fa-solid fa-check-double"></i>

                Receive All Remaining

            </button>

        </div>

        <form
            method="POST"
            action="{{ route(
                'admin.purchase-orders.receive',
                $purchaseOrder
            ) }}"
            class="receive-inventory-form"
            id="receiveInventoryForm">

            @csrf

            @if ($errors->has('items'))

            <div class="receive-validation-error">

                <i class="fa-solid fa-circle-exclamation"></i>

                <span>
                    {{ $errors->first('items') }}
                </span>

            </div>

            @endif

            <div class="receive-table-wrapper">

                <table class="receive-table">

                    <thead>

                        <tr>
                            <th>Product / Variant</th>
                            <th>SKU</th>
                            <th class="number-column">Ordered</th>
                            <th class="number-column">Previously Received</th>
                            <th class="number-column">Remaining</th>
                            <th class="number-column">Receive Now</th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach (
                        $purchaseOrder->items
                        as $index => $item
                        )

                        @php
                        $itemRemaining = max(
                        0,
                        (int) $item->quantity_ordered
                        - (int) $item->quantity_received
                        );
                        @endphp

                        @if ($itemRemaining > 0)

                        <tr>

                            <td>

                                <div class="receive-item-information">

                                    <span class="receive-item-icon">

                                        @if ($item->product_variant_id)

                                        <i class="fa-solid fa-layer-group"></i>

                                        @else

                                        <i class="fa-solid fa-box"></i>

                                        @endif

                                    </span>

                                    <div>

                                        <strong>
                                            {{ $item->item_name }}
                                        </strong>

                                        @if ($item->variant_name)

                                        <span>
                                            {{ $item->variant_name }}
                                        </span>

                                        @endif

                                    </div>

                                </div>

                                <input
                                    type="hidden"
                                    name="items[{{ $index }}][purchase_order_item_id]"
                                    value="{{ $item->id }}">

                            </td>

                            <td>
                                {{ $item->sku ?: 'Not assigned' }}
                            </td>

                            <td class="number-column">
                                {{ number_format(
                                            $item->quantity_ordered
                                        ) }}
                            </td>

                            <td class="number-column">

                                <strong class="already-received-value">

                                    {{ number_format(
                                                $item->quantity_received
                                            ) }}

                                </strong>

                            </td>

                            <td class="number-column">

                                <strong class="receive-remaining-value">

                                    {{ number_format(
                                                $itemRemaining
                                            ) }}

                                </strong>

                            </td>

                            <td class="number-column">

                                <input
                                    type="number"
                                    class="receive-quantity-input"
                                    name="items[{{ $index }}][quantity_received]"
                                    value="{{ old(
                                                'items.' . $index . '.quantity_received',
                                                0
                                            ) }}"
                                    min="0"
                                    max="{{ $itemRemaining }}"
                                    step="1"
                                    data-remaining="{{ $itemRemaining }}"
                                    aria-label="Receive quantity for {{ $item->item_name }}">

                                @error(
                                'items.' . $index . '.quantity_received'
                                )

                                <small class="receive-field-error">
                                    {{ $message }}
                                </small>

                                @enderror

                            </td>

                        </tr>

                        @endif

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="receive-inventory-footer">

                <div class="receive-notes-field">

                    <label for="receiving_notes">
                        Receiving Notes
                    </label>

                    <textarea
                        id="receiving_notes"
                        name="receiving_notes"
                        rows="3"
                        placeholder="Optional delivery, condition or warehouse notes">{{ old('receiving_notes') }}</textarea>

                    @error('receiving_notes')

                    <small class="receive-field-error">
                        {{ $message }}
                    </small>

                    @enderror

                </div>

                <div class="receive-submit-area">

                    <span>
                        Units to receive
                    </span>

                    <strong id="receiveQuantityTotal">
                        0
                    </strong>

                    <button
                        type="submit"
                        class="receive-inventory-button"
                        id="receiveInventoryButton"
                        disabled>

                        <i class="fa-solid fa-box-circle-check"></i>

                        Receive Inventory

                    </button>

                </div>

            </div>

        </form>

    </section>

    @endif

    {{-- Items Table --}}
    <section class="purchase-order-items-panel">

        <div class="purchase-order-items-header">

            <div>

                <span class="purchase-order-eyebrow">
                    Ordered inventory
                </span>

                <h2>
                    Purchase Order Items
                </h2>

                <p>
                    Products and variants included in this purchase order.
                </p>

            </div>

            <span class="purchase-order-item-count">

                <strong>
                    {{ number_format(
                        $purchaseOrder->items->count()
                    ) }}
                </strong>

                items

            </span>

        </div>

        <div class="purchase-order-table-wrapper">

            <table class="purchase-order-table">

                <thead>

                    <tr>

                        <th>Product / Variant</th>
                        <th>SKU</th>
                        <th class="number-column">Stock Before</th>
                        <th class="number-column">Reorder Point</th>
                        <th class="number-column">Ordered</th>
                        <th class="number-column">Received</th>
                        <th class="number-column">Remaining</th>
                        <th class="number-column">Unit Cost</th>
                        <th class="number-column">Line Total</th>
                        <th>Progress</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse (
                    $purchaseOrder->items
                    as $item
                    )

                    @php
                    $itemRemaining = max(
                    0,
                    (int) $item->quantity_ordered
                    - (int) $item->quantity_received
                    );

                    $itemPercentage =
                    $item->quantity_ordered > 0
                    ? min(
                    100,
                    (int) round(
                    (
                    $item->quantity_received
                    / $item->quantity_ordered
                    ) * 100
                    )
                    )
                    : 0;
                    @endphp

                    <tr>

                        <td>

                            <div class="purchase-item-information">

                                <span class="purchase-item-icon">

                                    @if ($item->product_variant_id)

                                    <i class="fa-solid fa-layer-group"></i>

                                    @else

                                    <i class="fa-solid fa-box"></i>

                                    @endif

                                </span>

                                <div>

                                    <strong>
                                        {{ $item->item_name }}
                                    </strong>

                                    @if ($item->variant_name)

                                    <span>
                                        {{ $item->variant_name }}
                                    </span>

                                    @endif

                                    @if ($item->notes)

                                    <small>
                                        {{ $item->notes }}
                                    </small>

                                    @endif

                                </div>

                            </div>

                        </td>

                        <td>

                            {{
                                    $item->sku
                                    ?: 'Not assigned'
                                }}

                        </td>

                        <td class="number-column">

                            {{ number_format(
                                    $item->stock_before
                                ) }}

                        </td>

                        <td class="number-column">

                            {{
                                    $item->reorder_point !== null
                                        ? number_format(
                                            $item->reorder_point
                                        )
                                        : 'Not set'
                                }}

                        </td>

                        <td class="number-column">

                            <strong>
                                {{ number_format(
                                        $item->quantity_ordered
                                    ) }}
                            </strong>

                        </td>

                        <td class="number-column">

                            <strong class="received-value">
                                {{ number_format(
                                        $item->quantity_received
                                    ) }}
                            </strong>

                        </td>

                        <td class="number-column">

                            <strong class="{{
                                    $itemRemaining > 0
                                        ? 'remaining-value'
                                        : 'complete-value'
                                }}">

                                {{ number_format(
                                        $itemRemaining
                                    ) }}

                            </strong>

                        </td>

                        <td class="number-column">

                            £{{ number_format(
                                    $item->unit_cost,
                                    2
                                ) }}

                        </td>

                        <td class="number-column">

                            <strong class="line-total-value">

                                £{{ number_format(
                                        $item->line_total,
                                        2
                                    ) }}

                            </strong>

                        </td>

                        <td>

                            <div class="item-progress">

                                <div class="item-progress-top">

                                    <span>
                                        {{ $itemPercentage }}%
                                    </span>

                                    <small>
                                        {{
                                                $itemRemaining === 0
                                                    ? 'Complete'
                                                    : 'Pending'
                                            }}
                                    </small>

                                </div>

                                <div class="item-progress-track">

                                    <span
                                        style="width:{{
                                                $itemPercentage
                                            }}%;">
                                    </span>

                                </div>

                            </div>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td
                            colspan="10"
                            class="purchase-order-empty">

                            No purchase-order items were found.

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </section>

    <section class="purchase-order-bottom-grid">

        {{-- Notes --}}
        <article class="purchase-order-notes-panel">

            <div class="purchase-order-notes-section">

                <span class="purchase-order-eyebrow">
                    Supplier notes
                </span>

                <h2>
                    Purchase Order Notes
                </h2>

                <p>
                    {{
                        $purchaseOrder->notes
                        ?: 'No supplier notes were added.'
                    }}
                </p>

            </div>

            <div class="purchase-order-notes-section internal">

                <span class="purchase-order-eyebrow">
                    Internal notes
                </span>

                <h2>
                    Administrator Notes
                </h2>

                <p>
                    {{
                        $purchaseOrder->internal_notes
                        ?: 'No internal notes were added.'
                    }}
                </p>

            </div>

        </article>

        {{-- Financial Summary --}}
        <article class="purchase-order-totals-panel">

            <span class="purchase-order-eyebrow">
                Financial summary
            </span>

            <h2>
                Purchase Order Totals
            </h2>

            <div class="purchase-order-total-row">

                <span>
                    Subtotal
                </span>

                <strong>
                    £{{ number_format(
                        $purchaseOrder->subtotal,
                        2
                    ) }}
                </strong>

            </div>

            <div class="purchase-order-total-row">

                <span>
                    Tax
                </span>

                <strong>
                    £{{ number_format(
                        $purchaseOrder->tax_amount,
                        2
                    ) }}
                </strong>

            </div>

            <div class="purchase-order-total-row">

                <span>
                    Shipping
                </span>

                <strong>
                    £{{ number_format(
                        $purchaseOrder->shipping_amount,
                        2
                    ) }}
                </strong>

            </div>

            <div class="purchase-order-total-row discount">

                <span>
                    Discount
                </span>

                <strong>
                    -£{{ number_format(
                        $purchaseOrder->discount_amount,
                        2
                    ) }}
                </strong>

            </div>

            <div class="purchase-order-total-row grand-total">

                <span>
                    Grand Total
                </span>

                <strong>
                    £{{ number_format(
                        $purchaseOrder->total_amount,
                        2
                    ) }}
                </strong>

            </div>

        </article>

    </section>

</div>

@endsection

@push('page-styles')

<style>
    .purchase-order-show-page {
        --show-text: #111827;
        --show-muted: #64748b;
        --show-border: #e5e7eb;
        --show-soft-border: #eef2f7;
        --show-indigo: #4f46e5;
        --show-indigo-soft: #eef2ff;
        --show-green: #15803d;
        --show-green-soft: #ecfdf3;
        --show-orange: #c2410c;
        --show-orange-soft: #fff7ed;
        --show-red: #b91c1c;
        --show-red-soft: #fef2f2;
        --show-blue: #0369a1;
        --show-blue-soft: #f0f9ff;

        display: flex;
        flex-direction: column;
        gap: 22px;
        min-width: 0;
        color: var(--show-text);
    }

    .purchase-order-show-page *,
    .purchase-order-show-page *::before,
    .purchase-order-show-page *::after {
        box-sizing: border-box;
    }

    .purchase-order-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        border-radius: 11px;
        font-size: 12px;
        font-weight: 700;
    }

    .purchase-order-alert.success {
        border: 1px solid #bbf7d0;
        background: #f0fdf4;
        color: #15803d;
    }

    .purchase-order-alert.error {
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #b91c1c;
    }

    .purchase-order-show-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 27px 29px;
        border: 1px solid var(--show-border);
        border-radius: 19px;
        background:
            radial-gradient(circle at top right,
                rgba(79, 70, 229, 0.14),
                transparent 38%),
            linear-gradient(135deg,
                #ffffff,
                #f8f9ff);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
    }

    .purchase-order-heading {
        display: flex;
        align-items: center;
        gap: 17px;
        min-width: 0;
    }

    .purchase-order-heading-icon {
        width: 58px;
        height: 58px;
        flex: 0 0 58px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: var(--show-indigo-soft);
        color: var(--show-indigo);
        font-size: 22px;
    }

    .purchase-order-eyebrow {
        display: block;
        margin-bottom: 6px;
        color: var(--show-indigo);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .purchase-order-title-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 11px;
        margin-bottom: 7px;
    }

    .purchase-order-title-row h1 {
        margin: 0;
        font-size: 28px;
    }

    .purchase-order-heading p {
        margin: 0;
        color: var(--show-muted);
        font-size: 12px;
    }

    .purchase-order-status {
        display: inline-flex;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
    }

    .purchase-order-status.draft {
        background: #f1f5f9;
        color: #475569;
    }

    .purchase-order-status.ordered {
        background: var(--show-blue-soft);
        color: var(--show-blue);
    }

    .purchase-order-status.partially_received {
        background: var(--show-orange-soft);
        color: var(--show-orange);
    }

    .purchase-order-status.received {
        background: var(--show-green-soft);
        color: var(--show-green);
    }

    .purchase-order-status.cancelled {
        background: var(--show-red-soft);
        color: var(--show-red);
    }

    .purchase-order-header-actions {
        display: flex;
        align-items: center;
        gap: 9px;
        flex-shrink: 0;
    }

    .purchase-order-button {
        min-height: 43px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 14px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-family: inherit;
        font-size: 11px;
        font-weight: 750;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .purchase-order-button.secondary {
        border-color: #d7dce5;
        background: #ffffff;
        color: #475569;
    }

    .purchase-order-button.reorder {
        border-color: #c7d2fe;
        background: var(--show-indigo-soft);
        color: var(--show-indigo);
    }

    .purchase-order-button.primary {
        background: #111827;
        color: #ffffff;
    }

    .purchase-order-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .purchase-order-summary-card {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 19px;
        border: 1px solid var(--show-border);
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 7px 20px rgba(15, 23, 42, 0.04);
    }

    .summary-card-icon {
        width: 45px;
        height: 45px;
        flex: 0 0 45px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    .summary-card-icon.ordered {
        background: var(--show-indigo-soft);
        color: var(--show-indigo);
    }

    .summary-card-icon.received {
        background: var(--show-green-soft);
        color: var(--show-green);
    }

    .summary-card-icon.remaining {
        background: var(--show-orange-soft);
        color: var(--show-orange);
    }

    .summary-card-icon.total {
        background: var(--show-blue-soft);
        color: var(--show-blue);
    }

    .summary-card-label,
    .purchase-order-summary-card small {
        display: block;
        color: var(--show-muted);
    }

    .summary-card-label {
        margin-bottom: 3px;
        font-size: 10px;
        font-weight: 700;
    }

    .purchase-order-summary-card strong {
        display: block;
        margin-bottom: 3px;
        font-size: 20px;
    }

    .purchase-order-summary-card small {
        font-size: 9px;
    }

    .purchase-order-progress-panel,
    .purchase-order-information-card,
    .purchase-order-items-panel,
    .purchase-order-notes-panel,
    .purchase-order-totals-panel {
        border: 1px solid var(--show-border);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 7px 22px rgba(15, 23, 42, 0.04);
    }

    .purchase-order-progress-panel {
        padding: 21px 23px;
    }

    .purchase-order-progress-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 15px;
    }

    .purchase-order-progress-header h2,
    .information-card-header h2,
    .purchase-order-items-header h2,
    .purchase-order-notes-panel h2,
    .purchase-order-totals-panel h2 {
        margin: 0 0 5px;
        font-size: 18px;
    }

    .purchase-order-progress-header p,
    .purchase-order-items-header p {
        margin: 0;
        color: var(--show-muted);
        font-size: 10px;
    }

    .purchase-order-progress-header>strong {
        color: var(--show-green);
        font-size: 23px;
    }

    .purchase-order-progress-track {
        height: 11px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .purchase-order-progress-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background:
            linear-gradient(90deg,
                #4f46e5,
                #16a34a);
    }

    .purchase-order-progress-details {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 10px;
        color: var(--show-muted);
        font-size: 9px;
    }

    .purchase-order-information-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .purchase-order-information-card {
        padding: 21px;
    }

    .information-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
    }

    .information-card-icon {
        width: 43px;
        height: 43px;
        flex: 0 0 43px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
    }

    .information-card-icon.supplier {
        background: var(--show-indigo-soft);
        color: var(--show-indigo);
    }

    .information-card-icon.calendar {
        background: var(--show-blue-soft);
        color: var(--show-blue);
    }

    .information-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 13px;
    }

    .information-list>div {
        padding: 12px;
        border-radius: 10px;
        background: #f8fafc;
    }

    .information-list-wide {
        grid-column: 1 / -1;
    }

    .information-list span {
        display: block;
        margin-bottom: 5px;
        color: var(--show-muted);
        font-size: 9px;
        font-weight: 700;
    }

    .information-list strong,
    .information-list a {
        color: var(--show-text);
        font-size: 11px;
        font-weight: 750;
        text-decoration: none;
    }

    .address-value {
        display: block;
        white-space: pre-line;
        line-height: 1.6;
    }

    .expected-overdue {
        color: var(--show-red) !important;
    }

    .purchase-order-items-panel {
        overflow: hidden;
    }

    .purchase-order-items-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 21px 23px;
        border-bottom: 1px solid var(--show-border);
    }

    .purchase-order-item-count {
        padding: 8px 12px;
        border-radius: 9px;
        background: var(--show-indigo-soft);
        color: var(--show-indigo);
        font-size: 9px;
        font-weight: 750;
        text-align: center;
    }

    .purchase-order-item-count strong {
        margin-right: 3px;
        font-size: 16px;
    }

    .purchase-order-table-wrapper {
        overflow-x: auto;
    }

    .purchase-order-table {
        width: 100%;
        min-width: 1280px;
        border-collapse: collapse;
    }

    .purchase-order-table th,
    .purchase-order-table td {
        padding: 14px 15px;
        border-bottom: 1px solid var(--show-soft-border);
        text-align: left;
        vertical-align: middle;
    }

    .purchase-order-table th {
        background: #f8fafc;
        color: var(--show-muted);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .purchase-order-table td {
        color: #374151;
        font-size: 10px;
    }

    .purchase-order-table .number-column {
        text-align: right;
        white-space: nowrap;
    }

    .purchase-item-information {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 245px;
    }

    .purchase-item-icon {
        width: 39px;
        height: 39px;
        flex: 0 0 39px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--show-border);
        border-radius: 10px;
        background: #f8fafc;
        color: #64748b;
    }

    .purchase-item-information strong,
    .purchase-item-information span,
    .purchase-item-information small {
        display: block;
    }

    .purchase-item-information strong {
        margin-bottom: 3px;
        color: var(--show-text);
    }

    .purchase-item-information span,
    .purchase-item-information small {
        color: var(--show-muted);
        font-size: 8px;
    }

    .received-value,
    .complete-value,
    .line-total-value {
        color: var(--show-green);
    }

    .remaining-value {
        color: var(--show-orange);
    }

    .item-progress {
        min-width: 100px;
    }

    .item-progress-top {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 5px;
    }

    .item-progress-top small {
        color: var(--show-muted);
    }

    .item-progress-track {
        height: 5px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .item-progress-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: var(--show-green);
    }

    .purchase-order-empty {
        padding: 45px 20px !important;
        text-align: center !important;
        color: var(--show-muted) !important;
    }

    .purchase-order-bottom-grid {
        display: grid;
        grid-template-columns:
            minmax(0, 1.5fr) minmax(320px, 0.7fr);
        gap: 18px;
    }

    .purchase-order-notes-panel {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        overflow: hidden;
    }

    .purchase-order-notes-section {
        padding: 22px;
    }

    .purchase-order-notes-section+.purchase-order-notes-section {
        border-left: 1px solid var(--show-border);
    }

    .purchase-order-notes-section.internal {
        background: #f8fafc;
    }

    .purchase-order-notes-section p {
        margin: 0;
        color: var(--show-muted);
        font-size: 11px;
        line-height: 1.7;
        white-space: pre-line;
    }

    .purchase-order-totals-panel {
        padding: 22px;
    }

    .purchase-order-total-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 11px 0;
        border-bottom: 1px solid var(--show-soft-border);
        color: var(--show-muted);
        font-size: 11px;
    }

    .purchase-order-total-row strong {
        color: var(--show-text);
    }

    .purchase-order-total-row.discount strong {
        color: var(--show-red);
    }

    .purchase-order-total-row.grand-total {
        margin-top: 5px;
        padding-top: 16px;
        border-bottom: 0;
        color: var(--show-text);
        font-size: 14px;
        font-weight: 800;
    }

    .purchase-order-total-row.grand-total strong {
        color: var(--show-green);
        font-size: 21px;
    }

    .purchase-order-action-form {
        display: inline-flex;
        margin: 0;
    }

    .purchase-order-button.ordered-action {
        border-color: #bae6fd;
        background: var(--show-blue-soft);
        color: var(--show-blue);
    }

    .purchase-order-button.ordered-action:hover {
        border-color: var(--show-blue);
        background: var(--show-blue);
        color: #ffffff;
    }

    .purchase-order-button.cancel-action {
        border-color: #fecaca;
        background: var(--show-red-soft);
        color: var(--show-red);
    }

    .purchase-order-button.cancel-action:hover {
        border-color: var(--show-red);
        background: var(--show-red);
        color: #ffffff;
    }

    .cancelled-value {
        color: var(--show-red) !important;
    }

    /*
|--------------------------------------------------------------------------
| Receive Inventory
|--------------------------------------------------------------------------
*/

    .receive-inventory-panel {
        border: 1px solid #bae6fd;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 7px 22px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .receive-inventory-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 21px 23px;
        border-bottom: 1px solid #dbeafe;
        background:
            linear-gradient(135deg,
                #f8fbff 0%,
                #eff6ff 100%);
    }

    .receive-inventory-heading {
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .receive-inventory-icon {
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: #dbeafe;
        color: #0369a1;
        font-size: 18px;
    }

    .receive-inventory-header h2 {
        margin: 0 0 5px;
        color: var(--show-text);
        font-size: 18px;
    }

    .receive-inventory-header p {
        margin: 0;
        color: var(--show-muted);
        font-size: 10px;
        line-height: 1.6;
    }

    .receive-all-button {
        min-height: 41px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 14px;
        border: 1px solid #bae6fd;
        border-radius: 9px;
        background: #ffffff;
        color: #0369a1;
        font-family: inherit;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
        white-space: nowrap;
    }

    .receive-all-button:hover {
        border-color: #0369a1;
        background: #0369a1;
        color: #ffffff;
    }

    .receive-validation-error {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 16px 22px 0;
        padding: 11px 13px;
        border: 1px solid #fecaca;
        border-radius: 9px;
        background: #fef2f2;
        color: #b91c1c;
        font-size: 10px;
        font-weight: 700;
    }

    .receive-table-wrapper {
        overflow-x: auto;
    }

    .receive-table {
        width: 100%;
        min-width: 850px;
        border-collapse: collapse;
    }

    .receive-table th,
    .receive-table td {
        padding: 13px 15px;
        border-bottom: 1px solid var(--show-soft-border);
        vertical-align: middle;
        text-align: left;
    }

    .receive-table th {
        background: #f8fafc;
        color: var(--show-muted);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .receive-table td {
        color: #374151;
        font-size: 10px;
    }

    .receive-table .number-column {
        text-align: right;
        white-space: nowrap;
    }

    .receive-item-information {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 230px;
    }

    .receive-item-icon {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--show-border);
        border-radius: 9px;
        background: #f8fafc;
        color: #64748b;
    }

    .receive-item-information strong,
    .receive-item-information span {
        display: block;
    }

    .receive-item-information strong {
        margin-bottom: 3px;
        color: var(--show-text);
    }

    .receive-item-information span {
        color: var(--show-muted);
        font-size: 8px;
    }

    .already-received-value {
        color: var(--show-green);
    }

    .receive-remaining-value {
        color: var(--show-orange);
    }

    .receive-quantity-input {
        width: 95px;
        height: 38px;
        padding: 0 9px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        color: #111827;
        font-family: inherit;
        font-weight: 750;
        text-align: right;
        outline: none;
    }

    .receive-quantity-input:focus {
        border-color: #0369a1;
        box-shadow: 0 0 0 3px rgba(3, 105, 161, 0.1);
    }

    .receive-field-error {
        display: block;
        margin-top: 5px;
        color: #b91c1c;
        font-size: 8px;
        white-space: normal;
    }

    .receive-inventory-footer {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: end;
        gap: 20px;
        padding: 18px 22px;
        background: #f8fafc;
    }

    .receive-notes-field label {
        display: block;
        margin-bottom: 7px;
        color: #374151;
        font-size: 10px;
        font-weight: 750;
    }

    .receive-notes-field textarea {
        width: 100%;
        min-height: 80px;
        padding: 10px 11px;
        border: 1px solid #d7dce5;
        border-radius: 9px;
        background: #ffffff;
        color: #111827;
        font-family: inherit;
        resize: vertical;
        outline: none;
    }

    .receive-notes-field textarea:focus {
        border-color: #0369a1;
        box-shadow: 0 0 0 3px rgba(3, 105, 161, 0.1);
    }

    .receive-submit-area {
        min-width: 185px;
        text-align: right;
    }

    .receive-submit-area>span {
        display: block;
        margin-bottom: 4px;
        color: var(--show-muted);
        font-size: 9px;
    }

    .receive-submit-area>strong {
        display: block;
        margin-bottom: 11px;
        color: #0369a1;
        font-size: 23px;
    }

    .receive-inventory-button {
        min-height: 43px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 16px;
        border: 1px solid #0369a1;
        border-radius: 9px;
        background: #0369a1;
        color: #ffffff;
        font-family: inherit;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
    }

    .receive-inventory-button:hover:not(:disabled) {
        border-color: #075985;
        background: #075985;
    }

    .receive-inventory-button:disabled {
        border-color: #d7dce5;
        background: #e2e8f0;
        color: #94a3b8;
        cursor: not-allowed;
    }

    .purchase-order-button.pdf-action {
        border-color: #fecaca;
        background: var(--show-red-soft);
        color: var(--show-red);
    }

    .purchase-order-button.pdf-action:hover {
        border-color: var(--show-red);
        background: var(--show-red);
        color: #ffffff;
    }

    .purchase-order-button.excel-action {
        border-color: #a7f3d0;
        background: #ecfdf5;
        color: #047857;
    }

    .purchase-order-button.excel-action:hover {
        border-color: #047857;
        background: #047857;
        color: #ffffff;
    }

    @media (max-width: 1150px) {
        .purchase-order-show-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .purchase-order-header-actions {
            flex-wrap: wrap;
        }

        .purchase-order-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .purchase-order-bottom-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 750px) {

        .purchase-order-summary-grid,
        .purchase-order-information-grid {
            grid-template-columns: 1fr;
        }

        .purchase-order-header-actions,
        .purchase-order-button {
            width: 100%;
        }

        .purchase-order-heading {
            align-items: flex-start;
        }

        .information-list,
        .purchase-order-notes-panel {
            grid-template-columns: 1fr;
        }

        .information-list-wide {
            grid-column: auto;
        }

        .purchase-order-notes-section+.purchase-order-notes-section {
            border-top: 1px solid var(--show-border);
            border-left: 0;
        }

        .purchase-order-action-form {
            width: 100%;
        }

        .purchase-order-action-form .purchase-order-button {
            width: 100%;
        }

        .receive-inventory-header {
            align-items: stretch;
            flex-direction: column;
        }

        .receive-all-button {
            width: 100%;
        }

        .receive-inventory-footer {
            grid-template-columns: 1fr;
        }

        .receive-submit-area {
            min-width: 0;
            text-align: left;
        }

        .receive-inventory-button {
            width: 100%;
        }
    }

    @media print {

        .purchase-order-header-actions,
        .purchase-order-action-form,
        .purchase-order-alert,
        .receive-inventory-panel {
            display: none !important;
        }

        .purchase-order-show-page {
            gap: 12px;
        }

        .purchase-order-show-header,
        .purchase-order-summary-card,
        .purchase-order-progress-panel,
        .purchase-order-information-card,
        .purchase-order-items-panel,
        .purchase-order-notes-panel,
        .purchase-order-totals-panel {
            box-shadow: none !important;
        }

        .purchase-order-table {
            min-width: 0;
        }

        .purchase-order-table th,
        .purchase-order-table td {
            padding: 6px;
            font-size: 7px;
        }

        .purchase-order-summary-grid {
            grid-template-columns: repeat(4, 1fr);
        }

        .purchase-order-information-grid,
        .purchase-order-bottom-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

@endpush
@push('page-scripts')

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function() {
            'use strict';

            const actionForms = document.querySelectorAll(
                '.purchase-order-action-form'
            );

            actionForms.forEach(function(form) {
                form.addEventListener(
                    'submit',
                    function(event) {
                        const message =
                            form.dataset.confirmMessage ||
                            'Continue with this action?';

                        const confirmed =
                            window.confirm(message);

                        if (!confirmed) {
                            event.preventDefault();

                            return;
                        }

                        const submitButton =
                            form.querySelector(
                                'button[type="submit"]'
                            );

                        if (submitButton) {
                            submitButton.disabled = true;

                            submitButton.innerHTML =
                                '<i class="fa-solid fa-spinner fa-spin"></i>' +
                                ' Processing...';
                        }
                    }
                );
            });
            /*
|--------------------------------------------------------------------------
| Receive Inventory
|--------------------------------------------------------------------------
*/

            const receiveForm = document.getElementById(
                'receiveInventoryForm'
            );

            const receiveInputs = Array.from(
                document.querySelectorAll(
                    '.receive-quantity-input'
                )
            );

            const receiveAllButton = document.getElementById(
                'receiveAllRemainingButton'
            );

            const receiveQuantityTotal =
                document.getElementById(
                    'receiveQuantityTotal'
                );

            const receiveInventoryButton =
                document.getElementById(
                    'receiveInventoryButton'
                );

            function receiveNumberValue(value) {
                const parsed = Number.parseInt(
                    value,
                    10
                );

                return Number.isFinite(parsed) ?
                    parsed :
                    0;
            }

            function updateReceiveTotal() {
                let total = 0;

                receiveInputs.forEach(function(input) {
                    const remaining = Math.max(
                        0,
                        receiveNumberValue(
                            input.dataset.remaining
                        )
                    );

                    let quantity = Math.max(
                        0,
                        receiveNumberValue(
                            input.value
                        )
                    );

                    if (quantity > remaining) {
                        quantity = remaining;
                        input.value = String(remaining);
                    }

                    total += quantity;
                });

                if (receiveQuantityTotal) {
                    receiveQuantityTotal.textContent =
                        total.toLocaleString('en-GB');
                }

                if (receiveInventoryButton) {
                    receiveInventoryButton.disabled =
                        total <= 0;
                }
            }

            receiveInputs.forEach(function(input) {
                input.addEventListener(
                    'input',
                    updateReceiveTotal
                );

                input.addEventListener(
                    'change',
                    updateReceiveTotal
                );
            });

            if (receiveAllButton) {
                receiveAllButton.addEventListener(
                    'click',
                    function() {
                        receiveInputs.forEach(
                            function(input) {
                                input.value =
                                    input.dataset.remaining ||
                                    '0';
                            }
                        );

                        updateReceiveTotal();
                    }
                );
            }

            if (receiveForm) {
                receiveForm.addEventListener(
                    'submit',
                    function(event) {
                        const total = receiveInputs.reduce(
                            function(sum, input) {
                                return sum + Math.max(
                                    0,
                                    receiveNumberValue(
                                        input.value
                                    )
                                );
                            },
                            0
                        );

                        if (total <= 0) {
                            event.preventDefault();

                            window.alert(
                                'Enter a received quantity for at least one item.'
                            );

                            return;
                        }

                        const confirmed = window.confirm(
                            'Receive ' +
                            total.toLocaleString('en-GB') +
                            ' inventory units? Product stock will be increased immediately.'
                        );

                        if (!confirmed) {
                            event.preventDefault();

                            return;
                        }

                        if (receiveInventoryButton) {
                            receiveInventoryButton.disabled = true;

                            receiveInventoryButton.innerHTML =
                                '<i class="fa-solid fa-spinner fa-spin"></i>' +
                                ' Receiving...';
                        }
                    }
                );
            }

            updateReceiveTotal();
        }

    );
</script>

@endpush