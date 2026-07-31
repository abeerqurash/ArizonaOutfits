@extends('admin.layouts.app')

@section('title', 'Inventory Reports')

@section('content')
<div class="inventory-report-page">

    {{-- Page header --}}
    <div class="report-header">

        <div>
            <h1>Inventory Reports</h1>

            <p>
                Review stock levels, inventory movements,
                low-stock items and recent inventory activity.
            </p>
        </div>

        <div class="report-header-right">

            <div class="report-period">
                {{ $startDate->format('d M Y') }}
                <span>—</span>
                {{ $endDate->format('d M Y') }}
            </div>

            <a
                href="{{ route('admin.inventory-reports.export', request()->query()) }}"
                class="report-button primary">
                Export CSV
            </a>

        </div>

    </div>

    {{-- Date filters --}}
    <div class="report-panel filter-panel">
        <form
            method="GET"
            action="{{ route('admin.inventory-reports.index') }}"
            class="report-filter-form"
            id="inventoryReportFilter">
            <div class="filter-field">
                <label for="date_range">Report period</label>

                <select
                    name="date_range"
                    id="date_range"
                    class="report-input">
                    <option
                        value="today"
                        @selected($dateRange==='today' )>
                        Today
                    </option>

                    <option
                        value="7_days"
                        @selected($dateRange==='7_days' )>
                        Last 7 days
                    </option>

                    <option
                        value="30_days"
                        @selected($dateRange==='30_days' )>
                        Last 30 days
                    </option>

                    <option
                        value="90_days"
                        @selected($dateRange==='90_days' )>
                        Last 90 days
                    </option>

                    <option
                        value="this_month"
                        @selected($dateRange==='this_month' )>
                        This month
                    </option>

                    <option
                        value="last_month"
                        @selected($dateRange==='last_month' )>
                        Last month
                    </option>

                    <option
                        value="custom"
                        @selected($dateRange==='custom' )>
                        Custom dates
                    </option>
                </select>
            </div>

            <div
                class="filter-field custom-date-field"
                data-custom-date>
                <label for="start_date">Start date</label>

                <input
                    type="date"
                    name="start_date"
                    id="start_date"
                    class="report-input"
                    value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
            </div>

            <div
                class="filter-field custom-date-field"
                data-custom-date>
                <label for="end_date">End date</label>

                <input
                    type="date"
                    name="end_date"
                    id="end_date"
                    class="report-input"
                    value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
            </div>

            <div class="filter-actions">
                <button type="submit" class="report-button primary">
                    Apply filter
                </button>

                <a
                    href="{{ route('admin.inventory-reports.index') }}"
                    class="report-button secondary">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Main summary --}}
    <div class="summary-grid">

        <div class="summary-card">
            <div class="summary-card-label">
                Total products
            </div>

            <div class="summary-card-value">
                {{ number_format($totalProducts) }}
            </div>

            <div class="summary-card-meta">
                {{ number_format($totalVariants) }} variants
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-card-label">
                Total stock units
            </div>

            <div class="summary-card-value">
                {{ number_format($totalStockUnits) }}
            </div>

            <div class="summary-card-meta">
                Products: {{ number_format($totalProductStock) }}
                · Variants: {{ number_format($totalVariantStock) }}
            </div>
        </div>

        <div class="summary-card warning">
            <div class="summary-card-label">
                Low-stock products
            </div>

            <div class="summary-card-value">
                {{ number_format($lowStockProducts) }}
            </div>

            <div class="summary-card-meta">
                Requires attention
            </div>
        </div>

        <div class="summary-card danger">
            <div class="summary-card-label">
                Out-of-stock products
            </div>

            <div class="summary-card-value">
                {{ number_format($outOfStockProducts) }}
            </div>

            <div class="summary-card-meta">
                Currently unavailable
            </div>
        </div>

    </div>

    {{-- Movement summary --}}
    <div class="summary-grid movement-summary">

        <div class="summary-card">
            <div class="summary-card-label">
                Inventory movements
            </div>

            <div class="summary-card-value">
                {{ number_format($totalMovements) }}
            </div>

            <div class="summary-card-meta">
                During selected period
            </div>
        </div>

        <div class="summary-card success">
            <div class="summary-card-label">
                Stock added
            </div>

            <div class="summary-card-value">
                +{{ number_format($totalStockAdded) }}
            </div>

            <div class="summary-card-meta">
                Units added
            </div>
        </div>

        <div class="summary-card danger">
            <div class="summary-card-label">
                Stock removed
            </div>

            <div class="summary-card-value">
                -{{ number_format($totalStockRemoved) }}
            </div>

            <div class="summary-card-meta">
                Units removed
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-card-label">
                Net stock movement
            </div>

            @php
            $netMovement = $totalStockAdded - $totalStockRemoved;
            @endphp

            <div class="summary-card-value">
                {{ $netMovement > 0 ? '+' : '' }}
                {{ number_format($netMovement) }}
            </div>

            <div class="summary-card-meta">
                Added minus removed
            </div>
        </div>

    </div>

    {{-- Movement types and adjusted products --}}
    <div class="report-grid two-columns">

        <section class="report-panel">
            <div class="panel-heading">
                <div>
                    <h2>Movement breakdown</h2>
                    <p>Inventory activity grouped by movement type.</p>
                </div>
            </div>

            <div class="report-table-wrapper">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Movement type</th>
                            <th>Movements</th>
                            <th>Quantity change</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($movementTypeTotals as $movement)
                        @php
                        $quantityChange = (int) $movement->total_quantity_change;
                        @endphp

                        <tr>
                            <td>
                                <span class="movement-badge">
                                    {{ ucwords(str_replace('_', ' ', $movement->movement_type)) }}
                                </span>
                            </td>

                            <td>
                                {{ number_format($movement->total_movements) }}
                            </td>

                            <td>
                                <span
                                    class="
                                            quantity-change
                                            {{ $quantityChange > 0 ? 'positive' : '' }}
                                            {{ $quantityChange < 0 ? 'negative' : '' }}
                                        ">
                                    {{ $quantityChange > 0 ? '+' : '' }}
                                    {{ number_format($quantityChange) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="empty-state">
                                No inventory movements were found for this period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="report-panel">
            <div class="panel-heading">
                <div>
                    <h2>Most adjusted products</h2>
                    <p>Products with the highest inventory activity.</p>
                </div>
            </div>

            <div class="report-table-wrapper">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Movements</th>
                            <th>Units moved</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($mostAdjustedProducts as $item)
                        <tr>
                            <td>
                                @if($item->product)
                                <a
                                    href="{{ route(
                                                'admin.products.inventory.edit',
                                                $item->product
                                            ) }}"
                                    class="product-link">
                                    {{ $item->product->name
                                                ?? $item->product->title
                                                ?? 'Product #' . $item->product_id }}
                                </a>

                                @if(!empty($item->product->sku))
                                <div class="table-subtext">
                                    SKU: {{ $item->product->sku }}
                                </div>
                                @endif
                                @else
                                <span class="muted-text">
                                    Deleted product
                                </span>
                                @endif
                            </td>

                            <td>
                                {{ number_format($item->movement_count) }}
                            </td>

                            <td>
                                {{ number_format($item->total_quantity_moved) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="empty-state">
                                No adjusted products were found for this period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    {{-- Low and out of stock --}}
    <div class="report-grid two-columns">

        <section class="report-panel">
            <div class="panel-heading">
                <div>
                    <h2>Low-stock products</h2>
                    <p>Products approaching their stock threshold.</p>
                </div>

                <a
                    href="{{ route('admin.inventory-alerts.index') }}"
                    class="panel-link">
                    View alerts
                </a>
            </div>

            <div class="report-table-wrapper">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Current stock</th>
                            <th>Threshold</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($lowStockItems as $product)
                        <tr>
                            <td>
                                <a
                                    href="{{ route(
                                            'admin.products.inventory.edit',
                                            $product
                                        ) }}"
                                    class="product-link">
                                    {{ $product->name
                                            ?? $product->title
                                            ?? 'Product #' . $product->id }}
                                </a>

                                @if(!empty($product->sku))
                                <div class="table-subtext">
                                    SKU: {{ $product->sku }}
                                </div>
                                @endif
                            </td>

                            <td>
                                <span class="stock-badge low">
                                    {{ number_format($product->stock) }}
                                </span>
                            </td>

                            <td>
                                {{ number_format(
                                        $product->low_stock_threshold ?? 5
                                    ) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="empty-state">
                                No low-stock products found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="report-panel">
            <div class="panel-heading">
                <div>
                    <h2>Out-of-stock products</h2>
                    <p>Products with no available inventory.</p>
                </div>
            </div>

            <div class="report-table-wrapper">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Stock</th>
                            <th>Last updated</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($outOfStockItems as $product)
                        <tr>
                            <td>
                                <a
                                    href="{{ route(
                                            'admin.products.inventory.edit',
                                            $product
                                        ) }}"
                                    class="product-link">
                                    {{ $product->name
                                            ?? $product->title
                                            ?? 'Product #' . $product->id }}
                                </a>

                                @if(!empty($product->sku))
                                <div class="table-subtext">
                                    SKU: {{ $product->sku }}
                                </div>
                                @endif
                            </td>

                            <td>
                                <span class="stock-badge out">
                                    {{ number_format($product->stock) }}
                                </span>
                            </td>

                            <td>
                                {{ optional($product->updated_at)->format('d M Y, h:i A') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="empty-state">
                                No out-of-stock products found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    {{-- Recent movements --}}
    <section class="report-panel">
        <div class="panel-heading">
            <div>
                <h2>Recent inventory movements</h2>
                <p>Latest stock changes across products and variants.</p>
            </div>

            <a
                href="{{ route('admin.inventory-history.index') }}"
                class="panel-link">
                View full history
            </a>
        </div>

        <div class="report-table-wrapper">
            <table class="report-table recent-movements-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Movement</th>
                        <th>Before</th>
                        <th>Change</th>
                        <th>After</th>
                        <th>User</th>
                        <th>Reason</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($recentMovements as $movement)
                    @php
                    $quantityChange = (int) $movement->quantity_change;
                    @endphp

                    <tr>
                        <td>
                            <span class="date-main">
                                {{ optional($movement->created_at)->format('d M Y') }}
                            </span>

                            <span class="date-time">
                                {{ optional($movement->created_at)->format('h:i A') }}
                            </span>
                        </td>

                        <td>
                            @if($movement->product)
                            <a
                                href="{{ route(
                                            'admin.products.inventory.edit',
                                            $movement->product
                                        ) }}"
                                class="product-link">
                                {{ $movement->product->name
                                            ?? $movement->product->title
                                            ?? 'Product #' . $movement->product_id }}
                            </a>

                            @if(!empty($movement->product->sku))
                            <div class="table-subtext">
                                SKU: {{ $movement->product->sku }}
                            </div>
                            @endif
                            @else
                            <span class="muted-text">
                                Deleted product
                            </span>
                            @endif
                        </td>

                        <td>
                            @if($movement->variant)
                            {{ $movement->variant->name
                                        ?? $movement->variant->title
                                        ?? $movement->variant->sku
                                        ?? 'Variant #' . $movement->product_variant_id }}
                            @else
                            <span class="muted-text">—</span>
                            @endif
                        </td>

                        <td>
                            <span class="movement-badge">
                                {{ ucwords(str_replace(
                                        '_',
                                        ' ',
                                        $movement->movement_type
                                    )) }}
                            </span>
                        </td>

                        <td>
                            {{ number_format($movement->stock_before) }}
                        </td>

                        <td>
                            <span
                                class="
                                        quantity-change
                                        {{ $quantityChange > 0 ? 'positive' : '' }}
                                        {{ $quantityChange < 0 ? 'negative' : '' }}
                                    ">
                                {{ $quantityChange > 0 ? '+' : '' }}
                                {{ number_format($quantityChange) }}
                            </span>
                        </td>

                        <td>
                            <strong>
                                {{ number_format($movement->stock_after) }}
                            </strong>
                        </td>

                        <td>
                            {{ $movement->user?->name ?? 'System' }}
                        </td>

                        <td>
                            {{ $movement->reason ?: '—' }}

                            @if($movement->order)
                            <div class="table-subtext">
                                Order:
                                {{ $movement->order->order_number
                                            ?? '#' . $movement->order->id }}
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="empty-state">
                            No inventory history has been recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection

