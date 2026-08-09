@extends('admin.layouts.app')

@section('title', 'Purchase Orders')

@section('content')

<div class="purchase-orders-page">

    {{-- Page Header --}}
    <section class="purchase-orders-header">

        <div class="purchase-orders-heading">

            <div class="purchase-orders-heading-icon">

                <i class="fa-solid fa-file-invoice-dollar"></i>

            </div>

            <div>

                <span class="purchase-orders-eyebrow">
                    Inventory purchasing
                </span>

                <h1>
                    Purchase Orders
                </h1>

                <p>
                    Manage supplier orders, purchasing costs and inventory
                    receiving progress.
                </p>

            </div>

        </div>

        <div class="purchase-orders-header-actions">

            <a
                href="{{ route(
                    'admin.reorder-dashboard.index'
                ) }}"
                class="purchase-orders-button secondary">

                <i class="fa-solid fa-cart-flatbed"></i>

                Reorder Dashboard

            </a>

        </div>

    </section>

    {{-- Success Message --}}
    @if (session('success'))

    <div class="purchase-orders-alert success">

        <i class="fa-solid fa-circle-check"></i>

        <span>
            {{ session('success') }}
        </span>

    </div>

    @endif

    {{-- Error Message --}}
    @if (session('error'))

    <div class="purchase-orders-alert error">

        <i class="fa-solid fa-circle-exclamation"></i>

        <span>
            {{ session('error') }}
        </span>

    </div>

    @endif

    {{-- Main Statistics --}}
    <section class="purchase-orders-summary-grid">

        <article class="purchase-orders-summary-card">

            <div class="purchase-summary-card-top">

                <span class="purchase-summary-icon total">

                    <i class="fa-solid fa-file-invoice"></i>

                </span>

                <span class="purchase-summary-label">
                    Total Purchase Orders
                </span>

            </div>

            <strong class="purchase-summary-value">
                {{ number_format($totalPurchaseOrders) }}
            </strong>

            <span class="purchase-summary-description">
                All purchasing records
            </span>

        </article>

        <article class="purchase-orders-summary-card">

            <div class="purchase-summary-card-top">

                <span class="purchase-summary-icon open">

                    <i class="fa-solid fa-hourglass-half"></i>

                </span>

                <span class="purchase-summary-label">
                    Open Order Value
                </span>

            </div>

            <strong class="purchase-summary-value">
                £{{ number_format($openOrderValue, 2) }}
            </strong>

            <span class="purchase-summary-description">
                Draft, ordered and partially received
            </span>

        </article>

        <article class="purchase-orders-summary-card">

            <div class="purchase-summary-card-top">

                <span class="purchase-summary-icon received">

                    <i class="fa-solid fa-circle-check"></i>

                </span>

                <span class="purchase-summary-label">
                    Received Orders
                </span>

            </div>

            <strong class="purchase-summary-value">
                {{ number_format($receivedCount) }}
            </strong>

            <span class="purchase-summary-description">
                Fully completed purchase orders
            </span>

        </article>

        <article class="purchase-orders-summary-card">

            <div class="purchase-summary-card-top">

                <span class="purchase-summary-icon value">

                    <i class="fa-solid fa-sterling-sign"></i>

                </span>

                <span class="purchase-summary-label">
                    Total Purchase Value
                </span>

            </div>

            <strong class="purchase-summary-value">
                £{{ number_format($totalPurchaseValue, 2) }}
            </strong>

            <span class="purchase-summary-description">
                Excluding cancelled orders
            </span>

        </article>

    </section>

    {{-- Status Statistics --}}
    <section class="purchase-orders-status-grid">

        <a
            href="{{ route(
                'admin.purchase-orders.index',
                ['status' => 'draft']
            ) }}"
            class="purchase-status-card draft">

            <span class="purchase-status-icon">
                <i class="fa-solid fa-pen-to-square"></i>
            </span>

            <div>

                <span>
                    Draft
                </span>

                <strong>
                    {{ number_format($draftCount) }}
                </strong>

            </div>

        </a>

        <a
            href="{{ route(
                'admin.purchase-orders.index',
                ['status' => 'ordered']
            ) }}"
            class="purchase-status-card ordered">

            <span class="purchase-status-icon">
                <i class="fa-solid fa-paper-plane"></i>
            </span>

            <div>

                <span>
                    Ordered
                </span>

                <strong>
                    {{ number_format($orderedCount) }}
                </strong>

            </div>

        </a>

        <a
            href="{{ route(
                'admin.purchase-orders.index',
                ['status' => 'partially_received']
            ) }}"
            class="purchase-status-card partial">

            <span class="purchase-status-icon">
                <i class="fa-solid fa-boxes-packing"></i>
            </span>

            <div>

                <span>
                    Partially Received
                </span>

                <strong>
                    {{ number_format($partiallyReceivedCount) }}
                </strong>

            </div>

        </a>

        <a
            href="{{ route(
                'admin.purchase-orders.index',
                ['status' => 'received']
            ) }}"
            class="purchase-status-card received">

            <span class="purchase-status-icon">
                <i class="fa-solid fa-box-circle-check"></i>
            </span>

            <div>

                <span>
                    Received
                </span>

                <strong>
                    {{ number_format($receivedCount) }}
                </strong>

            </div>

        </a>

        <a
            href="{{ route(
                'admin.purchase-orders.index',
                ['status' => 'cancelled']
            ) }}"
            class="purchase-status-card cancelled">

            <span class="purchase-status-icon">
                <i class="fa-solid fa-ban"></i>
            </span>

            <div>

                <span>
                    Cancelled
                </span>

                <strong>
                    {{ number_format($cancelledCount) }}
                </strong>

            </div>

        </a>

    </section>

    {{-- Main Panel --}}
    <section class="purchase-orders-panel">

        <div class="purchase-orders-panel-header">

            <div>

                <span class="purchase-orders-eyebrow">
                    Purchasing history
                </span>

                <h2>
                    All Purchase Orders
                </h2>

                <p>
                    Search and filter purchasing records.
                </p>

            </div>

        </div>

        {{-- Filters --}}
        <form
            method="GET"
            action="{{ route(
                'admin.purchase-orders.index'
            ) }}"
            class="purchase-orders-filters">

            <div class="purchase-orders-search">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search reference, supplier or email..."
                    autocomplete="off">

            </div>

            <select name="status">

                <option value="">
                    All Statuses
                </option>

                <option
                    value="draft"
                    @selected(request('status')==='draft' )>

                    Draft

                </option>

                <option
                    value="ordered"
                    @selected(request('status')==='ordered' )>

                    Ordered

                </option>

                <option
                    value="partially_received"
                    @selected(
                    request('status')==='partially_received'
                    )>

                    Partially Received

                </option>

                <option
                    value="received"
                    @selected(request('status')==='received' )>

                    Received

                </option>

                <option
                    value="cancelled"
                    @selected(request('status')==='cancelled' )>

                    Cancelled

                </option>

            </select>

            <select name="sort">

                <option value="">
                    Newest First
                </option>

                <option
                    value="oldest"
                    @selected(request('sort')==='oldest' )>

                    Oldest First

                </option>

                <option
                    value="highest-total"
                    @selected(
                    request('sort')==='highest-total'
                    )>

                    Highest Total

                </option>

                <option
                    value="lowest-total"
                    @selected(
                    request('sort')==='lowest-total'
                    )>

                    Lowest Total

                </option>

                <option
                    value="expected-date"
                    @selected(
                    request('sort')==='expected-date'
                    )>

                    Expected Delivery

                </option>

            </select>

            <button
                type="submit"
                class="purchase-orders-filter-button">

                <i class="fa-solid fa-filter"></i>

                Filter

            </button>

            <a
                href="{{ route(
                    'admin.purchase-orders.index'
                ) }}"
                class="purchase-orders-reset-button">

                <i class="fa-solid fa-rotate-left"></i>

                Reset

            </a>

        </form>

        {{-- Table --}}
        <div class="purchase-orders-table-wrapper">

            <table class="purchase-orders-table">

                <thead>

                    <tr>

                        <th>Reference</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th class="number-column">Items</th>
                        <th class="number-column">Ordered</th>
                        <th class="number-column">Received</th>
                        <th class="number-column">Total</th>
                        <th>Order Date</th>
                        <th>Expected</th>
                        <th>Created By</th>
                        <th class="action-column"></th>

                    </tr>

                </thead>

                <tbody>

                    @forelse (
                    $purchaseOrders
                    as $purchaseOrder
                    )

                    @php
                    $orderedQuantity = (int) (
                    $purchaseOrder
                    ->ordered_quantity
                    ?? 0
                    );

                    $receivedQuantity = (int) (
                    $purchaseOrder
                    ->received_quantity
                    ?? 0
                    );

                    $receivingPercentage =
                    $orderedQuantity > 0
                    ? min(
                    100,
                    round(
                    (
                    $receivedQuantity
                    / $orderedQuantity
                    ) * 100
                    )
                    )
                    : 0;
                    @endphp

                    <tr>

                        <td>

                            <a
                                href="{{ route(
                                        'admin.purchase-orders.show',
                                        $purchaseOrder
                                    ) }}"
                                class="purchase-order-reference">

                                {{ $purchaseOrder->reference }}

                            </a>

                            <span class="purchase-order-created-date">
                                Created
                                {{ $purchaseOrder
                                        ->created_at
                                        ->format('d M Y H:i') }}
                            </span>

                        </td>

                        <td>

                            <strong class="purchase-order-supplier-name">
                                {{
                                        $purchaseOrder->supplier_name
                                        ?: 'Not assigned'
                                    }}
                            </strong>

                            @if (
                            filled(
                            $purchaseOrder->supplier_email
                            )
                            )

                            <span class="purchase-order-supplier-email">
                                {{ $purchaseOrder->supplier_email }}
                            </span>

                            @endif

                        </td>

                        <td>

                            <span
                                class="purchase-order-status {{
                                        $purchaseOrder->status
                                    }}">

                                {{ $purchaseOrder->status_label }}

                            </span>

                        </td>

                        <td class="number-column">

                            {{ number_format(
                                    $purchaseOrder->items_count
                                ) }}

                        </td>

                        <td class="number-column">

                            {{ number_format(
                                    $orderedQuantity
                                ) }}

                        </td>

                        <td class="number-column">

                            <div class="receiving-progress">

                                <div class="receiving-progress-top">

                                    <span>
                                        {{ number_format(
                                                $receivedQuantity
                                            ) }}
                                    </span>

                                    <small>
                                        {{ $receivingPercentage }}%
                                    </small>

                                </div>

                                <div class="receiving-progress-track">

                                    <span
                                        style="width:{{
                                                $receivingPercentage
                                            }}%;">
                                    </span>

                                </div>

                            </div>

                        </td>

                        <td class="number-column">

                            <strong class="purchase-order-total">
                                £{{ number_format(
                                        $purchaseOrder
                                            ->total_amount,
                                        2
                                    ) }}
                            </strong>

                        </td>

                        <td>

                            {{
                                    optional(
                                        $purchaseOrder->order_date
                                    )->format('d M Y')
                                    ?: 'Not set'
                                }}

                        </td>

                        <td>

                            @if (
                            $purchaseOrder->expected_date
                            )

                            <span class="{{
                                        $purchaseOrder
                                            ->expected_date
                                            ->isPast()
                                        && !$purchaseOrder
                                            ->isReceived()
                                        && !$purchaseOrder
                                            ->isCancelled()
                                            ? 'purchase-order-overdue'
                                            : ''
                                    }}">

                                {{
                                            $purchaseOrder
                                                ->expected_date
                                                ->format('d M Y')
                                        }}

                            </span>

                            @else

                            <span class="purchase-order-muted">
                                Not set
                            </span>

                            @endif

                        </td>

                        <td>

                            {{
                                    $purchaseOrder
                                        ->creator
                                        ? (
                                            $purchaseOrder
                                                ->creator
                                                ->name
                                            ?? $purchaseOrder
                                                ->creator
                                                ->email
                                        )
                                        : 'System'
                                }}

                        </td>

                        <td class="action-column">

                            <div class="purchase-order-row-actions">

                                <a
                                    href="{{ route(
                'admin.purchase-orders.pdf',
                $purchaseOrder
            ) }}"
                                    class="purchase-order-row-action pdf"
                                    title="Download PDF">

                                    <i class="fa-solid fa-file-pdf"></i>

                                </a>
                                <a
                                    href="{{ route(
        'admin.purchase-orders.excel',
        $purchaseOrder
    ) }}"
                                    class="purchase-order-row-action excel"
                                    title="Download Excel">

                                    <i class="fa-solid fa-file-excel"></i>

                                </a>
                                <a
                                    href="{{ route(
                'admin.purchase-orders.show',
                $purchaseOrder
            ) }}"
                                    class="purchase-order-row-action"
                                    title="View purchase order">

                                    <i class="fa-solid fa-arrow-right"></i>

                                </a>

                            </div>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td
                            colspan="11"
                            class="purchase-orders-empty">

                            <div class="purchase-orders-empty-icon">

                                <i class="fa-solid fa-file-circle-xmark"></i>

                            </div>

                            <h3>
                                No purchase orders found
                            </h3>

                            <p>
                                Generate a purchase order from the
                                Reorder Dashboard.
                            </p>

                            <a
                                href="{{ route(
                                        'admin.reorder-dashboard.index'
                                    ) }}">

                                Open Reorder Dashboard

                            </a>

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Pagination --}}
        @if ($purchaseOrders->hasPages())

        <div class="purchase-orders-pagination">

            {{ $purchaseOrders->links() }}

        </div>

        @endif

    </section>

