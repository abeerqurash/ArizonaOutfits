@extends('admin.layouts.app')

@section('title', 'Reorder Dashboard')

@section('content')

@php
$totalItems = $reorderItems->count();

$configuredItems = $reorderItems
->filter(function ($item) {
return !$item['missing_reorder_point']
&& !$item['missing_reorder_quantity'];
})
->count();
@endphp

<div class="reorder-dashboard-page">

    {{-- Page Header --}}
    <section class="reorder-header">

        <div class="reorder-header-content">

            <div class="reorder-header-icon">
                <i class="fa-solid fa-cart-flatbed"></i>
            </div>

            <div>

                <span class="reorder-eyebrow">
                    Inventory planning
                </span>

                <h1>
                    Reorder Dashboard
                </h1>

                <p>
                    Review products and variants that require restocking,
                    calculate expected purchase costs and prioritise urgent
                    inventory orders.
                </p>

            </div>

        </div>

        <div class="reorder-header-actions">

            <a
                href="{{ route('admin.products.index') }}"
                class="reorder-button secondary">

                <i class="fa-solid fa-boxes-stacked"></i>

                Manage Products
            </a>

            <a
                href="{{ route('admin.stock-valuation.index') }}"
                class="reorder-button valuation">

                <i class="fa-solid fa-chart-column"></i>

                Stock Valuation
            </a>
            <a
                href="{{ route(
        'admin.reorder-dashboard.export.csv'
    ) }}"
                id="exportReorderCsvButton"
                data-export-url="{{ route(
        'admin.reorder-dashboard.export.csv'
    ) }}"
                class="reorder-button csv">

                <i class="fa-solid fa-file-csv"></i>

                Export CSV
            </a>
            <a
                href="{{ route(
        'admin.reorder-dashboard.export.excel'
    ) }}"
                id="exportReorderExcelButton"
                data-export-url="{{ route(
        'admin.reorder-dashboard.export.excel'
    ) }}"
                class="reorder-button excel">

                <i class="fa-solid fa-file-excel"></i>

                Export Excel
            </a>
            <button
                type="button"
                class="reorder-button primary"
                onclick="window.print()">

                <i class="fa-solid fa-print"></i>

                Print Report
            </button>


        </div>

    </section>

    {{-- Main Summary Cards --}}
    <section class="reorder-summary-grid">

        <article class="reorder-summary-card">

            <div class="summary-card-top">

                <div class="summary-icon reorder">
                    <i class="fa-solid fa-cart-arrow-down"></i>
                </div>

                <span class="summary-label">
                    Need Reordering
                </span>

            </div>

            <strong
                class="summary-value"
                id="summaryNeedsReorder">

                {{ number_format($productsNeedReordering) }}

            </strong>

            <span class="summary-description">
                Products and variants at or below their reorder point
            </span>

        </article>

        <article class="reorder-summary-card">

            <div class="summary-card-top">

                <div class="summary-icon out">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>

                <span class="summary-label">
                    Out of Stock
                </span>

            </div>

            <strong
                class="summary-value"
                id="summaryOutOfStock">

                {{ number_format($outOfStockCount) }}

            </strong>

            <span class="summary-description">
                Items requiring immediate attention
            </span>

        </article>

        <article class="reorder-summary-card">

            <div class="summary-card-top">

                <div class="summary-icon units">
                    <i class="fa-solid fa-cubes-stacked"></i>
                </div>

                <span class="summary-label">
                    Units to Purchase
                </span>

            </div>

            <strong
                class="summary-value"
                id="summarySuggestedUnits">

                {{ number_format($totalSuggestedUnits) }}

            </strong>

            <span class="summary-description">
                Total suggested reorder quantity
            </span>

        </article>

        <article class="reorder-summary-card">

            <div class="summary-card-top">

                <div class="summary-icon budget">
                    <i class="fa-solid fa-sterling-sign"></i>
                </div>

                <span class="summary-label">
                    Estimated Purchase Cost
                </span>

            </div>

            <strong
                class="summary-value"
                id="summaryEstimatedCost">

                £{{ number_format($estimatedPurchaseCost, 2) }}

            </strong>

            <span class="summary-description">
                Suggested quantity multiplied by product cost
            </span>

        </article>

    </section>

    {{-- Additional Statistics --}}
    <section class="reorder-mini-grid">

        <article class="reorder-mini-card">

            <span class="mini-icon low">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </span>

            <div>
                <span>Low Stock</span>

                <strong id="miniLowStock">
                    {{ number_format($lowStockCount) }}
                </strong>
            </div>

        </article>

        <article class="reorder-mini-card">

            <span class="mini-icon healthy">
                <i class="fa-solid fa-circle-check"></i>
            </span>

            <div>
                <span>Healthy Items</span>

                <strong id="miniHealthy">
                    {{ number_format($healthyCount) }}
                </strong>
            </div>

        </article>

        <article class="reorder-mini-card">

            <span class="mini-icon configured">
                <i class="fa-solid fa-sliders"></i>
            </span>

            <div>
                <span>Reorder Configured</span>

                <strong>
                    {{ number_format($configuredItems) }}
                    /
                    {{ number_format($totalItems) }}
                </strong>
            </div>

        </article>

        <article class="reorder-mini-card">

            <span class="mini-icon missing">
                <i class="fa-solid fa-circle-exclamation"></i>
            </span>

            <div>
                <span>Missing Cost</span>

                <strong id="miniMissingCost">
                    {{ number_format($missingCostCount) }}
                </strong>
            </div>

        </article>

    </section>

    {{-- Configuration Warnings --}}
    @if (
    $missingReorderPointCount > 0
    || $missingReorderQuantityCount > 0
    || $missingCostCount > 0
    )

    <section class="configuration-alert">

        <div class="configuration-alert-icon">
            <i class="fa-solid fa-screwdriver-wrench"></i>
        </div>

        <div class="configuration-alert-content">

            <span class="configuration-alert-eyebrow">
                Configuration required
            </span>

            <h2>
                Some inventory records need attention
            </h2>

            <p>
                Configure missing reorder values and product costs to
                improve the accuracy of purchase recommendations.
            </p>

        </div>

        <div class="configuration-alert-stats">

            <div>
                <strong>
                    {{ number_format($missingReorderPointCount) }}
                </strong>

                <span>
                    Missing reorder point
                </span>
            </div>

            <div>
                <strong>
                    {{ number_format($missingReorderQuantityCount) }}
                </strong>

                <span>
                    Missing reorder quantity
                </span>
            </div>

            <div>
                <strong>
                    {{ number_format($missingCostCount) }}
                </strong>

                <span>
                    Missing cost price
                </span>
            </div>

        </div>

    </section>

    @endif

    {{-- Priority Items --}}
    <section class="priority-panel">

        <div class="section-heading">

            <div>

                <span class="section-eyebrow">
                    Immediate attention
                </span>

                <h2>
                    Priority Reorder Items
                </h2>

                <p>
                    The most urgent inventory items based on stock availability.
                </p>

            </div>

        </div>

        <div class="priority-grid">

            @forelse ($urgentItems as $item)

            <article class="priority-card {{ $item['urgency'] }}">

                <div class="priority-card-top">

                    <span class="priority-badge {{ $item['urgency'] }}">
                        {{ $item['urgency_label'] }}
                    </span>

                    <span class="priority-stock">
                        {{ number_format($item['stock']) }}
                        in stock
                    </span>

                </div>

                <h3>
                    {{ $item['item_name'] }}
                </h3>

                <span class="priority-sku">
                    SKU:
                    {{ $item['sku'] ?: 'Not assigned' }}
                </span>

                <div class="priority-numbers">

                    <div>
                        <span>Reorder Point</span>

                        <strong>
                            {{ number_format($item['reorder_point']) }}
                        </strong>
                    </div>

                    <div>
                        <span>Suggested Qty</span>

                        <strong>
                            {{ number_format($item['suggested_quantity']) }}
                        </strong>
                    </div>

                    <div>
                        <span>Estimated Cost</span>

                        <strong>
                            £{{ number_format($item['estimated_cost'], 2) }}
                        </strong>
                    </div>

                </div>

                <a
                    href="{{ $item['edit_url'] }}"
                    class="priority-action">

                    Configure Product

                    <i class="fa-solid fa-arrow-right"></i>

                </a>

            </article>

            @empty

            <div class="empty-priority-state">

                <i class="fa-solid fa-circle-check"></i>

                <h3>
                    No urgent reorder items
                </h3>

                <p>
                    Your current inventory levels are above their configured
                    reorder points.
                </p>

            </div>

            @endforelse

        </div>

    </section>

    {{-- Charts --}}
    <section class="reorder-chart-grid">

        <article class="chart-card">

            <div class="chart-card-header">

                <div>
                    <span class="section-eyebrow">
                        Purchase quantities
                    </span>

                    <h2>
                        Highest Suggested Orders
                    </h2>
                </div>

                <span class="chart-card-icon purple">
                    <i class="fa-solid fa-chart-column"></i>
                </span>

            </div>

            <div class="chart-container">
                <canvas id="reorderQuantityChart"></canvas>
            </div>

        </article>

        <article class="chart-card">

            <div class="chart-card-header">

                <div>
                    <span class="section-eyebrow">
                        Inventory condition
                    </span>

                    <h2>
                        Stock Health Distribution
                    </h2>
                </div>

                <span class="chart-card-icon green">
                    <i class="fa-solid fa-chart-pie"></i>
                </span>

            </div>

            <div class="chart-container">
                <canvas id="stockHealthChart"></canvas>
            </div>

        </article>

        <article class="chart-card chart-card-full">

            <div class="chart-card-header">

                <div>
                    <span class="section-eyebrow">
                        Purchase planning
                    </span>

                    <h2>
                        Estimated Purchase Cost by Category
                    </h2>
                </div>

                <span class="chart-card-icon orange">
                    <i class="fa-solid fa-wallet"></i>
                </span>

            </div>

            <div class="chart-container category-chart-container">
                <canvas id="categoryPurchaseChart"></canvas>
            </div>

        </article>

    </section>

    <form
        method="POST"
        action="{{ route(
        'admin.purchase-orders.create-from-reorder'
    ) }}"
        id="generatePurchaseOrderForm">

        @csrf

        {{-- Reorder Table --}}
        <section class="reorder-panel">

            <div class="reorder-panel-header">

                <div>

                    <span class="section-eyebrow">
                        Purchase recommendations
                    </span>

                    <h2>
                        Inventory Reorder List
                    </h2>

                    <p>
                        Search, filter and review suggested inventory purchases.
                    </p>

                </div>

            </div>

            <div
                class="purchase-selection-toolbar"
                id="purchaseSelectionToolbar">

                <div class="purchase-selection-summary">

                    <span class="purchase-selection-icon">
                        <i class="fa-solid fa-list-check"></i>
                    </span>

                    <div>

                        <strong id="selectedReorderCount">
                            0
                        </strong>

                        <span>
                            reorder items selected
                        </span>

                    </div>

                </div>

                <button
                    type="submit"
                    class="generate-purchase-order-button"
                    id="generatePurchaseOrderButton"
                    disabled>

                    <i class="fa-solid fa-file-circle-plus"></i>

                    Generate Purchase Order
                </button>

            </div>

            <div class="reorder-toolbar">

                <div class="toolbar-search">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="search"
                        id="reorderSearch"
                        placeholder="Search product, variant or SKU..."
                        autocomplete="off">

                </div>

                <div class="toolbar-filters">

                    <select id="reorderStatusFilter">

                        <option value="">
                            All Stock Statuses
                        </option>

                        <option value="reorder">
                            Needs Reorder
                        </option>

                        <option value="healthy">
                            Healthy
                        </option>

                        <option value="low">
                            Low Stock
                        </option>

                        <option value="out">
                            Out of Stock
                        </option>

                    </select>

                    <select id="reorderUrgencyFilter">

                        <option value="">
                            All Urgency Levels
                        </option>

                        <option value="critical">
                            Critical
                        </option>

                        <option value="high">
                            High
                        </option>

                        <option value="medium">
                            Medium
                        </option>

                        <option value="normal">
                            Normal
                        </option>

                    </select>

                    <select id="reorderConfigurationFilter">

                        <option value="">
                            All Configuration
                        </option>

                        <option value="missing-point">
                            Missing Reorder Point
                        </option>

                        <option value="missing-quantity">
                            Missing Reorder Quantity
                        </option>

                        <option value="missing-cost">
                            Missing Cost
                        </option>

                    </select>

                    <select id="reorderSortFilter">

                        <option value="">
                            Sort Items
                        </option>

                        <option value="urgency">
                            Highest Urgency
                        </option>

                        <option value="cost">
                            Highest Purchase Cost
                        </option>

                        <option value="quantity">
                            Highest Suggested Quantity
                        </option>

                        <option value="stock">
                            Lowest Stock
                        </option>

                    </select>

                    <button
                        type="button"
                        class="toolbar-reset"
                        id="resetReorderFilters">

                        <i class="fa-solid fa-rotate-left"></i>

                        Reset

                    </button>

                </div>

            </div>

            <div class="reorder-table-wrapper">

                <table class="reorder-table">

                    <thead>

                        <tr>

                            <th class="selection-column">

                                <input
                                    type="checkbox"
                                    id="selectAllReorderItems"
                                    aria-label="Select all visible reorder items">

                            </th>

                            <th>Product / Variant</th>
                            <th>Stock Status</th>
                            <th class="number-column">Current Stock</th>
                            <th class="number-column">Reorder Point</th>
                            <th class="number-column">Reorder Qty</th>
                            <th class="number-column">Suggested Qty</th>
                            <th class="number-column">Cost Price</th>
                            <th class="number-column">Estimated Cost</th>
                            <th>Urgency</th>
                            <th class="action-column"></th>
                        </tr>

                    </thead>

                    <tbody id="reorderTableBody">

                        @forelse ($reorderItems as $item)

                        @php
                        $searchValue = strtolower(
                        trim(
                        $item['item_name']
                        . ' '
                        . ($item['sku'] ?? '')
                        )
                        );
                        @endphp

                        <tr
                            class="reorder-table-row"

                            data-search="{{ $searchValue }}"

                            data-status="{{ $item['stock_status'] }}"

                            data-needs-reorder="{{ $item['needs_reorder'] ? 'true' : 'false' }}"

                            data-urgency="{{ $item['urgency'] }}"

                            data-stock="{{ $item['stock'] }}"

                            data-suggested-quantity="{{ $item['suggested_quantity'] }}"

                            data-estimated-cost="{{ $item['estimated_cost'] }}"

                            data-missing-point="{{ $item['missing_reorder_point'] ? 'true' : 'false' }}"

                            data-missing-quantity="{{ $item['missing_reorder_quantity'] ? 'true' : 'false' }}"

                            data-missing-cost="{{ $item['missing_cost'] ? 'true' : 'false' }}">
                            <td class="selection-column">

                                @if ($item['needs_reorder'])

                                <input
                                    type="checkbox"
                                    class="reorder-item-checkbox"
                                    name="selected_items[]"
                                    value="{{
                $item['is_variant']
                    ? 'variant:' . $item['variant_id']
                    : 'product:' . $item['product_id']
            }}"
                                    aria-label="Select {{ $item['item_name'] }}">

                                @else

                                <input
                                    type="checkbox"
                                    disabled
                                    title="This item does not currently need reordering">

                                @endif

                            </td>
                            <td>

                                <div class="item-information">

                                    <div class="item-icon">

                                        @if ($item['is_variant'])

                                        <i class="fa-solid fa-layer-group"></i>

                                        @else

                                        <i class="fa-solid fa-box"></i>

                                        @endif

                                    </div>

                                    <div>

                                        <a href="{{ $item['edit_url'] }}">
                                            {{ $item['item_name'] }}
                                        </a>

                                        <span>
                                            SKU:
                                            {{ $item['sku'] ?: 'Not assigned' }}
                                        </span>

                                        @if ($item['is_variant'])

                                        <small>
                                            Product variant
                                        </small>

                                        @else

                                        <small>
                                            Simple product
                                        </small>

                                        @endif

                                    </div>

                                </div>

                            </td>

                            <td>

                                <div class="stock-status-group">

                                    <span
                                        class="stock-status-badge {{ $item['stock_status'] }}">

                                        <span class="stock-status-dot"></span>

                                        {{ $item['stock_status_label'] }}

                                    </span>

                                    @if ($item['needs_reorder'])

                                    <small class="needs-order-label">
                                        Reorder recommended
                                    </small>

                                    @endif

                                </div>

                            </td>

                            <td class="number-column">

                                <strong>
                                    {{ number_format($item['stock']) }}
                                </strong>

                            </td>

                            <td class="number-column">

                                @if ($item['missing_reorder_point'])

                                <span class="configuration-missing">
                                    Not set
                                </span>

                                @else

                                {{ number_format($item['reorder_point']) }}

                                @endif

                            </td>

                            <td class="number-column">

                                @if ($item['missing_reorder_quantity'])

                                <span class="configuration-missing">
                                    Not set
                                </span>

                                @else

                                {{ number_format($item['reorder_quantity']) }}

                                @endif

                            </td>

                            <td class="number-column">

                                <strong
                                    class="{{
                                        $item['suggested_quantity'] > 0
                                            ? 'suggested-positive'
                                            : ''
                                    }}">

                                    {{ number_format($item['suggested_quantity']) }}

                                </strong>

                            </td>

                            <td class="number-column">

                                @if ($item['missing_cost'])

                                <span class="configuration-missing">
                                    Not set
                                </span>

                                @else

                                £{{ number_format($item['cost_price'], 2) }}

                                @endif

                            </td>

                            <td class="number-column">

                                <strong class="estimated-cost-value">
                                    £{{ number_format($item['estimated_cost'], 2) }}
                                </strong>

                            </td>

                            <td>

                                <span
                                    class="urgency-badge {{ $item['urgency'] }}">

                                    {{ $item['urgency_label'] }}

                                </span>

                            </td>

                            <td class="action-column">

                                <a
                                    href="{{ $item['edit_url'] }}"
                                    class="row-action"
                                    title="Configure inventory">

                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>

                                </a>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td
                                colspan="11"
                                class="reorder-empty-state">

                                <div class="empty-state-icon">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>

                                <h3>
                                    No inventory items found
                                </h3>

                                <p>
                                    Add products or variants to begin generating
                                    reorder recommendations.
                                </p>

                            </td>

                        </tr>

                        @endforelse

                        <tr
                            id="reorderSearchEmpty"
                            style="display:none;">

                            <td
                                colspan="11"
                                class="reorder-empty-state">

                                <div class="empty-state-icon">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </div>

                                <h3>
                                    No matching inventory items
                                </h3>

                                <p>
                                    Try changing your search text or selected filters.
                                </p>

                            </td>

                        </tr>

                    </tbody>

                    @if ($reorderItems->isNotEmpty())

                    <tfoot>

                        <tr>

                            <td colspan="6">

                                <strong>
                                    Filtered Reorder Summary
                                </strong>

                                <span class="table-total-description">

                                    <span id="footerVisibleItems">
                                        {{ number_format($reorderItems->count()) }}
                                    </span>

                                    items shown in this report

                                </span>

                            </td>

                            <td class="number-column">

                                <strong id="footerSuggestedUnits">
                                    {{ number_format($totalSuggestedUnits) }}
                                </strong>

                            </td>

                            <td></td>

                            <td class="number-column">

                                <strong id="footerEstimatedCost">
                                    £{{ number_format($estimatedPurchaseCost, 2) }}
                                </strong>

                            </td>

                            <td colspan="2"></td>

                        </tr>

                    </tfoot>

                    @endif

                </table>

            </div>

            <div
                class="reorder-pagination"
                id="reorderPagination">

                <div class="pagination-summary">

                    Showing

                    <strong id="paginationFrom">
                        0
                    </strong>

                    –

                    <strong id="paginationTo">
                        0
                    </strong>

                    of

                    <strong id="paginationTotal">
                        {{ number_format($reorderItems->count()) }}
                    </strong>

                    items

                </div>

                <div class="pagination-actions">

                    <label for="reorderPageSize">
                        Rows per page
                    </label>

                    <select id="reorderPageSize">

                        <option value="10">
                            10
                        </option>

                        <option value="25">
                            25
                        </option>

                        <option value="50">
                            50
                        </option>

                        <option value="100">
                            100
                        </option>

                    </select>

                    <div
                        class="pagination-buttons"
                        id="paginationButtons">
                    </div>

                </div>

            </div>

        </section>
    </form>