@push('page-styles')
<style>
    /*
    |--------------------------------------------------------------------------
    | Inventory Reports Dashboard
    |--------------------------------------------------------------------------
    */

    .inventory-report-page {
        --report-primary: #111827;
        --report-primary-hover: #1f2937;
        --report-text: #111827;
        --report-muted: #6b7280;
        --report-border: #e5e7eb;
        --report-soft-border: #eef0f3;
        --report-background: #f6f7fb;
        --report-card: #ffffff;
        --report-indigo: #4f46e5;
        --report-indigo-soft: #eef2ff;
        --report-green: #15803d;
        --report-green-soft: #ecfdf3;
        --report-orange: #c2410c;
        --report-orange-soft: #fff7ed;
        --report-red: #b91c1c;
        --report-red-soft: #fef2f2;
        --report-blue: #0369a1;
        --report-blue-soft: #f0f9ff;

        display: flex;
        flex-direction: column;
        gap: 24px;
        min-width: 0;
        padding: 4px;
        color: var(--report-text);
    }

    .inventory-report-page *,
    .inventory-report-page *::before,
    .inventory-report-page *::after {
        box-sizing: border-box;
    }

    /*
    |--------------------------------------------------------------------------
    | Page Header
    |--------------------------------------------------------------------------
    */

    .report-header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 28px 30px;
        border: 1px solid rgba(229, 231, 235, 0.8);
        border-radius: 20px;
        background:
            radial-gradient(
                circle at top right,
                rgba(79, 70, 229, 0.13),
                transparent 35%
            ),
            linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.04),
            0 12px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .report-header::before {
        content: "";
        position: absolute;
        top: -60px;
        right: -40px;
        width: 180px;
        height: 180px;
        border: 28px solid rgba(79, 70, 229, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }

    .report-header > div:first-child {
        position: relative;
        z-index: 1;
        min-width: 0;
    }

    .report-header h1 {
        margin: 0 0 8px;
        color: #111827;
        font-size: clamp(25px, 3vw, 34px);
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.035em;
    }

    .report-header p {
        max-width: 650px;
        margin: 0;
        color: var(--report-muted);
        font-size: 14px;
        line-height: 1.7;
    }

    .report-header-right {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        flex-shrink: 0;
    }

    .report-period {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 10px 16px;
        border: 1px solid var(--report-border);
        border-radius: 11px;
        background: rgba(255, 255, 255, 0.9);
        color: #374151;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        backdrop-filter: blur(8px);
    }

    .report-period span {
        color: #9ca3af;
    }

    /*
    |--------------------------------------------------------------------------
    | Buttons
    |--------------------------------------------------------------------------
    */

    .report-button {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 18px;
        border: 1px solid transparent;
        border-radius: 11px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 750;
        line-height: 1;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition:
            transform 0.2s ease,
            background-color 0.2s ease,
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .report-button:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .report-button:active {
        transform: translateY(0);
    }

    .report-button:focus-visible {
        outline: 3px solid rgba(79, 70, 229, 0.22);
        outline-offset: 2px;
    }

    .report-button.primary {
        border-color: var(--report-primary);
        background: var(--report-primary);
        color: #ffffff;
        box-shadow: 0 8px 18px rgba(17, 24, 39, 0.16);
    }

    .report-button.primary:hover {
        border-color: var(--report-primary-hover);
        background: var(--report-primary-hover);
        color: #ffffff;
        box-shadow: 0 10px 22px rgba(17, 24, 39, 0.2);
    }

    .report-button.secondary {
        border-color: #d8dce3;
        background: #ffffff;
        color: #374151;
    }

    .report-button.secondary:hover {
        border-color: #b9c0cb;
        background: #f9fafb;
        color: #111827;
    }

    /*
    |--------------------------------------------------------------------------
    | Panels
    |--------------------------------------------------------------------------
    */

    .report-panel {
        min-width: 0;
        border: 1px solid var(--report-border);
        border-radius: 18px;
        background: var(--report-card);
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.03),
            0 8px 24px rgba(15, 23, 42, 0.045);
        overflow: hidden;
        transition:
            box-shadow 0.2s ease,
            transform 0.2s ease;
    }

    .report-panel:hover {
        box-shadow:
            0 2px 5px rgba(15, 23, 42, 0.04),
            0 14px 34px rgba(15, 23, 42, 0.065);
    }

    /*
    |--------------------------------------------------------------------------
    | Filter Panel
    |--------------------------------------------------------------------------
    */

    .filter-panel {
        position: relative;
        padding: 22px;
        overflow: visible;
    }

    .filter-panel::before {
        content: "Filters";
        display: block;
        margin-bottom: 16px;
        color: #111827;
        font-size: 15px;
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .report-filter-form {
        display: grid;
        grid-template-columns:
            minmax(190px, 1.1fr)
            minmax(160px, 1fr)
            minmax(160px, 1fr)
            auto;
        align-items: end;
        gap: 16px;
    }

    .filter-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .filter-field label {
        color: #4b5563;
        font-size: 12px;
        font-weight: 750;
    }

    .report-input {
        width: 100%;
        min-height: 44px;
        padding: 10px 13px;
        border: 1px solid #d8dce3;
        border-radius: 11px;
        background: #ffffff;
        color: #111827;
        font-family: inherit;
        font-size: 13px;
        outline: none;
        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease,
            background-color 0.2s ease;
    }

    .report-input:hover {
        border-color: #b9c0cb;
    }

    .report-input:focus {
        border-color: var(--report-indigo);
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    }

    select.report-input {
        cursor: pointer;
    }

    .filter-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /*
    |--------------------------------------------------------------------------
    | Summary Cards
    |--------------------------------------------------------------------------
    */

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .summary-card {
        position: relative;
        min-width: 0;
        min-height: 142px;
        padding: 22px 22px 20px;
        border: 1px solid var(--report-border);
        border-radius: 18px;
        background:
            linear-gradient(
                145deg,
                rgba(255, 255, 255, 1) 0%,
                rgba(249, 250, 251, 0.72) 100%
            );
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.03),
            0 8px 22px rgba(15, 23, 42, 0.045);
        overflow: hidden;
        transition:
            transform 0.2s ease,
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .summary-card:hover {
        transform: translateY(-3px);
        border-color: #d7dbe2;
        box-shadow:
            0 4px 8px rgba(15, 23, 42, 0.04),
            0 16px 34px rgba(15, 23, 42, 0.07);
    }

    .summary-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: var(--report-indigo);
    }

    .summary-card::after {
        content: "";
        position: absolute;
        top: -32px;
        right: -32px;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: var(--report-indigo-soft);
        opacity: 0.75;
        pointer-events: none;
    }

    .summary-card.success::before {
        background: #16a34a;
    }

    .summary-card.success::after {
        background: var(--report-green-soft);
    }

    .summary-card.warning::before {
        background: #f59e0b;
    }

    .summary-card.warning::after {
        background: var(--report-orange-soft);
    }

    .summary-card.danger::before {
        background: #dc2626;
    }

    .summary-card.danger::after {
        background: var(--report-red-soft);
    }

    .summary-card-label,
    .summary-card-value,
    .summary-card-meta {
        position: relative;
        z-index: 1;
    }

    .summary-card-label {
        margin-bottom: 14px;
        color: #6b7280;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.07em;
    }

    .summary-card-value {
        margin-bottom: 10px;
        color: #111827;
        font-size: clamp(28px, 3vw, 36px);
        font-weight: 850;
        line-height: 1;
        letter-spacing: -0.04em;
    }

    .summary-card-meta {
        color: #7b8494;
        font-size: 12px;
        line-height: 1.55;
    }

    .movement-summary .summary-card:nth-child(2) .summary-card-value {
        color: var(--report-green);
    }

    .movement-summary .summary-card:nth-child(3) .summary-card-value {
        color: var(--report-red);
    }

    /*
    |--------------------------------------------------------------------------
    | Two-column Sections
    |--------------------------------------------------------------------------
    */

    .report-grid {
        display: grid;
        min-width: 0;
        gap: 20px;
    }

    .report-grid.two-columns {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    /*
    |--------------------------------------------------------------------------
    | Panel Header
    |--------------------------------------------------------------------------
    */

    .panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        min-height: 82px;
        padding: 19px 21px;
        border-bottom: 1px solid var(--report-soft-border);
        background:
            linear-gradient(
                180deg,
                #ffffff 0%,
                rgba(249, 250, 251, 0.68) 100%
            );
    }

    .panel-heading > div {
        min-width: 0;
    }

    .panel-heading h2 {
        margin: 0 0 5px;
        color: #111827;
        font-size: 16px;
        font-weight: 800;
        line-height: 1.3;
        letter-spacing: -0.02em;
    }

    .panel-heading p {
        margin: 0;
        color: #7b8494;
        font-size: 12px;
        line-height: 1.55;
    }

    .panel-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        min-height: 34px;
        padding: 8px 12px;
        border: 1px solid #dfe3e9;
        border-radius: 9px;
        background: #ffffff;
        color: #374151;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition:
            background-color 0.2s ease,
            border-color 0.2s ease,
            color 0.2s ease,
            transform 0.2s ease;
    }

    .panel-link:hover {
        transform: translateY(-1px);
        border-color: #c8ced7;
        background: #f9fafb;
        color: #111827;
        text-decoration: none;
    }

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    */

    .report-table-wrapper {
        width: 100%;
        min-width: 0;
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    .report-table-wrapper::-webkit-scrollbar {
        height: 8px;
    }

    .report-table-wrapper::-webkit-scrollbar-track {
        background: transparent;
    }

    .report-table-wrapper::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #cbd5e1;
    }

    .report-table {
        width: 100%;
        min-width: 540px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .recent-movements-table {
        min-width: 1120px;
    }

    .report-table th,
    .report-table td {
        padding: 15px 17px;
        border-bottom: 1px solid var(--report-soft-border);
        text-align: left;
        vertical-align: middle;
    }

    .report-table th {
        position: relative;
        background: #f8f9fb;
        color: #667085;
        font-size: 10px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: 0.065em;
        white-space: nowrap;
    }

    .report-table td {
        color: #4b5563;
        font-size: 12px;
        line-height: 1.55;
    }

    .report-table tbody tr {
        transition: background-color 0.18s ease;
    }

    .report-table tbody tr:hover {
        background: #fafbfc;
    }

    .report-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .product-link {
        color: #111827;
        font-size: 12px;
        font-weight: 800;
        line-height: 1.4;
        text-decoration: none;
    }

    .product-link:hover {
        color: var(--report-indigo);
        text-decoration: none;
    }

    .table-subtext,
    .date-time {
        display: block;
        margin-top: 4px;
        color: #98a2b3;
        font-size: 10px;
        line-height: 1.35;
    }

    .date-main {
        display: block;
        color: #344054;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    /*
    |--------------------------------------------------------------------------
    | Badges
    |--------------------------------------------------------------------------
    */

    .movement-badge,
    .stock-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 38px;
        min-height: 26px;
        padding: 5px 10px;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 850;
        line-height: 1;
        white-space: nowrap;
    }

    .movement-badge {
        border-color: #dfe3ff;
        background: var(--report-indigo-soft);
        color: #4338ca;
    }

    .stock-badge.low {
        border-color: #fed7aa;
        background: var(--report-orange-soft);
        color: var(--report-orange);
    }

    .stock-badge.out {
        border-color: #fecaca;
        background: var(--report-red-soft);
        color: var(--report-red);
    }

    .quantity-change {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 46px;
        min-height: 25px;
        padding: 4px 9px;
        border-radius: 8px;
        background: #f3f4f6;
        color: #4b5563;
        font-size: 11px;
        font-weight: 850;
    }

    .quantity-change.positive {
        background: var(--report-green-soft);
        color: var(--report-green);
    }

    .quantity-change.negative {
        background: var(--report-red-soft);
        color: var(--report-red);
    }

    .muted-text {
        color: #98a2b3;
        font-style: italic;
    }

    /*
    |--------------------------------------------------------------------------
    | Empty States
    |--------------------------------------------------------------------------
    */

    .empty-state {
        padding: 48px 24px !important;
        background: #ffffff;
        color: #98a2b3 !important;
        font-size: 12px !important;
        text-align: center !important;
    }

    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1250px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .report-filter-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .filter-actions {
            grid-column: 1 / -1;
            justify-content: flex-end;
        }
    }

    @media (max-width: 950px) {
        .report-grid.two-columns {
            grid-template-columns: minmax(0, 1fr);
        }

        .report-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .report-header-right {
            width: 100%;
            justify-content: space-between;
        }
    }

    @media (max-width: 700px) {
        .inventory-report-page {
            gap: 16px;
            padding: 0;
        }

        .report-header {
            padding: 22px 18px;
            border-radius: 15px;
        }

        .report-header h1 {
            font-size: 25px;
        }

        .report-header-right {
            align-items: stretch;
            flex-direction: column;
        }

        .report-period {
            width: 100%;
            white-space: normal;
            text-align: center;
        }

        .report-header-right .report-button {
            width: 100%;
        }

        .filter-panel {
            padding: 18px;
        }

        .report-filter-form,
        .summary-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .filter-actions {
            display: grid;
            grid-column: auto;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }

        .filter-actions .report-button {
            width: 100%;
        }

        .summary-card {
            min-height: 132px;
            border-radius: 15px;
        }

        .panel-heading {
            align-items: flex-start;
            flex-direction: column;
            min-height: auto;
            padding: 17px;
        }

        .panel-link {
            width: 100%;
        }

        .report-panel {
            border-radius: 15px;
        }
    }

    @media (max-width: 420px) {
        .filter-actions {
            grid-template-columns: 1fr;
        }

        .report-button {
            width: 100%;
        }

        .summary-card-value {
            font-size: 30px;
        }
    }
</style>
@endpush

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateRange = document.getElementById('date_range');
        const customDateFields = document.querySelectorAll(
            '[data-custom-date]'
        );

        if (!dateRange || !customDateFields.length) {
            return;
        }

        function updateCustomDateVisibility() {
            const isCustom = dateRange.value === 'custom';

            customDateFields.forEach(function(field) {
                field.style.display = isCustom ? 'flex' : 'none';

                const input = field.querySelector('input');

                if (input) {
                    input.disabled = !isCustom;
                }
            });
        }

        dateRange.addEventListener(
            'change',
            updateCustomDateVisibility
        );

        updateCustomDateVisibility();
    });
</script>
@endpush