</div>

@endsection

@push('page-styles')

<style>
    .purchase-orders-page {
        --po-text: #111827;
        --po-muted: #64748b;
        --po-border: #e5e7eb;
        --po-soft-border: #eef2f7;
        --po-indigo: #4f46e5;
        --po-indigo-soft: #eef2ff;
        --po-green: #15803d;
        --po-green-soft: #ecfdf3;
        --po-orange: #c2410c;
        --po-orange-soft: #fff7ed;
        --po-red: #b91c1c;
        --po-red-soft: #fef2f2;
        --po-blue: #0369a1;
        --po-blue-soft: #f0f9ff;

        display: flex;
        flex-direction: column;
        gap: 22px;
        min-width: 0;
        color: var(--po-text);
    }

    .purchase-orders-page *,
    .purchase-orders-page *::before,
    .purchase-orders-page *::after {
        box-sizing: border-box;
    }

    .purchase-orders-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 22px;
        padding: 27px 29px;
        border: 1px solid var(--po-border);
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

    .purchase-orders-heading {
        display: flex;
        align-items: center;
        gap: 17px;
        min-width: 0;
    }

    .purchase-orders-heading-icon {
        width: 58px;
        height: 58px;
        flex: 0 0 58px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: var(--po-indigo-soft);
        color: var(--po-indigo);
        font-size: 22px;
    }

    .purchase-orders-eyebrow {
        display: block;
        margin-bottom: 6px;
        color: var(--po-indigo);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .purchase-orders-header h1 {
        margin: 0 0 7px;
        font-size: 29px;
    }

    .purchase-orders-header p {
        margin: 0;
        color: var(--po-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .purchase-orders-header-actions {
        flex-shrink: 0;
    }

    .purchase-orders-button {
        min-height: 43px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 15px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 750;
        text-decoration: none;
    }

    .purchase-orders-button.secondary {
        border: 1px solid #c7d2fe;
        background: var(--po-indigo-soft);
        color: var(--po-indigo);
    }

    .purchase-orders-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        border-radius: 11px;
        font-size: 12px;
        font-weight: 650;
    }

    .purchase-orders-alert.success {
        border: 1px solid #bbf7d0;
        background: #f0fdf4;
        color: #15803d;
    }

    .purchase-orders-alert.error {
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #b91c1c;
    }

    .purchase-orders-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 17px;
    }

    .purchase-orders-summary-card {
        padding: 20px;
        border: 1px solid var(--po-border);
        border-radius: 15px;
        background: #ffffff;
        box-shadow: 0 7px 20px rgba(15, 23, 42, 0.04);
    }

    .purchase-summary-card-top {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 17px;
    }

    .purchase-summary-icon {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
    }

    .purchase-summary-icon.total {
        background: var(--po-indigo-soft);
        color: var(--po-indigo);
    }

    .purchase-summary-icon.open {
        background: var(--po-orange-soft);
        color: var(--po-orange);
    }

    .purchase-summary-icon.received,
    .purchase-summary-icon.value {
        background: var(--po-green-soft);
        color: var(--po-green);
    }

    .purchase-summary-label {
        color: var(--po-muted);
        font-size: 12px;
        font-weight: 700;
    }

    .purchase-summary-value {
        display: block;
        margin-bottom: 8px;
        font-size: 25px;
    }

    .purchase-summary-description {
        color: var(--po-muted);
        font-size: 10px;
        line-height: 1.5;
    }

    .purchase-orders-status-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 13px;
    }

    .purchase-status-card {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 14px 15px;
        border: 1px solid var(--po-border);
        border-radius: 12px;
        background: #ffffff;
        color: var(--po-text);
        text-decoration: none;
        transition: 0.2s ease;
    }

    .purchase-status-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
    }

    .purchase-status-icon {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .purchase-status-card.draft .purchase-status-icon {
        background: #f1f5f9;
        color: #475569;
    }

    .purchase-status-card.ordered .purchase-status-icon {
        background: var(--po-blue-soft);
        color: var(--po-blue);
    }

    .purchase-status-card.partial .purchase-status-icon {
        background: var(--po-orange-soft);
        color: var(--po-orange);
    }

    .purchase-status-card.received .purchase-status-icon {
        background: var(--po-green-soft);
        color: var(--po-green);
    }

    .purchase-status-card.cancelled .purchase-status-icon {
        background: var(--po-red-soft);
        color: var(--po-red);
    }

    .purchase-status-card span {
        display: block;
        margin-bottom: 3px;
        color: var(--po-muted);
        font-size: 10px;
    }

    .purchase-status-card strong {
        font-size: 18px;
    }

    .purchase-orders-panel {
        border: 1px solid var(--po-border);
        border-radius: 17px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .purchase-orders-panel-header {
        padding: 21px 23px;
        border-bottom: 1px solid var(--po-border);
    }

    .purchase-orders-panel-header h2 {
        margin: 0 0 5px;
        font-size: 20px;
    }

    .purchase-orders-panel-header p {
        margin: 0;
        color: var(--po-muted);
        font-size: 11px;
    }

    .purchase-orders-filters {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 23px;
        border-bottom: 1px solid var(--po-border);
        background: #f8fafc;
    }

    .purchase-orders-search {
        position: relative;
        width: min(100%, 340px);
        flex: 0 0 340px;
    }

    .purchase-orders-search i {
        position: absolute;
        top: 50%;
        left: 13px;
        color: #94a3b8;
        transform: translateY(-50%);
    }

    .purchase-orders-search input {
        width: 100%;
        height: 43px;
        padding: 0 13px 0 39px;
        border: 1px solid #d7dce5;
        border-radius: 9px;
        background: #ffffff;
        font-family: inherit;
        outline: none;
    }

    .purchase-orders-filters select {
        min-width: 165px;
        height: 43px;
        padding: 0 12px;
        border: 1px solid #d7dce5;
        border-radius: 9px;
        background: #ffffff;
        font-family: inherit;
    }

    .purchase-orders-filter-button,
    .purchase-orders-reset-button {
        min-height: 43px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 14px;
        border-radius: 9px;
        font-family: inherit;
        font-size: 11px;
        font-weight: 750;
        text-decoration: none;
    }

    .purchase-orders-filter-button {
        border: 1px solid var(--po-indigo);
        background: var(--po-indigo);
        color: #ffffff;
        cursor: pointer;
    }

    .purchase-orders-reset-button {
        border: 1px solid #d7dce5;
        background: #ffffff;
        color: #475569;
    }

    .purchase-orders-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .purchase-orders-table {
        width: 100%;
        min-width: 1320px;
        border-collapse: collapse;
    }

    .purchase-orders-table th,
    .purchase-orders-table td {
        padding: 14px 15px;
        border-bottom: 1px solid var(--po-soft-border);
        vertical-align: middle;
        text-align: left;
    }

    .purchase-orders-table th {
        background: #f8fafc;
        color: var(--po-muted);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 0.055em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .purchase-orders-table td {
        color: #374151;
        font-size: 11px;
    }

    .purchase-orders-table tbody tr:hover {
        background: #fafbff;
    }

    .purchase-orders-table .number-column {
        text-align: right;
        white-space: nowrap;
    }

    .purchase-orders-table .action-column {
        width: 130px;
        text-align: right;
    }

    .purchase-order-reference {
        display: block;
        margin-bottom: 4px;
        color: var(--po-indigo);
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
    }

    .purchase-order-created-date,
    .purchase-order-supplier-email,
    .purchase-order-muted {
        display: block;
        color: var(--po-muted);
        font-size: 9px;
    }

    .purchase-order-supplier-name {
        display: block;
        margin-bottom: 3px;
    }

    .purchase-order-status {
        display: inline-flex;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .purchase-order-status.draft {
        background: #f1f5f9;
        color: #475569;
    }

    .purchase-order-status.ordered {
        background: var(--po-blue-soft);
        color: var(--po-blue);
    }

    .purchase-order-status.partially_received {
        background: var(--po-orange-soft);
        color: var(--po-orange);
    }

    .purchase-order-status.received {
        background: var(--po-green-soft);
        color: var(--po-green);
    }

    .purchase-order-status.cancelled {
        background: var(--po-red-soft);
        color: var(--po-red);
    }

    .receiving-progress {
        min-width: 100px;
    }

    .receiving-progress-top {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 5px;
    }

    .receiving-progress-top small {
        color: var(--po-muted);
    }

    .receiving-progress-track {
        height: 5px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .receiving-progress-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: var(--po-green);
    }

    .purchase-order-total {
        color: var(--po-green);
    }

    .purchase-order-overdue {
        color: var(--po-red);
        font-weight: 800;
    }

    .purchase-order-row-action {
        width: 33px;
        height: 33px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--po-border);
        border-radius: 8px;
        background: #ffffff;
        color: #64748b;
        text-decoration: none;
    }

    .purchase-order-row-action:hover {
        border-color: var(--po-indigo);
        background: var(--po-indigo);
        color: #ffffff;
    }

    .purchase-orders-empty {
        padding: 55px 20px !important;
        text-align: center !important;
    }

    .purchase-orders-empty-icon {
        width: 55px;
        height: 55px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        border-radius: 15px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 20px;
    }

    .purchase-orders-empty h3 {
        margin: 0 0 6px;
    }

    .purchase-orders-empty p {
        margin: 0 0 14px;
        color: var(--po-muted);
    }

    .purchase-orders-empty a {
        color: var(--po-indigo);
        font-weight: 750;
        text-decoration: none;
    }

    .purchase-orders-pagination {
        padding: 17px 22px;
        border-top: 1px solid var(--po-border);
    }

    .purchase-order-row-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }

    .purchase-order-row-action.pdf {
        border-color: #fecaca;
        background: #fef2f2;
        color: #b91c1c;
    }

    .purchase-order-row-action.pdf:hover {
        border-color: #b91c1c;
        background: #b91c1c;
        color: #ffffff;
    }

    .purchase-order-row-action.excel {
        border-color: #a7f3d0;
        background: #ecfdf5;
        color: #047857;
    }

    .purchase-order-row-action.excel:hover {
        border-color: #047857;
        background: #047857;
        color: #ffffff;
    }

    @media (max-width: 1200px) {
        .purchase-orders-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .purchase-orders-status-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .purchase-orders-filters {
            align-items: stretch;
            flex-wrap: wrap;
        }

        .purchase-orders-search {
            width: 100%;
            flex-basis: 100%;
        }
    }

    @media (max-width: 700px) {
        .purchase-orders-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .purchase-orders-heading {
            align-items: flex-start;
        }

        .purchase-orders-header-actions,
        .purchase-orders-button {
            width: 100%;
        }

        .purchase-orders-summary-grid,
        .purchase-orders-status-grid {
            grid-template-columns: 1fr;
        }

        .purchase-orders-filters,
        .purchase-orders-filters select,
        .purchase-orders-filter-button,
        .purchase-orders-reset-button {
            width: 100%;
        }
    }
</style>

@endpush