</div>

@endsection

@push('page-styles')

<style>
    .reorder-dashboard-page {
        --reorder-text: #111827;
        --reorder-muted: #64748b;
        --reorder-border: #e5e7eb;
        --reorder-soft-border: #eef2f7;
        --reorder-primary: #111827;
        --reorder-indigo: #4f46e5;
        --reorder-indigo-soft: #eef2ff;
        --reorder-green: #15803d;
        --reorder-green-soft: #ecfdf3;
        --reorder-orange: #c2410c;
        --reorder-orange-soft: #fff7ed;
        --reorder-red: #b91c1c;
        --reorder-red-soft: #fef2f2;
        --reorder-blue: #0369a1;
        --reorder-blue-soft: #f0f9ff;

        display: flex;
        flex-direction: column;
        gap: 24px;
        min-width: 0;
        padding: 4px;
        color: var(--reorder-text);
    }

    .reorder-dashboard-page *,
    .reorder-dashboard-page *::before,
    .reorder-dashboard-page *::after {
        box-sizing: border-box;
    }

    .reorder-header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 28px 30px;
        border: 1px solid rgba(229, 231, 235, 0.85);
        border-radius: 20px;
        background:
            radial-gradient(circle at top right,
                rgba(79, 70, 229, 0.14),
                transparent 36%),
            linear-gradient(135deg,
                #ffffff 0%,
                #f8f9ff 100%);
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.04),
            0 12px 30px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .reorder-header::before {
        content: "";
        position: absolute;
        top: -70px;
        right: -50px;
        width: 210px;
        height: 210px;
        border: 32px solid rgba(79, 70, 229, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }

    .reorder-header-content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
    }

    .reorder-header-icon {
        width: 60px;
        height: 60px;
        flex: 0 0 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 17px;
        background: var(--reorder-indigo-soft);
        color: var(--reorder-indigo);
        font-size: 24px;
    }

    .reorder-eyebrow,
    .section-eyebrow,
    .configuration-alert-eyebrow {
        display: block;
        margin-bottom: 6px;
        color: var(--reorder-indigo);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .reorder-header h1 {
        margin: 0 0 8px;
        color: var(--reorder-text);
        font-size: 30px;
        line-height: 1.2;
    }

    .reorder-header p {
        max-width: 700px;
        margin: 0;
        color: var(--reorder-muted);
        font-size: 14px;
        line-height: 1.65;
    }

    .reorder-header-actions {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .reorder-button {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 10px 16px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 750;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
        transition: 0.2s ease;
    }

    .reorder-button:hover {
        transform: translateY(-1px);
    }

    .reorder-button.primary {
        background: var(--reorder-primary);
        color: #ffffff;
    }

    .reorder-button.secondary {
        border-color: var(--reorder-border);
        background: #ffffff;
        color: #374151;
    }

    .reorder-button.valuation {
        border-color: #c7d2fe;
        background: var(--reorder-indigo-soft);
        color: var(--reorder-indigo);
    }

    .reorder-button.valuation:hover {
        border-color: var(--reorder-indigo);
        background: var(--reorder-indigo);
        color: #ffffff;
    }

    .reorder-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .reorder-summary-card {
        position: relative;
        min-width: 0;
        padding: 21px;
        border: 1px solid var(--reorder-border);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .reorder-summary-card::after {
        content: "";
        position: absolute;
        right: -30px;
        bottom: -38px;
        width: 95px;
        height: 95px;
        border: 17px solid rgba(148, 163, 184, 0.08);
        border-radius: 50%;
    }

    .summary-card-top {
        display: flex;
        align-items: center;
        gap: 11px;
        margin-bottom: 18px;
    }

    .summary-icon {
        width: 41px;
        height: 41px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        font-size: 16px;
    }

    .summary-icon.reorder {
        background: var(--reorder-indigo-soft);
        color: var(--reorder-indigo);
    }

    .summary-icon.out {
        background: var(--reorder-red-soft);
        color: var(--reorder-red);
    }

    .summary-icon.units {
        background: var(--reorder-blue-soft);
        color: var(--reorder-blue);
    }

    .summary-icon.budget {
        background: var(--reorder-green-soft);
        color: var(--reorder-green);
    }

    .summary-label {
        color: var(--reorder-muted);
        font-size: 13px;
        font-weight: 700;
    }

    .summary-value {
        position: relative;
        z-index: 1;
        display: block;
        margin-bottom: 11px;
        color: var(--reorder-text);
        font-size: 28px;
        line-height: 1.2;
    }

    .summary-description {
        position: relative;
        z-index: 1;
        display: block;
        color: var(--reorder-muted);
        font-size: 11px;
        line-height: 1.5;
    }

    .reorder-mini-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .reorder-mini-card {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 16px 17px;
        border: 1px solid var(--reorder-border);
        border-radius: 13px;
        background: #ffffff;
    }

    .mini-icon {
        width: 39px;
        height: 39px;
        flex: 0 0 39px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .mini-icon.low {
        background: var(--reorder-orange-soft);
        color: var(--reorder-orange);
    }

    .mini-icon.healthy {
        background: var(--reorder-green-soft);
        color: var(--reorder-green);
    }

    .mini-icon.configured {
        background: var(--reorder-indigo-soft);
        color: var(--reorder-indigo);
    }

    .mini-icon.missing {
        background: var(--reorder-red-soft);
        color: var(--reorder-red);
    }

    .reorder-mini-card span {
        display: block;
        margin-bottom: 3px;
        color: var(--reorder-muted);
        font-size: 12px;
        font-weight: 650;
    }

    .reorder-mini-card strong {
        color: var(--reorder-text);
        font-size: 18px;
    }

    .configuration-alert {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 20px;
        padding: 22px 24px;
        border: 1px solid #fed7aa;
        border-radius: 17px;
        background:
            linear-gradient(135deg,
                #fffaf5 0%,
                #fff7ed 100%);
    }

    .configuration-alert-icon {
        width: 54px;
        height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 15px;
        background: #ffedd5;
        color: var(--reorder-orange);
        font-size: 21px;
    }

    .configuration-alert-content h2 {
        margin: 0 0 6px;
        font-size: 19px;
    }

    .configuration-alert-content p {
        margin: 0;
        color: #78716c;
        font-size: 12px;
        line-height: 1.6;
    }

    .configuration-alert-stats {
        display: flex;
        align-items: stretch;
        gap: 10px;
    }

    .configuration-alert-stats div {
        min-width: 125px;
        padding: 12px;
        border: 1px solid #fed7aa;
        border-radius: 11px;
        background: rgba(255, 255, 255, 0.75);
        text-align: center;
    }

    .configuration-alert-stats strong {
        display: block;
        margin-bottom: 4px;
        color: var(--reorder-orange);
        font-size: 18px;
    }

    .configuration-alert-stats span {
        display: block;
        color: #78716c;
        font-size: 10px;
        line-height: 1.4;
    }

    .priority-panel,
    .reorder-panel,
    .chart-card {
        border: 1px solid var(--reorder-border);
        border-radius: 17px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
    }

    .priority-panel {
        padding: 23px;
    }

    .section-heading {
        margin-bottom: 18px;
    }

    .section-heading h2,
    .chart-card-header h2,
    .reorder-panel-header h2 {
        margin: 0 0 6px;
        color: var(--reorder-text);
        font-size: 20px;
    }

    .section-heading p,
    .reorder-panel-header p {
        margin: 0;
        color: var(--reorder-muted);
        font-size: 12px;
        line-height: 1.6;
    }

    .priority-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 15px;
    }

    .priority-card {
        padding: 18px;
        border: 1px solid var(--reorder-border);
        border-left-width: 4px;
        border-radius: 13px;
        background: #ffffff;
    }

    .priority-card.critical {
        border-left-color: #dc2626;
    }

    .priority-card.high {
        border-left-color: #ea580c;
    }

    .priority-card.medium {
        border-left-color: #d97706;
    }

    .priority-card.normal {
        border-left-color: #16a34a;
    }

    .priority-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .priority-badge,
    .urgency-badge {
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
    }

    .priority-badge.critical,
    .urgency-badge.critical {
        background: #fee2e2;
        color: #b91c1c;
    }

    .priority-badge.high,
    .urgency-badge.high {
        background: #ffedd5;
        color: #c2410c;
    }

    .priority-badge.medium,
    .urgency-badge.medium {
        background: #fef3c7;
        color: #a16207;
    }

    .priority-badge.normal,
    .urgency-badge.normal {
        background: #dcfce7;
        color: #15803d;
    }

    .priority-stock {
        color: var(--reorder-muted);
        font-size: 11px;
        font-weight: 650;
    }

    .priority-card h3 {
        margin: 0 0 5px;
        font-size: 15px;
    }

    .priority-sku {
        display: block;
        color: var(--reorder-muted);
        font-size: 10px;
    }

    .priority-numbers {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin: 15px 0;
    }

    .priority-numbers div {
        padding: 10px;
        border-radius: 9px;
        background: #f8fafc;
    }

    .priority-numbers span {
        display: block;
        margin-bottom: 4px;
        color: var(--reorder-muted);
        font-size: 9px;
    }

    .priority-numbers strong {
        font-size: 13px;
    }

    .priority-action {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: var(--reorder-indigo);
        font-size: 11px;
        font-weight: 750;
        text-decoration: none;
    }

    .empty-priority-state {
        grid-column: 1 / -1;
        padding: 35px 20px;
        border: 1px dashed #bbf7d0;
        border-radius: 13px;
        background: #f0fdf4;
        text-align: center;
    }

    .empty-priority-state i {
        margin-bottom: 10px;
        color: var(--reorder-green);
        font-size: 28px;
    }

    .empty-priority-state h3 {
        margin: 0 0 6px;
    }

    .empty-priority-state p {
        margin: 0;
        color: var(--reorder-muted);
        font-size: 12px;
    }

    .reorder-chart-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .chart-card {
        min-width: 0;
        padding: 22px;
    }

    .chart-card-full {
        grid-column: 1 / -1;
    }

    .chart-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 18px;
    }

    .chart-card-header h2 {
        font-size: 17px;
    }

    .chart-card-icon {
        width: 43px;
        height: 43px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
    }

    .chart-card-icon.purple {
        background: var(--reorder-indigo-soft);
        color: var(--reorder-indigo);
    }

    .chart-card-icon.green {
        background: var(--reorder-green-soft);
        color: var(--reorder-green);
    }

    .chart-card-icon.orange {
        background: var(--reorder-orange-soft);
        color: var(--reorder-orange);
    }

    .chart-container {
        position: relative;
        height: 310px;
    }

    .category-chart-container {
        height: 360px;
    }

    .reorder-panel {
        overflow: hidden;
    }

    .reorder-panel-header {
        padding: 22px 24px;
        border-bottom: 1px solid var(--reorder-border);
    }

    .reorder-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 17px 24px;
        border-bottom: 1px solid var(--reorder-border);
        background: #f8fafc;
    }

    .toolbar-search {
        position: relative;
        width: min(100%, 330px);
        flex: 0 0 330px;
    }

    .toolbar-search i {
        position: absolute;
        top: 50%;
        left: 14px;
        color: #94a3b8;
        transform: translateY(-50%);
    }

    .toolbar-search input {
        width: 100%;
        height: 44px;
        padding: 0 14px 0 40px;
        border: 1px solid #d7dce5;
        border-radius: 10px;
        background: #ffffff;
        color: #111827;
        font-family: inherit;
        outline: none;
    }

    .toolbar-search input:focus {
        border-color: var(--reorder-indigo);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .toolbar-filters {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
        flex: 1;
    }

    .toolbar-filters select {
        min-width: 155px;
        height: 44px;
        padding: 0 32px 0 12px;
        border: 1px solid #d7dce5;
        border-radius: 10px;
        background: #ffffff;
        color: #374151;
        font-family: inherit;
        font-size: 12px;
        cursor: pointer;
        outline: none;
    }

    .toolbar-reset {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 14px;
        border: 1px solid #d7dce5;
        border-radius: 10px;
        background: #ffffff;
        color: #475569;
        font-family: inherit;
        font-size: 12px;
        font-weight: 750;
        cursor: pointer;
    }

    .reorder-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .reorder-table {
        width: 100%;
        min-width: 1380px;
        border-collapse: collapse;
    }

    .reorder-table th,
    .reorder-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--reorder-soft-border);
        vertical-align: middle;
    }

    .reorder-table th {
        background: #f8fafc;
        color: #64748b;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.055em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .reorder-table td {
        color: #374151;
        font-size: 12px;
    }

    .reorder-table tbody tr:hover {
        background: #fafbff;
    }

    .reorder-table .number-column {
        text-align: right;
        white-space: nowrap;
    }

    .reorder-table .action-column {
        width: 54px;
        text-align: right;
    }

    .reorder-table tfoot td {
        border-top: 1px solid var(--reorder-border);
        border-bottom: 0;
        background: #f8fafc;
    }

    .table-total-description {
        display: block;
        margin-top: 4px;
        color: var(--reorder-muted);
        font-size: 10px;
    }

    .item-information {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 250px;
    }

    .item-icon {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--reorder-border);
        border-radius: 10px;
        background: #f8fafc;
        color: #64748b;
    }

    .item-information a {
        display: block;
        max-width: 270px;
        margin-bottom: 3px;
        overflow: hidden;
        color: var(--reorder-text);
        font-weight: 750;
        text-decoration: none;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .item-information span,
    .item-information small {
        display: block;
        color: var(--reorder-muted);
        font-size: 10px;
    }

    .stock-status-group {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }

    .stock-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .stock-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .stock-status-badge.healthy {
        background: var(--reorder-green-soft);
        color: var(--reorder-green);
    }

    .stock-status-badge.low {
        background: var(--reorder-orange-soft);
        color: var(--reorder-orange);
    }

    .stock-status-badge.out {
        background: var(--reorder-red-soft);
        color: var(--reorder-red);
    }

    .needs-order-label {
        color: var(--reorder-indigo);
        font-size: 9px;
        font-weight: 700;
    }

    .configuration-missing {
        display: inline-flex;
        padding: 5px 7px;
        border-radius: 7px;
        background: var(--reorder-orange-soft);
        color: var(--reorder-orange);
        font-size: 9px;
        font-weight: 800;
    }

    .suggested-positive,
    .estimated-cost-value {
        color: var(--reorder-green);
    }

    .row-action {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--reorder-border);
        border-radius: 9px;
        background: #ffffff;
        color: #64748b;
        text-decoration: none;
    }

    .row-action:hover {
        border-color: var(--reorder-primary);
        background: var(--reorder-primary);
        color: #ffffff;
    }

    .reorder-empty-state {
        padding: 50px 20px !important;
        text-align: center;
    }

    .empty-state-icon {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 13px;
        border-radius: 15px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 20px;
    }

    .reorder-empty-state h3 {
        margin: 0 0 6px;
    }

    .reorder-empty-state p {
        margin: 0;
        color: var(--reorder-muted);
        font-size: 12px;
    }

    .reorder-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 17px 24px;
        border-top: 1px solid var(--reorder-border);
        background: #ffffff;
    }

    .pagination-summary {
        color: var(--reorder-muted);
        font-size: 11px;
    }

    .pagination-summary strong {
        color: var(--reorder-text);
    }

    .pagination-actions {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .pagination-actions label {
        color: var(--reorder-muted);
        font-size: 11px;
        font-weight: 700;
    }

    .pagination-actions select {
        width: 72px;
        height: 37px;
        padding: 0 9px;
        border: 1px solid #d7dce5;
        border-radius: 8px;
        background: #ffffff;
    }

    .pagination-buttons {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .pagination-button {
        min-width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        border: 1px solid #d7dce5;
        border-radius: 8px;
        background: #ffffff;
        color: #475569;
        font-family: inherit;
        font-size: 11px;
        font-weight: 750;
        cursor: pointer;
    }

    .pagination-button.active {
        border-color: var(--reorder-indigo);
        background: var(--reorder-indigo);
        color: #ffffff;
    }

    .pagination-button:disabled {
        background: #f8fafc;
        color: #cbd5e1;
        cursor: not-allowed;
    }

    .reorder-button.csv {
        border-color: #bbf7d0;
        background: #ecfdf3;
        color: #15803d;
    }

    .reorder-button.csv:hover {
        border-color: #15803d;
        background: #15803d;
        color: #ffffff;
    }

    .reorder-button.excel {
        border-color: #a7f3d0;
        background: #ecfdf5;
        color: #047857;
    }

    .reorder-button.excel:hover {
        border-color: #047857;
        background: #047857;
        color: #ffffff;
    }

    /*
|--------------------------------------------------------------------------
| Purchase-order selection
|--------------------------------------------------------------------------
*/

    .selection-column {
        width: 48px;
        min-width: 48px;
        text-align: center !important;
    }

    .selection-column input {
        width: 17px;
        height: 17px;
        accent-color: var(--reorder-indigo);
        cursor: pointer;
    }

    .selection-column input:disabled {
        cursor: not-allowed;
        opacity: 0.4;
    }

    .purchase-selection-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 14px 24px;
        border-bottom: 1px solid var(--reorder-border);
        background:
            linear-gradient(135deg,
                #f8faff 0%,
                #eef2ff 100%);
    }

    .purchase-selection-summary {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .purchase-selection-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #ffffff;
        color: var(--reorder-indigo);
    }

    .purchase-selection-summary strong {
        display: inline;
        color: var(--reorder-indigo);
        font-size: 17px;
    }

    .purchase-selection-summary span {
        color: #64748b;
        font-size: 11px;
        font-weight: 650;
    }

    .generate-purchase-order-button {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 16px;
        border: 1px solid var(--reorder-indigo);
        border-radius: 10px;
        background: var(--reorder-indigo);
        color: #ffffff;
        font-family: inherit;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .generate-purchase-order-button:hover:not(:disabled) {
        border-color: #4338ca;
        background: #4338ca;
        transform: translateY(-1px);
    }

    .generate-purchase-order-button:disabled {
        border-color: #d7dce5;
        background: #e2e8f0;
        color: #94a3b8;
        cursor: not-allowed;
    }

    @media (max-width: 1250px) {
        .reorder-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .reorder-header-actions {
            flex-wrap: wrap;
        }

        .reorder-summary-grid,
        .reorder-mini-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .configuration-alert {
            grid-template-columns: auto 1fr;
        }

        .configuration-alert-stats {
            grid-column: 1 / -1;
        }

        .reorder-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .toolbar-search {
            width: 100%;
            flex-basis: auto;
        }

        .toolbar-filters {
            flex-wrap: wrap;
        }
    }

    @media (max-width: 850px) {

        .priority-grid,
        .reorder-chart-grid {
            grid-template-columns: 1fr;
        }

        .chart-card-full {
            grid-column: auto;
        }

        .configuration-alert-stats {
            display: grid;
            grid-template-columns: 1fr;
            width: 100%;
        }

        .configuration-alert-stats div {
            min-width: 0;
        }
    }

    @media (max-width: 650px) {
        .reorder-header {
            padding: 22px 18px;
        }

        .reorder-header-content {
            align-items: flex-start;
        }

        .reorder-header-icon {
            width: 48px;
            height: 48px;
            flex-basis: 48px;
        }

        .reorder-header h1 {
            font-size: 24px;
        }

        .reorder-header-actions,
        .reorder-button {
            width: 100%;
        }

        .reorder-summary-grid,
        .reorder-mini-grid {
            grid-template-columns: 1fr;
        }

        .configuration-alert {
            grid-template-columns: 1fr;
        }

        .configuration-alert-icon {
            display: none;
        }

        .priority-numbers {
            grid-template-columns: 1fr;
        }

        .toolbar-filters,
        .toolbar-filters select,
        .toolbar-reset {
            width: 100%;
        }

        .reorder-pagination {
            align-items: stretch;
            flex-direction: column;
        }

        .pagination-actions {
            justify-content: center;
            flex-wrap: wrap;
        }

        .pagination-summary {
            text-align: center;
        }

        .purchase-selection-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .generate-purchase-order-button {
            width: 100%;
        }
    }

    @media print {

        .reorder-header-actions,
        .reorder-toolbar,
        .reorder-pagination,
        .row-action,
        .purchase-selection-toolbar,
        .selection-column {
            display: none !important;
        }

        .reorder-dashboard-page {
            gap: 14px;
        }

        .reorder-header,
        .reorder-summary-card,
        .reorder-mini-card,
        .configuration-alert,
        .priority-panel,
        .chart-card,
        .reorder-panel {
            box-shadow: none !important;
        }

        .reorder-table {
            min-width: 0;
        }

        .reorder-table th,
        .reorder-table td {
            padding: 6px;
            font-size: 8px;
        }
    }
</style>

@endpush

@push('page-scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function() {
            'use strict';

            const tableBody = document.getElementById(
                'reorderTableBody'
            );

            const rows = Array.from(
                document.querySelectorAll(
                    '.reorder-table-row'
                )
            );

            const searchInput = document.getElementById(
                'reorderSearch'
            );

            const statusFilter = document.getElementById(
                'reorderStatusFilter'
            );

            const urgencyFilter = document.getElementById(
                'reorderUrgencyFilter'
            );

            const configurationFilter =
                document.getElementById(
                    'reorderConfigurationFilter'
                );

            const sortFilter = document.getElementById(
                'reorderSortFilter'
            );

            const resetButton = document.getElementById(
                'resetReorderFilters'
            );

            const emptyRow = document.getElementById(
                'reorderSearchEmpty'
            );

            const pageSizeSelect = document.getElementById(
                'reorderPageSize'
            );

            const paginationButtons =
                document.getElementById(
                    'paginationButtons'
                );

            const paginationFrom =
                document.getElementById(
                    'paginationFrom'
                );

            const paginationTo =
                document.getElementById(
                    'paginationTo'
                );

            const paginationTotal =
                document.getElementById(
                    'paginationTotal'
                );

            const exportCsvButton = document.getElementById(
                'exportReorderCsvButton'
            );

            const exportExcelButton = document.getElementById(
                'exportReorderExcelButton'
            );

            const selectAllCheckbox = document.getElementById(
                'selectAllReorderItems'
            );

            const itemCheckboxes = Array.from(
                document.querySelectorAll(
                    '.reorder-item-checkbox'
                )
            );

            const selectedReorderCount =
                document.getElementById(
                    'selectedReorderCount'
                );

            const generatePurchaseOrderButton =
                document.getElementById(
                    'generatePurchaseOrderButton'
                );

            const generatePurchaseOrderForm =
                document.getElementById(
                    'generatePurchaseOrderForm'
                );

            let currentPage = 1;

            function numberValue(value) {
                const parsed = Number.parseFloat(value);

                return Number.isFinite(parsed) ?
                    parsed :
                    0;
            }

            function formatCurrency(value) {
                return new Intl.NumberFormat(
                    'en-GB', {
                        style: 'currency',
                        currency: 'GBP'
                    }
                ).format(value);
            }

            /*
|--------------------------------------------------------------------------
| Build filtered export URLs
|--------------------------------------------------------------------------
*/

            function buildExportUrl(button) {
                if (!button) {
                    return '';
                }

                const baseUrl =
                    button.dataset.exportUrl ||
                    button.href;

                const url = new URL(
                    baseUrl,
                    window.location.origin
                );

                [
                    'search',
                    'status',
                    'urgency',
                    'configuration',
                    'sort'
                ].forEach(function(parameter) {
                    url.searchParams.delete(parameter);
                });

                const values = {
                    search: searchInput.value.trim(),
                    status: statusFilter.value,
                    urgency: urgencyFilter.value,
                    configuration: configurationFilter.value,
                    sort: sortFilter.value
                };

                Object.entries(values).forEach(
                    function([key, value]) {
                        if (value !== '') {
                            url.searchParams.set(
                                key,
                                value
                            );
                        }
                    }
                );

                return url.toString();
            }

            function updateExportLinks() {
                if (exportCsvButton) {
                    exportCsvButton.href =
                        buildExportUrl(exportCsvButton);
                }

                if (exportExcelButton) {
                    exportExcelButton.href =
                        buildExportUrl(exportExcelButton);
                }
            }

            function getFilteredRows() {
                return rows.filter(function(row) {
                    return (
                        row.dataset.filterMatch ===
                        'true'
                    );
                });
            }

            /*
|--------------------------------------------------------------------------
| Return selectable checkboxes from visible filtered rows
|--------------------------------------------------------------------------
*/

            function getVisibleSelectableCheckboxes() {
                return getFilteredRows()
                    .filter(function(row) {
                        return row.style.display !== 'none';
                    })
                    .map(function(row) {
                        return row.querySelector(
                            '.reorder-item-checkbox'
                        );
                    })
                    .filter(Boolean);
            }

            /*
            |--------------------------------------------------------------------------
            | Update selected-item counter and button
            |--------------------------------------------------------------------------
            */

            function updatePurchaseSelection() {
                const selectedItems =
                    itemCheckboxes.filter(
                        function(checkbox) {
                            return checkbox.checked;
                        }
                    );

                if (selectedReorderCount) {
                    selectedReorderCount.textContent =
                        selectedItems.length.toLocaleString(
                            'en-GB'
                        );
                }

                if (generatePurchaseOrderButton) {
                    generatePurchaseOrderButton.disabled =
                        selectedItems.length === 0;
                }

                if (selectAllCheckbox) {
                    const visibleCheckboxes =
                        getVisibleSelectableCheckboxes();

                    const selectedVisible =
                        visibleCheckboxes.filter(
                            function(checkbox) {
                                return checkbox.checked;
                            }
                        );

                    selectAllCheckbox.checked =
                        visibleCheckboxes.length > 0 &&
                        selectedVisible.length ===
                        visibleCheckboxes.length;

                    selectAllCheckbox.indeterminate =
                        selectedVisible.length > 0 &&
                        selectedVisible.length <
                        visibleCheckboxes.length;
                }
            }

            function sortRows() {
                if (!tableBody) {
                    return;
                }

                const selectedSort =
                    sortFilter.value;

                const sortedRows = [...rows];

                const urgencyOrder = {
                    critical: 4,
                    high: 3,
                    medium: 2,
                    normal: 1
                };

                sortedRows.sort(
                    function(firstRow, secondRow) {
                        if (selectedSort === 'urgency') {
                            return (
                                urgencyOrder[
                                    secondRow.dataset.urgency
                                ] || 0
                            ) - (
                                urgencyOrder[
                                    firstRow.dataset.urgency
                                ] || 0
                            );
                        }

                        if (selectedSort === 'cost') {
                            return numberValue(
                                secondRow.dataset
                                .estimatedCost
                            ) - numberValue(
                                firstRow.dataset
                                .estimatedCost
                            );
                        }

                        if (
                            selectedSort ===
                            'quantity'
                        ) {
                            return numberValue(
                                secondRow.dataset
                                .suggestedQuantity
                            ) - numberValue(
                                firstRow.dataset
                                .suggestedQuantity
                            );
                        }

                        if (selectedSort === 'stock') {
                            return numberValue(
                                firstRow.dataset.stock
                            ) - numberValue(
                                secondRow.dataset.stock
                            );
                        }

                        return (
                            firstRow.dataset.search || ''
                        ).localeCompare(
                            secondRow.dataset.search || ''
                        );
                    }
                );

                sortedRows.forEach(function(row) {
                    tableBody.appendChild(row);
                });

                if (emptyRow) {
                    tableBody.appendChild(emptyRow);
                }
            }

            function updateSummary() {
                const filteredRows =
                    getFilteredRows();

                let needsReorder = 0;
                let outOfStock = 0;
                let lowStock = 0;
                let healthy = 0;
                let suggestedUnits = 0;
                let estimatedCost = 0;
                let missingCost = 0;

                filteredRows.forEach(function(row) {
                    if (
                        row.dataset.needsReorder ===
                        'true'
                    ) {
                        needsReorder++;
                    }

                    if (
                        row.dataset.status === 'out'
                    ) {
                        outOfStock++;
                    }

                    if (
                        row.dataset.status === 'low'
                    ) {
                        lowStock++;
                    }

                    if (
                        row.dataset.status ===
                        'healthy'
                    ) {
                        healthy++;
                    }

                    if (
                        row.dataset.missingCost ===
                        'true'
                    ) {
                        missingCost++;
                    }

                    suggestedUnits += numberValue(
                        row.dataset.suggestedQuantity
                    );

                    estimatedCost += numberValue(
                        row.dataset.estimatedCost
                    );
                });

                const values = {
                    summaryNeedsReorder: needsReorder,

                    summaryOutOfStock: outOfStock,

                    summarySuggestedUnits: suggestedUnits,

                    miniLowStock: lowStock,

                    miniHealthy: healthy,

                    miniMissingCost: missingCost,

                    footerVisibleItems: filteredRows.length,

                    footerSuggestedUnits: suggestedUnits
                };

                Object.entries(values).forEach(
                    function([id, value]) {
                        const element =
                            document.getElementById(id);

                        if (element) {
                            element.textContent =
                                Number(value)
                                .toLocaleString(
                                    'en-GB'
                                );
                        }
                    }
                );

                const costElements = [
                    'summaryEstimatedCost',
                    'footerEstimatedCost'
                ];

                costElements.forEach(function(id) {
                    const element =
                        document.getElementById(id);

                    if (element) {
                        element.textContent =
                            formatCurrency(
                                estimatedCost
                            );
                    }
                });
            }

            function createPageButton(
                label,
                page,
                options = {}
            ) {
                const button =
                    document.createElement('button');

                button.type = 'button';
                button.className =
                    'pagination-button';

                button.textContent = label;
                button.disabled =
                    Boolean(options.disabled);

                if (options.active) {
                    button.classList.add('active');
                }

                button.addEventListener(
                    'click',
                    function() {
                        if (button.disabled) {
                            return;
                        }

                        currentPage = page;
                        applyPagination();
                    }
                );

                return button;
            }

            function renderPagination(
                totalPages
            ) {
                if (!paginationButtons) {
                    return;
                }

                paginationButtons.innerHTML = '';

                paginationButtons.appendChild(
                    createPageButton(
                        'Previous',
                        Math.max(
                            1,
                            currentPage - 1
                        ), {
                            disabled: currentPage <= 1
                        }
                    )
                );

                for (
                    let page = 1; page <= totalPages; page++
                ) {
                    paginationButtons.appendChild(
                        createPageButton(
                            String(page),
                            page, {
                                active: page ===
                                    currentPage
                            }
                        )
                    );
                }

                paginationButtons.appendChild(
                    createPageButton(
                        'Next',
                        Math.min(
                            totalPages,
                            currentPage + 1
                        ), {
                            disabled: totalPages === 0 ||
                                currentPage >=
                                totalPages
                        }
                    )
                );
            }

            function applyPagination() {
                const filteredRows =
                    getFilteredRows();

                const pageSize = Math.max(
                    1,
                    numberValue(
                        pageSizeSelect ?
                        pageSizeSelect.value :
                        10
                    )
                );

                const totalRows =
                    filteredRows.length;

                const totalPages = Math.max(
                    1,
                    Math.ceil(
                        totalRows / pageSize
                    )
                );

                if (currentPage > totalPages) {
                    currentPage = totalPages;
                }

                const start =
                    (currentPage - 1) *
                    pageSize;

                const end = Math.min(
                    start + pageSize,
                    totalRows
                );

                rows.forEach(function(row) {
                    row.style.display = 'none';
                });

                filteredRows
                    .slice(start, end)
                    .forEach(function(row) {
                        row.style.display = '';
                    });

                if (paginationFrom) {
                    paginationFrom.textContent =
                        totalRows === 0 ?
                        '0' :
                        String(start + 1);
                }

                if (paginationTo) {
                    paginationTo.textContent =
                        String(end);
                }

                if (paginationTotal) {
                    paginationTotal.textContent =
                        totalRows.toLocaleString(
                            'en-GB'
                        );
                }

                if (emptyRow) {
                    emptyRow.style.display =
                        totalRows === 0 &&
                        rows.length > 0 ?
                        '' :
                        'none';
                }

                renderPagination(
                    totalRows === 0 ?
                    0 :
                    totalPages
                );
                updatePurchaseSelection();
            }

            function filterRows(
                resetPage = true
            ) {
                const keyword =
                    searchInput.value
                    .trim()
                    .toLowerCase();

                const selectedStatus =
                    statusFilter.value;

                const selectedUrgency =
                    urgencyFilter.value;

                const selectedConfiguration =
                    configurationFilter.value;

                rows.forEach(function(row) {
                    const searchableText =
                        (
                            row.dataset.search || ''
                        ).toLowerCase();

                    let matchesStatus = true;

                    if (
                        selectedStatus === 'reorder'
                    ) {
                        matchesStatus =
                            row.dataset.needsReorder ===
                            'true';
                    } else if (
                        selectedStatus !== ''
                    ) {
                        matchesStatus =
                            row.dataset.status ===
                            selectedStatus;
                    }

                    const matchesUrgency =
                        selectedUrgency === '' ||
                        row.dataset.urgency ===
                        selectedUrgency;

                    let matchesConfiguration =
                        true;

                    if (
                        selectedConfiguration ===
                        'missing-point'
                    ) {
                        matchesConfiguration =
                            row.dataset.missingPoint ===
                            'true';
                    }

                    if (
                        selectedConfiguration ===
                        'missing-quantity'
                    ) {
                        matchesConfiguration =
                            row.dataset.missingQuantity ===
                            'true';
                    }

                    if (
                        selectedConfiguration ===
                        'missing-cost'
                    ) {
                        matchesConfiguration =
                            row.dataset.missingCost ===
                            'true';
                    }

                    const matchesSearch =
                        keyword === '' ||
                        searchableText.includes(
                            keyword
                        );

                    row.dataset.filterMatch =
                        matchesSearch &&
                        matchesStatus &&
                        matchesUrgency &&
                        matchesConfiguration ?
                        'true' :
                        'false';
                });



                if (resetPage) {
                    currentPage = 1;
                }

                sortRows();
                updateSummary();
                updateExportLinks();
                applyPagination();
            }

            [
                searchInput,
                statusFilter,
                urgencyFilter,
                configurationFilter,
                sortFilter
            ].forEach(function(element) {
                if (!element) {
                    return;
                }

                element.addEventListener(
                    element.tagName === 'INPUT' ?
                    'input' :
                    'change',
                    function() {
                        filterRows(true);
                    }
                );
            });

            [
                exportCsvButton,
                exportExcelButton
            ].forEach(function(button) {
                if (!button) {
                    return;
                }

                button.addEventListener(
                    'click',
                    function() {
                        button.href =
                            buildExportUrl(button);
                    }
                );
            });

            if (pageSizeSelect) {
                pageSizeSelect.addEventListener(
                    'change',
                    function() {
                        currentPage = 1;
                        applyPagination();
                    }
                );
            }

            if (resetButton) {
                resetButton.addEventListener(
                    'click',
                    function() {
                        searchInput.value = '';
                        statusFilter.value = '';
                        urgencyFilter.value = '';
                        configurationFilter.value =
                            '';
                        sortFilter.value = '';

                        if (pageSizeSelect) {
                            pageSizeSelect.value =
                                '10';
                        }

                        itemCheckboxes.forEach(function(checkbox) {
                            checkbox.checked = false;
                        });

                        if (selectAllCheckbox) {
                            selectAllCheckbox.checked = false;
                            selectAllCheckbox.indeterminate = false;
                        }

                        currentPage = 1;
                        filterRows(true);
                        searchInput.focus();
                    }
                );
            }

            itemCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener(
                    'change',
                    updatePurchaseSelection
                );
            });

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener(
                    'change',
                    function() {
                        getVisibleSelectableCheckboxes()
                            .forEach(function(checkbox) {
                                checkbox.checked =
                                    selectAllCheckbox.checked;
                            });

                        updatePurchaseSelection();
                    }
                );
            }

            if (generatePurchaseOrderForm) {
                generatePurchaseOrderForm.addEventListener(
                    'submit',
                    function(event) {
                        const selectedCount =
                            itemCheckboxes.filter(
                                function(checkbox) {
                                    return checkbox.checked;
                                }
                            ).length;

                        if (selectedCount === 0) {
                            event.preventDefault();

                            window.alert(
                                'Please select at least one reorder item.'
                            );
                        }
                    }
                );
            }

            filterRows(true);
            updatePurchaseSelection();

            if (typeof Chart !== 'undefined') {
                const quantityCanvas =
                    document.getElementById(
                        'reorderQuantityChart'
                    );

                const stockCanvas =
                    document.getElementById(
                        'stockHealthChart'
                    );

                const categoryCanvas =
                    document.getElementById(
                        'categoryPurchaseChart'
                    );

                if (quantityCanvas) {
                    new Chart(
                        quantityCanvas, {
                            type: 'bar',

                            data: {
                                labels: @json($reorderChartLabels),

                                datasets: [{
                                    label: 'Suggested Quantity',

                                    data: @json($reorderChartData)
                                }]
                            },

                            options: {
                                responsive: true,
                                maintainAspectRatio: false,

                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                },

                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        }
                    );
                }

                if (stockCanvas) {
                    new Chart(
                        stockCanvas, {
                            type: 'doughnut',

                            data: {
                                labels: @json(
                                    array_keys(
                                        $stockHealthDistribution
                                    )
                                ),

                                datasets: [{
                                    data: @json(
                                        array_values(
                                            $stockHealthDistribution
                                        )
                                    )
                                }]
                            },

                            options: {
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        }
                    );
                }

                if (categoryCanvas) {
                    new Chart(
                        categoryCanvas, {
                            type: 'bar',

                            data: {
                                labels: @json(
                                    array_keys(
                                        $categoryPurchaseCosts
                                    )
                                ),

                                datasets: [{
                                    label: 'Purchase Cost',

                                    data: @json(
                                        array_values(
                                            $categoryPurchaseCosts
                                        )
                                    )
                                }]
                            },

                            options: {
                                responsive: true,
                                maintainAspectRatio: false,

                                indexAxis: 'y',

                                plugins: {
                                    legend: {
                                        display: false
                                    },

                                    tooltip: {
                                        callbacks: {
                                            label: function(
                                                context
                                            ) {
                                                return formatCurrency(
                                                    context.raw
                                                );
                                            }
                                        }
                                    }
                                },

                                scales: {
                                    x: {
                                        beginAtZero: true,

                                        ticks: {
                                            callback: function(
                                                value
                                            ) {
                                                return formatCurrency(
                                                    value
                                                );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    );
                }
            }
        }
    );
</script>

@endpush