@extends('admin.layouts.app')

@section('title', 'Stock Valuation')

@section('content')

@php
$averageMargin = $totalRetail > 0
? ($totalProfit / $totalRetail) * 100
: 0;

$lowStockCount = $products
->filter(function ($product) {
return (int) $product->stock > 0
&& (int) $product->stock <= 5;
    })
    ->count();

    $outOfStockCount = $products
    ->filter(function ($product) {
    return (int) $product->stock <= 0;
        })
        ->count();

        $productsWithCost = $products
        ->filter(function ($product) {
        return (float) $product->cost_price > 0;
        })
        ->count();
        @endphp

        <div class="stock-valuation-page">

            {{-- Page Header --}}
            <section class="valuation-header">

                <div class="valuation-header-content">

                    <div class="valuation-header-icon">
                        <i class="fa-solid fa-chart-column"></i>
                    </div>

                    <div>
                        <span class="valuation-eyebrow">
                            Inventory analytics
                        </span>

                        <h1>
                            Stock Valuation
                        </h1>

                        <p>
                            Review your current inventory cost, potential retail value,
                            expected profit and product-level stock performance.
                        </p>
                    </div>

                </div>

                <div class="valuation-header-actions">

                    <a
                        href="{{ route('admin.products.index') }}"
                        class="valuation-button secondary">

                        <i class="fa-solid fa-boxes-stacked"></i>

                        Manage Products
                    </a>

                    <a
                        href="{{ route(
        'admin.stock-valuation.export.csv'
    ) }}"
                        id="exportCsvButton"
                        data-export-url="{{ route(
        'admin.stock-valuation.export.csv'
    ) }}"
                        class="valuation-button export">

                        <i class="fa-solid fa-file-csv"></i>

                        Export CSV
                    </a>

                    <a
                        href="{{ route(
        'admin.stock-valuation.export.excel'
    ) }}"
                        id="exportExcelButton"
                        data-export-url="{{ route(
        'admin.stock-valuation.export.excel'
    ) }}"
                        class="valuation-button export excel">

                        <i class="fa-solid fa-file-excel"></i>

                        Export Excel
                    </a>

                    <a
                        href="{{ route(
        'admin.stock-valuation.export.pdf'
    ) }}"
                        id="exportPdfButton"
                        data-export-url="{{ route(
        'admin.stock-valuation.export.pdf'
    ) }}"
                        class="valuation-button pdf">

                        <i class="fa-solid fa-file-pdf"></i>

                        Export PDF
                    </a>

                    <button
                        type="button"
                        class="valuation-button primary"
                        onclick="window.print()">

                        <i class="fa-solid fa-print"></i>

                        Print Report
                    </button>

                </div>

            </section>

            {{-- Main Summary Cards --}}
            <section class="valuation-summary-grid">

                <article class="valuation-summary-card">

                    <div class="summary-card-top">

                        <div class="summary-icon cost">
                            <i class="fa-solid fa-coins"></i>
                        </div>

                        <span class="summary-label">
                            Inventory Cost
                        </span>

                    </div>

                    <strong
                        class="summary-value"
                        id="totalCostCard">

                        £{{ number_format($totalCost,2) }}

                    </strong>

                    <div class="summary-footer">
                        <span>
                            Cost price × available stock
                        </span>
                    </div>

                </article>

                <article class="valuation-summary-card">

                    <div class="summary-card-top">

                        <div class="summary-icon retail">
                            <i class="fa-solid fa-tags"></i>
                        </div>

                        <span class="summary-label">
                            Retail Value
                        </span>

                    </div>

                    <strong
                        class="summary-value"
                        id="totalRetailCard">

                        £{{ number_format($totalRetail,2) }}

                    </strong>

                    <div class="summary-footer">
                        <span>
                            Potential value at regular price
                        </span>
                    </div>

                </article>

                <article class="valuation-summary-card">

                    <div class="summary-card-top">

                        <div class="summary-icon profit">
                            <i class="fa-solid fa-arrow-trend-up"></i>
                        </div>

                        <span class="summary-label">
                            Expected Profit
                        </span>

                    </div>

                    <strong
                        class="summary-value"
                        id="totalProfitCard">

                        £{{ number_format($totalProfit,2) }}

                    </strong>

                    <div class="summary-footer">

                        <span class="summary-positive">
                            <i class="fa-solid fa-chart-line"></i>

                            {{ number_format($averageMargin, 1) }}% margin
                        </span>

                    </div>

                </article>

                <article class="valuation-summary-card">

                    <div class="summary-card-top">

                        <div class="summary-icon units">
                            <i class="fa-solid fa-cubes"></i>
                        </div>

                        <span class="summary-label">
                            Total Units
                        </span>

                    </div>

                    <strong
                        class="summary-value"
                        id="totalUnitsCard">

                        {{ number_format($totalUnits) }}

                    </strong>

                    <div class="summary-footer">
                        <span>
                            Across
                            <span id="summaryProducts">
                                {{ number_format($products->count()) }}
                            </span>
                            products
                        </span>
                    </div>

                </article>

            </section>

            <section class="inventory-health">

                <div class="health-left">

                    <div class="health-header">

                        <div class="health-icon">
                            <i class="fa-solid fa-heart-pulse"></i>
                        </div>

                        <div>

                            <span>
                                Inventory Health
                            </span>

                            <h2>
                                Overall Stock Health
                            </h2>

                        </div>

                    </div>

                    <div class="health-progress">

                        <div
                            id="healthProgress"
                            class="health-progress-bar"
                            style="width:{{ $healthScore }}%;">

                        </div>

                    </div>

                    <div
                        class="health-score"
                        id="healthScore">

                        {{ $healthScore }}%

                    </div>

                </div>

                <div class="health-right">

                    <div class="health-item success">

                        <i class="fa-solid fa-circle-check"></i>

                        <div>

                            <strong id="healthyProducts">

                                {{ $healthyProducts }}

                            </strong>
                            <span>

                                Healthy Products

                            </span>

                        </div>

                    </div>

                    <div class="health-item warning">

                        <i class="fa-solid fa-triangle-exclamation"></i>

                        <div>

                            <strong id="lowStockProducts">
                                {{ $lowStockProducts }}
                            </strong>

                            <span>

                                Low Stock

                            </span>

                        </div>

                    </div>

                    <div class="health-item danger">

                        <i class="fa-solid fa-ban"></i>

                        <div>

                            <strong id="outStockProducts">
                                {{ $outOfStockProducts }}
                            </strong>

                            <span>

                                Out of Stock

                            </span>

                        </div>

                    </div>

                    <div class="health-item orange">

                        <i class="fa-solid fa-coins"></i>

                        <div>

                            <strong id="missingCostProducts">
                                {{ $missingCostProducts }}
                            </strong>

                            <span>

                                Missing Cost

                            </span>

                        </div>

                    </div>

                    <div class="health-item red">

                        <i class="fa-solid fa-arrow-trend-down"></i>

                        <div>

                            <strong id="negativeMarginProducts">
                                {{ $negativeMarginProducts }}
                            </strong>

                            <span>

                                Negative Margin

                            </span>

                        </div>

                    </div>

                </div>

            </section>

            <section class="analytics-grid">

                @include(
                'admin.stock-valuation.partials.analytics-card',
                [
                'title'=>'Highest Inventory Value',
                'icon'=>'fa-coins',
                'items'=>$highestInventoryValue,
                'field'=>'inventory_retail',
                'colour'=>'blue'
                ]
                )

                @include(
                'admin.stock-valuation.partials.analytics-card',
                [
                'title'=>'Highest Expected Profit',
                'icon'=>'fa-chart-line',
                'items'=>$highestProfitProducts,
                'field'=>'inventory_profit',
                'colour'=>'green'
                ]
                )

                @include(
                'admin.stock-valuation.partials.analytics-card',
                [
                'title'=>'Largest Investment',
                'icon'=>'fa-wallet',
                'items'=>$highestInvestmentProducts,
                'field'=>'inventory_cost',
                'colour'=>'orange'
                ]
                )

                @include(
                'admin.stock-valuation.partials.analytics-card',
                [
                'title'=>'Lowest Margin',
                'icon'=>'fa-arrow-trend-down',
                'items'=>$lowestMarginProducts,
                'field'=>'margin',
                'colour'=>'red'
                ]
                )

            </section>
            <section class="analytics-dashboard">

                <div class="chart-card">
                    <h3>Inventory Value</h3>

                    <canvas id="inventoryValueChart"></canvas>
                </div>

                <div class="chart-card">
                    <h3>Expected Profit</h3>

                    <canvas id="profitChart"></canvas>
                </div>

                <div class="chart-card">
                    <h3>Stock Distribution</h3>

                    <canvas id="stockChart"></canvas>
                </div>

                <div class="chart-card">
                    <h3>Margin Distribution</h3>

                    <canvas id="marginChart"></canvas>
                </div>

                <div class="chart-card chart-full">

                    <h3>Inventory Value by Category</h3>

                    <canvas id="categoryChart"></canvas>

                </div>

            </section>
            {{-- Additional Statistics --}}
            <section class="valuation-mini-grid">

                <article class="valuation-mini-card">

                    <div class="mini-card-icon configured">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>

                    <div>
                        <span>
                            Cost Configured
                        </span>

                        <strong>
                            {{ number_format($productsWithCost) }}
                        </strong>
                    </div>

                </article>

                <article class="valuation-mini-card">

                    <div class="mini-card-icon low-stock">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>

                    <div>
                        <span>
                            Low Stock
                        </span>

                        <strong>
                            {{ number_format($lowStockCount) }}
                        </strong>
                    </div>

                </article>

                <article class="valuation-mini-card">

                    <div class="mini-card-icon out-stock">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </div>

                    <div>
                        <span>
                            Out of Stock
                        </span>

                        <strong>
                            {{ number_format($outOfStockCount) }}
                        </strong>
                    </div>

                </article>

                <article class="valuation-mini-card">

                    <div class="mini-card-icon margin">
                        <i class="fa-solid fa-percent"></i>
                    </div>

                    <div>
                        <span>
                            Average Margin
                        </span>

                        <strong>
                            {{ number_format($averageMargin, 1) }}%
                        </strong>
                    </div>

                </article>

            </section>

            {{-- Product Valuation Table --}}
            <section class="valuation-panel">

                <div class="valuation-panel-header">

                    <div class="panel-title">

                        <span class="valuation-panel-eyebrow">
                            Product Breakdown
                        </span>

                        <h2>
                            Inventory Value by Product
                        </h2>

                        <p>
                            Review, search and filter inventory performance.
                        </p>

                    </div>

                </div>

                <div class="valuation-filter-toolbar">

                    <div class="toolbar-search">

                        <i class="fa-solid fa-magnifying-glass"></i>

                        <input
                            id="valuation-product-search"
                            type="search"
                            placeholder="Search product, SKU..."
                            autocomplete="off">

                    </div>

                    <div class="toolbar-filters">

                        <select id="stockFilter">

                            <option value="">
                                All Stock
                            </option>

                            <option value="available">
                                In Stock
                            </option>

                            <option value="low">
                                Low Stock
                            </option>

                            <option value="out">
                                Out of Stock
                            </option>

                        </select>

                        <select id="marginFilter">

                            <option value="">
                                All Margins
                            </option>

                            <option value="positive">
                                Positive
                            </option>

                            <option value="negative">
                                Negative
                            </option>

                        </select>

                        <select id="sortFilter">

                            <option value="">
                                Sort Products
                            </option>

                            <option value="profit">
                                Highest Profit
                            </option>

                            <option value="value">
                                Highest Value
                            </option>

                            <option value="stock">
                                Highest Stock
                            </option>

                        </select>

                        <div class="saved-view-control">

                            <select
                                id="savedViewSelect"
                                class="saved-view-select">

                                <option value="">
                                    Saved Views
                                </option>

                            </select>

                            <button
                                type="button"
                                class="saved-view-button"
                                id="saveCurrentViewButton"
                                title="Save current filters">

                                <i class="fa-solid fa-bookmark"></i>

                                Save View

                            </button>

                            <button
                                type="button"
                                class="saved-view-delete"
                                id="deleteSavedViewButton"
                                title="Delete selected saved view"
                                disabled>

                                <i class="fa-solid fa-trash"></i>

                            </button>

                        </div>

                        <button
                            class="toolbar-reset"
                            id="resetFilters">

                            <i class="fa-solid fa-rotate"></i>

                            Reset

                        </button>

                    </div>

                </div>

                <div
                    class="saved-view-modal"
                    id="savedViewModal"
                    aria-hidden="true">

                    <div
                        class="saved-view-modal-backdrop"
                        data-close-saved-view-modal>
                    </div>

                    <div
                        class="saved-view-modal-dialog"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="savedViewModalTitle">

                        <div class="saved-view-modal-header">

                            <div>

                                <span class="saved-view-modal-eyebrow">
                                    Filter preset
                                </span>

                                <h3 id="savedViewModalTitle">
                                    Save Current View
                                </h3>

                            </div>

                            <button
                                type="button"
                                class="saved-view-modal-close"
                                data-close-saved-view-modal
                                aria-label="Close modal">

                                <i class="fa-solid fa-xmark"></i>

                            </button>

                        </div>

                        <div class="saved-view-modal-body">

                            <label for="savedViewName">
                                View Name
                            </label>

                            <input
                                type="text"
                                id="savedViewName"
                                maxlength="60"
                                placeholder="Example: Low Stock Products"
                                autocomplete="off">

                            <p class="saved-view-modal-help">
                                This view will remember your current search,
                                stock filter, margin filter, sorting and rows per page.
                            </p>

                            <div
                                class="saved-view-modal-error"
                                id="savedViewModalError"
                                hidden>
                            </div>

                        </div>

                        <div class="saved-view-modal-footer">

                            <button
                                type="button"
                                class="saved-view-cancel-button"
                                data-close-saved-view-modal>

                                Cancel

                            </button>

                            <button
                                type="button"
                                class="saved-view-confirm-button"
                                id="confirmSaveViewButton">

                                <i class="fa-solid fa-bookmark"></i>

                                Save View

                            </button>

                        </div>

                    </div>

                </div>

                <div class="valuation-table-wrapper">

                    <table class="valuation-table">

                        <thead>

                            <tr>
                                <th>Product</th>
                                <th>Stock Status</th>
                                <th class="number-column">Cost Price</th>
                                <th class="number-column">Selling Price</th>
                                <th class="number-column">Inventory Cost</th>
                                <th class="number-column">Retail Value</th>
                                <th class="number-column">Expected Profit</th>
                                <th class="number-column">Margin</th>
                                <th class="action-column"></th>
                            </tr>

                        </thead>

                        <tbody id="valuation-product-body">

                            @forelse ($products as $product)

                            @php
                            $stock = (int) ($product->stock ?? 0);
                            $costPrice = (float) ($product->cost_price ?? 0);
                            $regularPrice = (float) ($product->regular_price ?? 0);

                            $sellingPrice = !is_null($product->sale_price)
                            && (float) $product->sale_price > 0
                            ? (float) $product->sale_price
                            : $regularPrice;

                            $unitProfit = $sellingPrice - $costPrice;

                            $margin = $sellingPrice > 0
                            ? ($unitProfit / $sellingPrice) * 100
                            : 0;

                            if ($stock <= 0) {
                                $stockClass='out' ;
                                $stockLabel='Out of stock' ;
                                } elseif ($stock <=5) {
                                $stockClass='low' ;
                                $stockLabel='Low stock' ;
                                } else {
                                $stockClass='available' ;
                                $stockLabel='In stock' ;
                                }

                                $searchValue=strtolower(
                                trim(
                                ($product->title ?? '')
                                . ' '
                                . ($product->sku ?? '')
                                )
                                );
                                @endphp

                                <tr
                                    class="valuation-product-row"

                                    data-search="{{ $searchValue }}"

                                    data-stock="{{ $stockClass }}"

                                    data-margin="{{ $margin }}"

                                    data-stock-value="{{ $stock }}"

                                    data-profit="{{ $product->inventory_profit }}"

                                    data-value="{{ $product->inventory_retail }}"

                                    data-total-cost="{{ $product->inventory_cost }}"

                                    data-total-retail="{{ $product->inventory_retail }}"

                                    data-total-profit="{{ $product->inventory_profit }}"

                                    data-units="{{ $stock }}"
                                    data-cost-price="{{ $costPrice }}"

                                    data-selling-price="{{ $sellingPrice }}">

                                    <td>

                                        <div class="valuation-product">

                                            <div class="valuation-product-image">

                                                @if (!empty($product->featured_image))

                                                <img
                                                    src="{{ asset(
                                                    'storage/'
                                                    . $product->featured_image
                                                ) }}"
                                                    alt="{{ $product->title }}">

                                                @else

                                                <i class="fa-solid fa-box"></i>

                                                @endif

                                            </div>

                                            <div class="valuation-product-info">

                                                <a
                                                    href="{{ route(
                                                'admin.products.edit',
                                                $product
                                            ) }}">

                                                    {{ $product->title }}

                                                </a>

                                                <span>
                                                    SKU:
                                                    {{ $product->sku ?: 'Not assigned' }}
                                                </span>

                                            </div>

                                        </div>

                                    </td>

                                    <td>

                                        <div class="stock-status-group">

                                            <span
                                                class="stock-status-badge {{ $stockClass }}">

                                                <span class="stock-status-dot"></span>

                                                {{ $stockLabel }}
                                            </span>

                                            <small>
                                                {{ number_format($stock) }} units
                                            </small>

                                        </div>

                                    </td>

                                    <td class="number-column">

                                        @if ($costPrice > 0)

                                        £{{ number_format($costPrice, 2) }}

                                        @else

                                        <span class="missing-cost">
                                            Not set
                                        </span>

                                        @endif

                                    </td>

                                    <td class="number-column">

                                        <div class="price-display">

                                            <strong>
                                                £{{ number_format($sellingPrice, 2) }}
                                            </strong>

                                            @if (
                                            !is_null($product->sale_price)
                                            && (float) $product->sale_price > 0
                                            && (float) $product->sale_price
                                            < $regularPrice
                                                )

                                                <span>
                                                £{{ number_format(
                                                $regularPrice,
                                                2
                                            ) }}
                                                </span>

                                                @endif

                                        </div>

                                    </td>

                                    <td class="number-column">

                                        <strong>
                                            £{{ number_format(
                                        $product->inventory_cost,
                                        2
                                    ) }}
                                        </strong>

                                    </td>

                                    <td class="number-column">

                                        <strong>
                                            £{{ number_format(
                                        $product->inventory_retail,
                                        2
                                    ) }}
                                        </strong>

                                    </td>

                                    <td class="number-column">

                                        <span
                                            class="
                                        profit-value
                                        {{ $product->inventory_profit < 0
                                            ? 'negative'
                                            : ''
                                        }}
                                    ">

                                            {{ $product->inventory_profit < 0
                                        ? '-'
                                        : ''
                                    }}£{{ number_format(
                                        abs($product->inventory_profit),
                                        2
                                    ) }}

                                        </span>

                                    </td>

                                    <td class="number-column">

                                        <span
                                            class="
                                        margin-badge
                                        {{ $margin < 0
                                            ? 'negative'
                                            : ''
                                        }}
                                    ">

                                            {{ number_format($margin, 1) }}%

                                        </span>

                                    </td>

                                    <td class="action-column">

                                        <a
                                            href="{{ route(
                                        'admin.products.edit',
                                        $product
                                    ) }}"
                                            class="valuation-row-action"
                                            title="Edit product">

                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>

                                        </a>

                                    </td>

                                </tr>

                                @empty

                                <tr>
                                    <td colspan="9" class="valuation-empty-state">

                                        <div class="valuation-empty-icon">
                                            <i class="fa-solid fa-box-open"></i>
                                        </div>

                                        <h3>
                                            No products found
                                        </h3>

                                        <p>
                                            Add products to start calculating your
                                            stock valuation.
                                        </p>

                                        <a
                                            href="{{ route(
                                        'admin.products.create'
                                    ) }}"
                                            class="valuation-button primary">

                                            <i class="fa-solid fa-plus"></i>

                                            Add Product
                                        </a>

                                    </td>
                                </tr>

                                @endforelse

                                <tr
                                    id="valuation-search-empty"
                                    class="valuation-search-empty"
                                    style="display:none;">

                                    <td colspan="9" class="valuation-empty-state">

                                        <div class="valuation-empty-icon">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                        </div>

                                        <h3>
                                            No matching products
                                        </h3>

                                        <p>
                                            Try another product name or SKU.
                                        </p>

                                    </td>

                                </tr>

                        </tbody>

                        @if ($products->isNotEmpty())

                        <tfoot>

                            <tr>

                                <td colspan="4">
                                    <strong>
                                        Current Inventory Totals
                                    </strong>

                                    <span class="table-total-description">

                                        <span id="footerUnits">
                                            {{ number_format($totalUnits) }}
                                        </span>

                                        units across

                                        <span id="footerProducts">
                                            {{ number_format($products->count()) }}
                                        </span>

                                        products

                                    </span>
                                </td>

                                <td class="number-column">
                                    <strong id="footerTotalCost">

                                        £{{ number_format($totalCost,2) }}

                                    </strong>
                                </td>

                                <td class="number-column">
                                    <strong id="footerTotalRetail">

                                        £{{ number_format($totalRetail,2) }}

                                    </strong>
                                </td>

                                <td class="number-column">
                                    <strong
                                        class="table-profit-total"
                                        id="footerTotalProfit">

                                        £{{ number_format($totalProfit,2) }}

                                    </strong>
                                </td>

                                <td class="number-column">
                                    <strong>
                                        {{ number_format($averageMargin, 1) }}%
                                    </strong>
                                </td>

                                <td></td>

                            </tr>

                        </tfoot>

                        @endif

                    </table>

                </div>

                <div
                    class="valuation-pagination"
                    id="valuationPagination">

                    <div class="pagination-summary">

                        <span>
                            Showing
                        </span>

                        <strong id="paginationFrom">
                            0
                        </strong>

                        <span>
                            –
                        </span>

                        <strong id="paginationTo">
                            0
                        </strong>

                        <span>
                            of
                        </span>

                        <strong id="paginationTotal">
                            {{ number_format($products->count()) }}
                        </strong>

                        <span>
                            products
                        </span>

                    </div>

                    <div class="pagination-actions">

                        <label
                            for="valuationPageSize"
                            class="page-size-label">

                            Rows per page

                        </label>

                        <select
                            id="valuationPageSize"
                            class="page-size-select">

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

        </div>

        @endsection

        @push('page-styles')

        <style>
            /*
    |--------------------------------------------------------------------------
    | Stock Valuation Dashboard
    |--------------------------------------------------------------------------
    */

            .stock-valuation-page {
                --valuation-primary: #111827;
                --valuation-primary-hover: #1f2937;
                --valuation-text: #111827;
                --valuation-muted: #6b7280;
                --valuation-border: #e5e7eb;
                --valuation-soft-border: #eef0f3;
                --valuation-card: #ffffff;
                --valuation-background: #f6f7fb;
                --valuation-indigo: #4f46e5;
                --valuation-indigo-soft: #eef2ff;
                --valuation-green: #15803d;
                --valuation-green-soft: #ecfdf3;
                --valuation-orange: #c2410c;
                --valuation-orange-soft: #fff7ed;
                --valuation-red: #b91c1c;
                --valuation-red-soft: #fef2f2;
                --valuation-blue: #0369a1;
                --valuation-blue-soft: #f0f9ff;

                display: flex;
                flex-direction: column;
                gap: 24px;
                min-width: 0;
                padding: 4px;
                color: var(--valuation-text);
            }

            .stock-valuation-page *,
            .stock-valuation-page *::before,
            .stock-valuation-page *::after {
                box-sizing: border-box;
            }

            /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

            .valuation-header {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 24px;
                padding: 28px 30px;
                border: 1px solid rgba(229, 231, 235, 0.8);
                border-radius: 20px;
                background:
                    radial-gradient(circle at top right,
                        rgba(79, 70, 229, 0.14),
                        transparent 35%),
                    linear-gradient(135deg,
                        #ffffff 0%,
                        #f8f9ff 100%);
                box-shadow:
                    0 1px 2px rgba(15, 23, 42, 0.04),
                    0 12px 30px rgba(15, 23, 42, 0.06);
                overflow: hidden;
            }

            .valuation-header::before {
                content: "";
                position: absolute;
                top: -65px;
                right: -45px;
                width: 190px;
                height: 190px;
                border: 30px solid rgba(79, 70, 229, 0.05);
                border-radius: 50%;
                pointer-events: none;
            }

            .valuation-header-content {
                position: relative;
                z-index: 1;
                display: flex;
                align-items: center;
                gap: 18px;
                min-width: 0;
            }

            .valuation-header-icon {
                width: 58px;
                height: 58px;
                flex: 0 0 58px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 16px;
                background: var(--valuation-indigo-soft);
                color: var(--valuation-indigo);
                font-size: 23px;
            }

            .valuation-eyebrow,
            .valuation-panel-eyebrow {
                display: block;
                margin-bottom: 6px;
                color: var(--valuation-indigo);
                font-size: 12px;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .valuation-header h1 {
                margin: 0 0 8px;
                color: var(--valuation-text);
                font-size: 30px;
                line-height: 1.2;
            }

            .valuation-header p {
                max-width: 680px;
                margin: 0;
                color: var(--valuation-muted);
                font-size: 14px;
                line-height: 1.65;
            }

            .valuation-header-actions {
                position: relative;
                z-index: 1;
                display: flex;
                align-items: center;
                gap: 10px;
                flex-shrink: 0;
            }

            .valuation-button {
                min-height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 9px;
                padding: 10px 16px;
                border: 1px solid transparent;
                border-radius: 10px;
                font-size: 14px;
                font-weight: 700;
                text-decoration: none;
                cursor: pointer;
                white-space: nowrap;
                transition:
                    background 0.2s ease,
                    border-color 0.2s ease,
                    color 0.2s ease,
                    transform 0.2s ease;
            }

            .valuation-button:hover {
                transform: translateY(-1px);
            }

            .valuation-button.primary {
                background: var(--valuation-primary);
                color: #ffffff;
            }

            .valuation-button.primary:hover {
                background: var(--valuation-primary-hover);
                color: #ffffff;
            }

            .valuation-button.secondary {
                border-color: var(--valuation-border);
                background: #ffffff;
                color: #374151;
            }

            .valuation-button.secondary:hover {
                border-color: #cbd5e1;
                background: #f9fafb;
                color: var(--valuation-text);
            }

            .valuation-button.export {
                border-color: #bbf7d0;
                background: #ecfdf3;
                color: #15803d;
            }

            .valuation-button.export:hover {
                border-color: #15803d;
                background: #15803d;
                color: #ffffff;
            }

            /*
    |--------------------------------------------------------------------------
    | Summary Cards
    |--------------------------------------------------------------------------
    */

            .valuation-summary-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 18px;
            }

            .valuation-summary-card {
                position: relative;
                min-width: 0;
                padding: 21px;
                border: 1px solid var(--valuation-border);
                border-radius: 16px;
                background: var(--valuation-card);
                box-shadow:
                    0 1px 2px rgba(15, 23, 42, 0.03),
                    0 8px 20px rgba(15, 23, 42, 0.04);
                overflow: hidden;
            }

            .valuation-summary-card::after {
                content: "";
                position: absolute;
                right: -28px;
                bottom: -36px;
                width: 90px;
                height: 90px;
                border: 16px solid rgba(148, 163, 184, 0.07);
                border-radius: 50%;
                pointer-events: none;
            }

            .summary-card-top {
                display: flex;
                align-items: center;
                gap: 11px;
                margin-bottom: 18px;
            }

            .summary-icon {
                width: 40px;
                height: 40px;
                flex: 0 0 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 11px;
                font-size: 16px;
            }

            .summary-icon.cost {
                background: var(--valuation-indigo-soft);
                color: var(--valuation-indigo);
            }

            .summary-icon.retail {
                background: var(--valuation-blue-soft);
                color: var(--valuation-blue);
            }

            .summary-icon.profit {
                background: var(--valuation-green-soft);
                color: var(--valuation-green);
            }

            .summary-icon.units {
                background: var(--valuation-orange-soft);
                color: var(--valuation-orange);
            }

            .summary-label {
                color: var(--valuation-muted);
                font-size: 13px;
                font-weight: 700;
            }

            .summary-value {
                position: relative;
                z-index: 1;
                display: block;
                margin-bottom: 13px;
                color: var(--valuation-text);
                font-size: 27px;
                line-height: 1.2;
                letter-spacing: -0.02em;
            }

            .summary-footer {
                position: relative;
                z-index: 1;
                min-height: 19px;
                color: var(--valuation-muted);
                font-size: 12px;
                line-height: 1.5;
            }

            .summary-positive {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                color: var(--valuation-green);
                font-weight: 700;
            }

            .summary-value.negative,
            .table-profit-total.negative {
                color: var(--valuation-red);
            }

            /*
    |--------------------------------------------------------------------------
    | Mini Cards
    |--------------------------------------------------------------------------
    */

            .valuation-mini-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 14px;
            }

            .valuation-mini-card {
                display: flex;
                align-items: center;
                gap: 13px;
                min-width: 0;
                padding: 15px 17px;
                border: 1px solid var(--valuation-border);
                border-radius: 13px;
                background: #ffffff;
            }

            .mini-card-icon {
                width: 38px;
                height: 38px;
                flex: 0 0 38px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 10px;
                font-size: 15px;
            }

            .mini-card-icon.configured {
                background: var(--valuation-green-soft);
                color: var(--valuation-green);
            }

            .mini-card-icon.low-stock {
                background: var(--valuation-orange-soft);
                color: var(--valuation-orange);
            }

            .mini-card-icon.out-stock {
                background: var(--valuation-red-soft);
                color: var(--valuation-red);
            }

            .mini-card-icon.margin {
                background: var(--valuation-indigo-soft);
                color: var(--valuation-indigo);
            }

            .valuation-mini-card span {
                display: block;
                margin-bottom: 3px;
                color: var(--valuation-muted);
                font-size: 12px;
                font-weight: 600;
            }

            .valuation-mini-card strong {
                display: block;
                color: var(--valuation-text);
                font-size: 18px;
                line-height: 1.2;
            }

            /*
    |--------------------------------------------------------------------------
    | Panel and Search
    |--------------------------------------------------------------------------
    */

            .valuation-panel {
                min-width: 0;
                border: 1px solid var(--valuation-border);
                border-radius: 17px;
                background: #ffffff;
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
                overflow: hidden;
            }

            .valuation-panel-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 24px;
                padding: 22px 24px;
                border-bottom: 1px solid var(--valuation-border);
            }

            .valuation-panel-header h2 {
                margin: 0 0 6px;
                color: var(--valuation-text);
                font-size: 20px;
                line-height: 1.3;
            }

            .valuation-panel-header p {
                margin: 0;
                color: var(--valuation-muted);
                font-size: 13px;
                line-height: 1.6;
            }


            /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

            .valuation-table-wrapper {
                width: 100%;
                overflow-x: auto;
            }

            .valuation-table {
                width: 100%;
                min-width: 1180px;
                border-collapse: collapse;
            }

            .valuation-table th,
            .valuation-table td {
                padding: 15px 17px;
                border-bottom: 1px solid var(--valuation-soft-border);
                vertical-align: middle;
            }

            .valuation-table th {
                background: #f8fafc;
                color: #64748b;
                font-size: 11px;
                font-weight: 800;
                letter-spacing: 0.055em;
                text-align: left;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .valuation-table td {
                color: #374151;
                font-size: 13px;
            }

            .valuation-table tbody tr {
                transition: background 0.18s ease;
            }

            .valuation-table tbody tr:hover {
                background: #fafbff;
            }

            .valuation-table tbody tr:last-child td {
                border-bottom: 0;
            }

            .valuation-table .number-column {
                text-align: right;
                white-space: nowrap;
            }

            .valuation-table .action-column {
                width: 54px;
                text-align: right;
            }

            .valuation-table tfoot td {
                padding-top: 17px;
                padding-bottom: 17px;
                border-top: 1px solid var(--valuation-border);
                border-bottom: 0;
                background: #f8fafc;
                color: var(--valuation-text);
            }

            .table-total-description {
                display: block;
                margin-top: 4px;
                color: var(--valuation-muted);
                font-size: 11px;
                font-weight: 500;
            }

            .table-profit-total {
                color: var(--valuation-green);
            }

            /*
|--------------------------------------------------------------------------
| Table Pagination
|--------------------------------------------------------------------------
*/

            .valuation-pagination {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
                padding: 18px 24px;
                border-top: 1px solid var(--valuation-border);
                background: #ffffff;
            }

            .pagination-summary {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 5px;
                color: var(--valuation-muted);
                font-size: 12px;
                line-height: 1.5;
            }

            .pagination-summary strong {
                color: var(--valuation-text);
                font-size: 12px;
                font-weight: 800;
            }

            .pagination-actions {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 10px;
            }

            .page-size-label {
                color: var(--valuation-muted);
                font-size: 12px;
                font-weight: 700;
                white-space: nowrap;
            }

            .page-size-select {
                width: 74px;
                height: 38px;
                padding: 0 28px 0 11px;
                border: 1px solid #d7dce5;
                border-radius: 9px;
                background-color: #ffffff;
                color: #374151;
                font-family: inherit;
                font-size: 12px;
                font-weight: 700;
                line-height: 1;
                cursor: pointer;
                outline: none;
                appearance: none;
                -webkit-appearance: none;
                -moz-appearance: none;

                background-image:
                    linear-gradient(45deg,
                        transparent 50%,
                        #64748b 50%),
                    linear-gradient(135deg,
                        #64748b 50%,
                        transparent 50%);

                background-position:
                    calc(100% - 15px) 16px,
                    calc(100% - 10px) 16px;

                background-size:
                    5px 5px,
                    5px 5px;

                background-repeat: no-repeat;
            }

            .page-size-select:focus {
                border-color: var(--valuation-indigo);
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            }

            .pagination-buttons {
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .pagination-button {
                min-width: 38px;
                height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                padding: 0 11px;
                border: 1px solid #d7dce5;
                border-radius: 9px;
                background: #ffffff;
                color: #475569;
                font-family: inherit;
                font-size: 12px;
                font-weight: 750;
                line-height: 1;
                cursor: pointer;
                transition:
                    border-color 0.2s ease,
                    background 0.2s ease,
                    color 0.2s ease,
                    transform 0.2s ease;
            }

            .pagination-button:hover:not(:disabled) {
                border-color: var(--valuation-indigo);
                background: var(--valuation-indigo-soft);
                color: var(--valuation-indigo);
                transform: translateY(-1px);
            }

            .pagination-button.active {
                border-color: var(--valuation-indigo);
                background: var(--valuation-indigo);
                color: #ffffff;
            }

            .pagination-button:disabled {
                border-color: #e5e7eb;
                background: #f8fafc;
                color: #cbd5e1;
                cursor: not-allowed;
            }

            .pagination-ellipsis {
                min-width: 24px;
                color: #94a3b8;
                font-size: 13px;
                font-weight: 700;
                text-align: center;
            }

            /*
    |--------------------------------------------------------------------------
    | Product Information
    |--------------------------------------------------------------------------
    */

            .valuation-product {
                display: flex;
                align-items: center;
                gap: 12px;
                min-width: 230px;
            }

            .valuation-product-image {
                width: 44px;
                height: 44px;
                flex: 0 0 44px;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--valuation-border);
                border-radius: 10px;
                background: #f8fafc;
                color: #94a3b8;
                overflow: hidden;
            }

            .valuation-product-image img {
                width: 100%;
                height: 100%;
                display: block;
                object-fit: cover;
            }

            .valuation-product-info {
                min-width: 0;
            }

            .valuation-product-info a {
                display: block;
                max-width: 250px;
                margin-bottom: 4px;
                overflow: hidden;
                color: var(--valuation-text);
                font-size: 13px;
                font-weight: 750;
                text-decoration: none;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .valuation-product-info a:hover {
                color: var(--valuation-indigo);
            }

            .valuation-product-info span {
                display: block;
                color: var(--valuation-muted);
                font-size: 11px;
            }

            /*
    |--------------------------------------------------------------------------
    | Stock and Price Indicators
    |--------------------------------------------------------------------------
    */

            .stock-status-group {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
                white-space: nowrap;
            }

            .stock-status-group small {
                color: var(--valuation-muted);
                font-size: 11px;
            }

            .stock-status-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 5px 8px;
                border-radius: 999px;
                font-size: 10px;
                font-weight: 800;
            }

            .stock-status-dot {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background: currentColor;
            }

            .stock-status-badge.available {
                background: var(--valuation-green-soft);
                color: var(--valuation-green);
            }

            .stock-status-badge.low {
                background: var(--valuation-orange-soft);
                color: var(--valuation-orange);
            }

            .stock-status-badge.out {
                background: var(--valuation-red-soft);
                color: var(--valuation-red);
            }

            .missing-cost {
                display: inline-flex;
                padding: 5px 8px;
                border-radius: 7px;
                background: var(--valuation-orange-soft);
                color: var(--valuation-orange);
                font-size: 10px;
                font-weight: 800;
            }

            .price-display {
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                gap: 3px;
            }

            .price-display strong {
                color: var(--valuation-text);
            }

            .price-display span {
                color: #9ca3af;
                font-size: 10px;
                text-decoration: line-through;
            }

            .profit-value {
                color: var(--valuation-green);
                font-weight: 800;
            }

            .profit-value.negative {
                color: var(--valuation-red);
            }

            .margin-badge {
                display: inline-flex;
                justify-content: center;
                min-width: 57px;
                padding: 6px 9px;
                border-radius: 8px;
                background: var(--valuation-green-soft);
                color: var(--valuation-green);
                font-size: 11px;
                font-weight: 800;
            }

            .margin-badge.negative {
                background: var(--valuation-red-soft);
                color: var(--valuation-red);
            }

            .valuation-row-action {
                width: 34px;
                height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--valuation-border);
                border-radius: 9px;
                background: #ffffff;
                color: #64748b;
                text-decoration: none;
                transition:
                    background 0.2s ease,
                    border-color 0.2s ease,
                    color 0.2s ease;
            }

            .valuation-row-action:hover {
                border-color: var(--valuation-primary);
                background: var(--valuation-primary);
                color: #ffffff;
            }

            /*
    |--------------------------------------------------------------------------
    | Empty State
    |--------------------------------------------------------------------------
    */

            .valuation-empty-state {
                padding: 55px 20px !important;
                text-align: center;
            }

            .valuation-empty-icon {
                width: 58px;
                height: 58px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 15px;
                border-radius: 16px;
                background: #f1f5f9;
                color: #64748b;
                font-size: 21px;
            }

            .valuation-empty-state h3 {
                margin: 0 0 7px;
                color: var(--valuation-text);
                font-size: 17px;
            }

            .valuation-empty-state p {
                margin: 0 0 18px;
                color: var(--valuation-muted);
                font-size: 13px;
            }

            /*
    |--------------------------------------------------------------------------
    | Stock health
    |--------------------------------------------------------------------------
    */

            .inventory-health {

                display: grid;

                grid-template-columns: 380px 1fr;

                gap: 24px;

                margin: 24px 0;

            }

            .health-left {

                background: #fff;

                border: 1px solid var(--valuation-border);

                border-radius: 18px;

                padding: 28px;

            }

            .health-header {

                display: flex;

                align-items: center;

                gap: 15px;

                margin-bottom: 25px;

            }

            .health-icon {

                width: 58px;

                height: 58px;

                border-radius: 16px;

                background: #eef2ff;

                display: flex;

                align-items: center;

                justify-content: center;

                font-size: 24px;

                color: #4f46e5;

            }

            .health-header span {

                display: block;

                font-size: 12px;

                font-weight: 700;

                color: #6366f1;

                text-transform: uppercase;

                letter-spacing: .08em;

            }

            .health-header h2 {

                margin: 4px 0 0;

                font-size: 22px;

            }

            .health-progress {

                height: 14px;

                background: #edf2f7;

                border-radius: 20px;

                overflow: hidden;

                margin: 30px 0;

            }

            .health-progress-bar {

                height: 100%;

                background: linear-gradient(90deg, #22c55e, #4f46e5);

                border-radius: 20px;

            }

            .health-score {

                font-size: 46px;

                font-weight: 800;

            }

            .health-right {

                display: grid;

                grid-template-columns: repeat(2, 1fr);

                gap: 18px;

            }

            .health-item {

                display: flex;

                align-items: center;

                gap: 18px;

                padding: 22px;

                border-radius: 16px;

                border: 1px solid var(--valuation-border);

                background: #fff;

            }

            .health-item i {

                width: 48px;

                height: 48px;

                display: flex;

                align-items: center;

                justify-content: center;

                border-radius: 12px;

                font-size: 20px;

            }

            .health-item strong {

                display: block;

                font-size: 24px;

                margin-bottom: 4px;

            }

            .health-item span {

                color: #64748b;

                font-size: 13px;

            }

            .health-item.success i {

                background: #ecfdf5;

                color: #16a34a;

            }

            .health-item.warning i {

                background: #fff7ed;

                color: #ea580c;

            }

            .health-item.danger i {

                background: #fef2f2;

                color: #dc2626;

            }

            .health-item.orange i {

                background: #fef3c7;

                color: #d97706;

            }

            .health-item.red i {

                background: #fee2e2;

                color: #b91c1c;

            }

            /*
    |--------------------------------------------------------------------------
    | analytics card
    |--------------------------------------------------------------------------
    */

            .analytics-grid {

                display: grid;

                grid-template-columns: repeat(2, 1fr);

                gap: 22px;

                margin: 24px 0;

            }

            .analytics-card {

                background: #fff;

                border: 1px solid var(--valuation-border);

                border-radius: 18px;

                padding: 22px;

                box-shadow: 0 8px 24px rgba(15, 23, 42, .04);

            }

            .analytics-card-header {

                display: flex;

                align-items: center;

                gap: 14px;

                margin-bottom: 20px;

            }

            .analytics-icon {

                width: 48px;

                height: 48px;

                border-radius: 12px;

                display: flex;

                align-items: center;

                justify-content: center;

                font-size: 18px;

            }

            .analytics-icon.blue {

                background: #eef2ff;

                color: #4f46e5;

            }

            .analytics-icon.green {

                background: #ecfdf5;

                color: #16a34a;

            }

            .analytics-icon.orange {

                background: #fff7ed;

                color: #ea580c;

            }

            .analytics-icon.red {

                background: #fef2f2;

                color: #dc2626;

            }

            .analytics-card h3 {

                margin: 0;

                font-size: 18px;

            }

            .analytics-card span {

                font-size: 12px;

                color: #64748b;

            }

            .analytics-list {

                display: flex;

                flex-direction: column;

                gap: 12px;

            }

            .analytics-row {

                display: flex;

                justify-content: space-between;

                align-items: center;

                padding: 12px 0;

                border-top: 1px solid #eef2f7;

            }

            .analytics-row:first-child {

                border-top: none;

                padding-top: 0;

            }

            .analytics-row strong {

                display: block;

                font-size: 14px;

            }

            .analytics-row small {

                color: #64748b;

            }


            /*
    |--------------------------------------------------------------------------
    | valuation-filter-toolbar
    |--------------------------------------------------------------------------
    */
            .analytics-dashboard {

                display: grid;

                grid-template-columns: repeat(2, 1fr);

                gap: 24px;

            }

            .chart-card {

                background: #fff;

                border: 1px solid var(--valuation-border);

                border-radius: 18px;

                padding: 24px;

                box-shadow: 0 8px 24px rgba(15, 23, 42, .04);

            }

            .chart-card h3 {

                margin: 0 0 20px;

                font-size: 18px;

            }

            .chart-full {

                grid-column: 1/-1;

            }

            .chart-card canvas {

                width: 100% !important;

                height: 320px !important;

            }



            /*
|--------------------------------------------------------------------------
| Valuation Filter Toolbar
|--------------------------------------------------------------------------
*/

            .valuation-filter-toolbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 18px;
                padding: 20px 24px;
                border-bottom: 1px solid var(--valuation-border);
                background:
                    linear-gradient(135deg,
                        #fafbff 0%,
                        #f8fafc 100%);
                width: 100%;
            }

            .toolbar-search {
                position: relative;
                width: 100%;
                max-width: 640px;
                min-width: 280px;
                flex: 1 1 500px;
            }

            .toolbar-search>i {
                position: absolute;
                top: 50%;
                left: 16px;
                z-index: 2;
                color: #94a3b8;
                font-size: 14px;
                line-height: 1;
                transform: translateY(-50%);
                pointer-events: none;
            }

            .toolbar-search input {
                width: 100%;
                height: 46px;
                padding: 0 16px 0 43px;
                border: 1px solid #d7dce5;
                border-radius: 11px;
                background: #ffffff;
                color: var(--valuation-text);
                font-family: inherit;
                font-size: 13px;
                outline: none;
                transition:
                    border-color 0.2s ease,
                    box-shadow 0.2s ease,
                    background 0.2s ease;
            }

            .toolbar-search input::placeholder {
                color: #94a3b8;
            }

            .toolbar-search input:focus {
                border-color: var(--valuation-indigo);
                background: #ffffff;
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            }

            .toolbar-filters {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 10px;
                flex: 0 0 auto;
                flex-wrap: nowrap;
            }

            .valuation-button.pdf {
                border-color: #fecaca;
                background: #fef2f2;
                color: #b91c1c;
            }

            .valuation-button.pdf:hover {
                border-color: #b91c1c;
                background: #b91c1c;
                color: #ffffff;
            }

            .toolbar-filters select {
                width: auto;
                min-width: 145px;
                height: 46px;
                padding: 0 38px 0 14px;
                border: 1px solid #d7dce5;
                border-radius: 11px;
                background-color: #ffffff;
                color: #374151;
                font-family: inherit;
                font-size: 13px;
                font-weight: 650;
                line-height: 1;
                cursor: pointer;
                outline: none;
                appearance: none;
                -webkit-appearance: none;
                -moz-appearance: none;

                background-image:
                    linear-gradient(45deg,
                        transparent 50%,
                        #64748b 50%),
                    linear-gradient(135deg,
                        #64748b 50%,
                        transparent 50%);

                background-position:
                    calc(100% - 18px) 20px,
                    calc(100% - 13px) 20px;

                background-size:
                    5px 5px,
                    5px 5px;

                background-repeat: no-repeat;

                transition:
                    border-color 0.2s ease,
                    box-shadow 0.2s ease,
                    background-color 0.2s ease;
            }

            .toolbar-filters select:hover {
                border-color: #b9c1ce;
                background-color: #fbfcff;
            }

            .toolbar-filters select:focus {
                border-color: var(--valuation-indigo);
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            }

            .toolbar-reset {
                height: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 0 18px;
                border: 1px solid var(--valuation-primary);
                border-radius: 11px;
                background: var(--valuation-primary);
                color: #ffffff;
                font-family: inherit;
                font-size: 13px;
                font-weight: 750;
                line-height: 1;
                cursor: pointer;
                white-space: nowrap;
                transition:
                    background 0.2s ease,
                    border-color 0.2s ease,
                    transform 0.2s ease,
                    box-shadow 0.2s ease;
            }

            .toolbar-reset:hover {
                border-color: var(--valuation-primary-hover);
                background: var(--valuation-primary-hover);
                color: #ffffff;
                box-shadow: 0 7px 16px rgba(17, 24, 39, 0.14);
                transform: translateY(-1px);
            }

            .toolbar-reset:active {
                box-shadow: none;
                transform: translateY(0);
            }

            .toolbar-reset i {
                position: static;
                width: auto;
                height: auto;
                color: inherit;
                font-size: 13px;
                line-height: 1;
                transform: none;
                pointer-events: none;
            }

            /*
|--------------------------------------------------------------------------
| Saved Views
|--------------------------------------------------------------------------
*/

            .saved-view-control {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .saved-view-select {
                width: 165px;
                min-width: 165px;
                height: 46px;
                padding: 0 38px 0 14px;
                border: 1px solid #d7dce5;
                border-radius: 11px;
                background-color: #ffffff;
                color: #374151;
                font-family: inherit;
                font-size: 13px;
                font-weight: 650;
                line-height: 1;
                cursor: pointer;
                outline: none;
                appearance: none;
                -webkit-appearance: none;
                -moz-appearance: none;

                background-image:
                    linear-gradient(45deg,
                        transparent 50%,
                        #64748b 50%),
                    linear-gradient(135deg,
                        #64748b 50%,
                        transparent 50%);

                background-position:
                    calc(100% - 18px) 20px,
                    calc(100% - 13px) 20px;

                background-size:
                    5px 5px,
                    5px 5px;

                background-repeat: no-repeat;
            }

            .saved-view-select:focus {
                border-color: var(--valuation-indigo);
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            }

            .saved-view-button,
            .saved-view-delete {
                height: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 7px;
                border-radius: 11px;
                font-family: inherit;
                font-size: 13px;
                font-weight: 750;
                cursor: pointer;
                transition:
                    border-color 0.2s ease,
                    background 0.2s ease,
                    color 0.2s ease,
                    transform 0.2s ease;
            }

            .saved-view-button {
                padding: 0 15px;
                border: 1px solid #c7d2fe;
                background: #eef2ff;
                color: #4f46e5;
                white-space: nowrap;
            }

            .saved-view-button:hover {
                border-color: #4f46e5;
                background: #4f46e5;
                color: #ffffff;
                transform: translateY(-1px);
            }

            .saved-view-delete {
                width: 46px;
                padding: 0;
                border: 1px solid #fecaca;
                background: #fef2f2;
                color: #b91c1c;
            }

            .saved-view-delete:hover:not(:disabled) {
                border-color: #b91c1c;
                background: #b91c1c;
                color: #ffffff;
                transform: translateY(-1px);
            }

            .saved-view-delete:disabled {
                border-color: #e5e7eb;
                background: #f8fafc;
                color: #cbd5e1;
                cursor: not-allowed;
            }

            /*
|--------------------------------------------------------------------------
| Saved View Modal
|--------------------------------------------------------------------------
*/

            .saved-view-modal {
                position: fixed;
                inset: 0;
                z-index: 9999;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .saved-view-modal.open {
                display: flex;
            }

            .saved-view-modal-backdrop {
                position: absolute;
                inset: 0;
                background: rgba(15, 23, 42, 0.55);
                backdrop-filter: blur(4px);
            }

            .saved-view-modal-dialog {
                position: relative;
                z-index: 1;
                width: min(100%, 480px);
                border: 1px solid rgba(229, 231, 235, 0.9);
                border-radius: 18px;
                background: #ffffff;
                box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
                overflow: hidden;
            }

            .saved-view-modal-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 20px;
                padding: 22px 24px;
                border-bottom: 1px solid var(--valuation-border);
                background:
                    linear-gradient(135deg,
                        #ffffff 0%,
                        #f8f9ff 100%);
            }

            .saved-view-modal-eyebrow {
                display: block;
                margin-bottom: 5px;
                color: var(--valuation-indigo);
                font-size: 11px;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .saved-view-modal-header h3 {
                margin: 0;
                color: var(--valuation-text);
                font-size: 20px;
            }

            .saved-view-modal-close {
                width: 38px;
                height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid var(--valuation-border);
                border-radius: 10px;
                background: #ffffff;
                color: #64748b;
                cursor: pointer;
            }

            .saved-view-modal-close:hover {
                border-color: #111827;
                background: #111827;
                color: #ffffff;
            }

            .saved-view-modal-body {
                padding: 24px;
            }

            .saved-view-modal-body label {
                display: block;
                margin-bottom: 8px;
                color: #374151;
                font-size: 13px;
                font-weight: 750;
            }

            .saved-view-modal-body input {
                width: 100%;
                height: 46px;
                padding: 0 14px;
                border: 1px solid #d7dce5;
                border-radius: 10px;
                background: #ffffff;
                color: #111827;
                font-family: inherit;
                font-size: 14px;
                outline: none;
            }

            .saved-view-modal-body input:focus {
                border-color: var(--valuation-indigo);
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            }

            .saved-view-modal-help {
                margin: 10px 0 0;
                color: #64748b;
                font-size: 12px;
                line-height: 1.6;
            }

            .saved-view-modal-error {
                margin-top: 12px;
                padding: 10px 12px;
                border: 1px solid #fecaca;
                border-radius: 9px;
                background: #fef2f2;
                color: #b91c1c;
                font-size: 12px;
                font-weight: 650;
            }

            .saved-view-modal-footer {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                padding: 17px 24px;
                border-top: 1px solid var(--valuation-border);
                background: #f8fafc;
            }

            .saved-view-cancel-button,
            .saved-view-confirm-button {
                min-height: 42px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 0 16px;
                border-radius: 10px;
                font-family: inherit;
                font-size: 13px;
                font-weight: 750;
                cursor: pointer;
            }

            .saved-view-cancel-button {
                border: 1px solid #d7dce5;
                background: #ffffff;
                color: #475569;
            }

            .saved-view-confirm-button {
                border: 1px solid #4f46e5;
                background: #4f46e5;
                color: #ffffff;
            }

            .saved-view-confirm-button:hover {
                border-color: #4338ca;
                background: #4338ca;
            }

            /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */
            @media (max-width: 1400px) {
                .valuation-filter-toolbar {
                    align-items: stretch;
                    flex-direction: column;
                }

                .toolbar-search {
                    width: 100%;
                    max-width: none;
                    min-width: 0;
                    flex-basis: auto;
                }

                .toolbar-filters {
                    justify-content: flex-start;
                    flex-wrap: wrap;
                }
            }

            @media (max-width: 1200px) {
                .valuation-summary-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .valuation-mini-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .inventory-health {

                    grid-template-columns: 1fr;

                }

                .health-right {

                    grid-template-columns: repeat(2, 1fr);

                }

                .valuation-filter-toolbar {
                    align-items: stretch;
                    flex-direction: column;
                }

                .toolbar-search {
                    width: 100%;
                    max-width: none;
                    flex-basis: auto;
                }

                .toolbar-filters {
                    justify-content: flex-start;
                }

                .toolbar-filters select {
                    flex: 1 1 160px;
                }
            }

            @media(max-width:1100px) {}

            @media(max-width:992px) {

                .analytics-grid {

                    grid-template-columns: 1fr;

                }

                .analytics-dashboard {

                    grid-template-columns: 1fr;

                }

                .chart-full {

                    grid-column: auto;

                }

            }


            @media (max-width: 900px) {
                .valuation-header {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .valuation-header-actions {
                    width: 100%;
                }

                .valuation-header-actions .valuation-button {
                    flex: 1;
                }

                .valuation-panel-header {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .valuation-filter-toolbar {
                    width: 100%;
                    max-width: none;
                }
            }


            @media(max-width:700px) {

                .health-right {

                    grid-template-columns: 1fr;

                }

                .valuation-filter-toolbar {
                    gap: 14px;
                    padding: 17px;
                }

                .toolbar-filters {
                    display: grid;
                    grid-template-columns: 1fr;
                    width: 100%;
                }

                .toolbar-filters select,
                .toolbar-reset {
                    width: 100%;
                    min-width: 0;
                }

                .valuation-pagination {
                    align-items: stretch;
                    flex-direction: column;
                    padding: 17px;
                }

                .pagination-summary {
                    justify-content: center;
                }

                .pagination-actions {
                    justify-content: center;
                    flex-wrap: wrap;
                }

                .pagination-buttons {
                    justify-content: center;
                    flex-wrap: wrap;
                }

                .saved-view-control {
                    display: grid;
                    grid-template-columns: 1fr auto;
                    width: 100%;
                }

                .saved-view-select {
                    width: 100%;
                    min-width: 0;
                    grid-column: 1 / -1;
                }

                .saved-view-button {
                    width: 100%;
                }

                .saved-view-delete {
                    width: 46px;
                }

            }





            @media (max-width: 640px) {
                .stock-valuation-page {
                    gap: 18px;
                    padding: 0;
                }

                .valuation-header {
                    padding: 22px 18px;
                    border-radius: 15px;
                }

                .valuation-header-content {
                    align-items: flex-start;
                }

                .valuation-header-icon {
                    width: 47px;
                    height: 47px;
                    flex-basis: 47px;
                    border-radius: 13px;
                    font-size: 19px;
                }

                .valuation-header h1 {
                    font-size: 24px;
                }

                .valuation-header-actions {
                    align-items: stretch;
                    flex-direction: column;
                }

                .valuation-summary-grid,
                .valuation-mini-grid {
                    grid-template-columns: 1fr;
                }

                .valuation-summary-card {
                    padding: 18px;
                }

                .summary-value {
                    font-size: 24px;
                }

                .valuation-panel {
                    border-radius: 14px;
                }

                .valuation-panel-header {
                    padding: 19px 17px;
                }
            }

            /*
    |--------------------------------------------------------------------------
    | Print
    |--------------------------------------------------------------------------
    */

            @media print {

                .valuation-header-actions,
                .valuation-filter-toolbar,
                .valuation-pagination,
                .saved-view-modal,
                .saved-view-control,
                .valuation-row-action {
                    display: none !important;
                }

                .stock-valuation-page {
                    gap: 14px;
                    padding: 0;
                }

                .valuation-header,
                .valuation-summary-card,
                .valuation-mini-card,
                .valuation-panel {
                    box-shadow: none;
                }

                .valuation-header {
                    padding: 18px;
                }

                .valuation-summary-grid,
                .valuation-mini-grid {
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                }

                .valuation-table-wrapper {
                    overflow: visible;
                }

                .valuation-table {
                    min-width: 0;
                    font-size: 9px;
                }

                .valuation-table th,
                .valuation-table td {
                    padding: 8px;
                }
            }
        </style>

        @endpush

        @push('page-scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                'use strict';

                const valuationStorageKey =
                    'arizona-outfits-stock-valuation-preferences';

                const tbody = document.getElementById(
                    'valuation-product-body'
                );

                const search = document.getElementById(
                    'valuation-product-search'
                );

                const stock = document.getElementById(
                    'stockFilter'
                );

                const margin = document.getElementById(
                    'marginFilter'
                );

                const sort = document.getElementById(
                    'sortFilter'
                );

                const reset = document.getElementById(
                    'resetFilters'
                );

                const empty = document.getElementById(
                    'valuation-search-empty'
                );

                const pagination = document.getElementById(
                    'valuationPagination'
                );

                const paginationFrom = document.getElementById(
                    'paginationFrom'
                );

                const paginationTo = document.getElementById(
                    'paginationTo'
                );

                const paginationTotal = document.getElementById(
                    'paginationTotal'
                );

                const pageSizeSelect = document.getElementById(
                    'valuationPageSize'
                );

                const paginationButtons = document.getElementById(
                    'paginationButtons'
                );

                let currentPage = 1;

                const exportCsvButton = document.getElementById(
                    'exportCsvButton'
                );

                const exportExcelButton = document.getElementById(
                    'exportExcelButton'
                );

                const exportPdfButton = document.getElementById(
                    'exportPdfButton'
                );
                const savedViewSelect = document.getElementById(
                    'savedViewSelect'
                );

                const saveCurrentViewButton = document.getElementById(
                    'saveCurrentViewButton'
                );

                const deleteSavedViewButton = document.getElementById(
                    'deleteSavedViewButton'
                );

                const savedViewModal = document.getElementById(
                    'savedViewModal'
                );

                const savedViewName = document.getElementById(
                    'savedViewName'
                );

                const confirmSaveViewButton = document.getElementById(
                    'confirmSaveViewButton'
                );

                const savedViewModalError = document.getElementById(
                    'savedViewModalError'
                );

                const savedViewsStorageKey =
                    'arizona-outfits-stock-valuation-saved-views';
                const inventoryLabels =
                    @json($inventoryValueLabels);

                const inventoryData =
                    @json($inventoryValueData);

                const profitLabels =
                    @json($profitLabels);

                const profitData =
                    @json($profitData);

                const stockData =
                    @json(array_values($stockDistribution));

                const stockLabels =
                    @json(array_keys($stockDistribution));

                const marginData =
                    @json(array_values($marginDistribution));

                const marginLabels =
                    @json(array_keys($marginDistribution));

                const categoryLabels =
                    @json(array_keys($categoryInventory));

                const categoryData =
                    @json(array_values($categoryInventory));

                const rows = Array.from(
                    document.querySelectorAll('.valuation-product-row')
                );

                /*
                |--------------------------------------------------------------------------
                | Required elements safety check
                |--------------------------------------------------------------------------
                */

                if (
                    !tbody ||
                    !search ||
                    !stock ||
                    !margin ||
                    !sort ||
                    !reset
                ) {
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Preserve original product order
                |--------------------------------------------------------------------------
                */

                rows.forEach(function(row, index) {
                    row.dataset.originalOrder = index;
                });

                /*
                |--------------------------------------------------------------------------
                | Safe number conversion
                |--------------------------------------------------------------------------
                */

                function numberValue(value) {
                    const parsedValue = Number.parseFloat(value);

                    return Number.isFinite(parsedValue) ?
                        parsedValue :
                        0;
                }

                /*
                |--------------------------------------------------------------------------
                | Currency formatter
                |--------------------------------------------------------------------------
                */

                function formatCurrency(value) {
                    return '£' + value.toLocaleString('en-GB', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                /*
|--------------------------------------------------------------------------
| Save table preferences
|--------------------------------------------------------------------------
*/

                function saveTablePreferences() {
                    const preferences = {
                        search: search ? search.value : '',
                        stock: stock ? stock.value : '',
                        margin: margin ? margin.value : '',
                        sort: sort ? sort.value : '',
                        pageSize: pageSizeSelect ?
                            pageSizeSelect.value : '10',
                        currentPage: currentPage
                    };

                    try {
                        window.localStorage.setItem(
                            valuationStorageKey,
                            JSON.stringify(preferences)
                        );
                    } catch (error) {
                        console.warn(
                            'Stock valuation preferences could not be saved.',
                            error
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Restore table preferences
                |--------------------------------------------------------------------------
                */

                function restoreTablePreferences() {
                    let preferences = null;

                    try {
                        const storedPreferences =
                            window.localStorage.getItem(
                                valuationStorageKey
                            );

                        if (storedPreferences) {
                            preferences = JSON.parse(
                                storedPreferences
                            );
                        }
                    } catch (error) {
                        console.warn(
                            'Stock valuation preferences could not be restored.',
                            error
                        );
                    }

                    if (
                        !preferences ||
                        typeof preferences !== 'object'
                    ) {
                        return;
                    }

                    if (
                        search &&
                        typeof preferences.search === 'string'
                    ) {
                        search.value = preferences.search;
                    }

                    if (
                        stock && [
                            '',
                            'available',
                            'low',
                            'out'
                        ].includes(preferences.stock)
                    ) {
                        stock.value = preferences.stock;
                    }

                    if (
                        margin && [
                            '',
                            'positive',
                            'negative'
                        ].includes(preferences.margin)
                    ) {
                        margin.value = preferences.margin;
                    }

                    if (
                        sort && [
                            '',
                            'profit',
                            'value',
                            'stock'
                        ].includes(preferences.sort)
                    ) {
                        sort.value = preferences.sort;
                    }

                    if (
                        pageSizeSelect && [
                            '10',
                            '25',
                            '50',
                            '100'
                        ].includes(
                            String(preferences.pageSize)
                        )
                    ) {
                        pageSizeSelect.value =
                            String(preferences.pageSize);
                    }

                    const savedPage = Number.parseInt(
                        preferences.currentPage,
                        10
                    );

                    if (
                        Number.isInteger(savedPage) &&
                        savedPage > 0
                    ) {
                        currentPage = savedPage;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Clear saved table preferences
                |--------------------------------------------------------------------------
                */

                function clearTablePreferences() {
                    try {
                        window.localStorage.removeItem(
                            valuationStorageKey
                        );
                    } catch (error) {
                        console.warn(
                            'Stock valuation preferences could not be cleared.',
                            error
                        );
                    }
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
                        button.dataset.exportUrl || button.href;

                    const url = new URL(
                        baseUrl,
                        window.location.origin
                    );

                    const searchValue = search.value.trim();
                    const stockValue = stock.value;
                    const marginValue = margin.value;
                    const sortValue = sort.value;

                    /*
                     * Remove previous filter parameters before
                     * applying the current selections.
                     */
                    url.searchParams.delete('search');
                    url.searchParams.delete('stock');
                    url.searchParams.delete('margin');
                    url.searchParams.delete('sort');

                    if (searchValue !== '') {
                        url.searchParams.set(
                            'search',
                            searchValue
                        );
                    }

                    if (stockValue !== '') {
                        url.searchParams.set(
                            'stock',
                            stockValue
                        );
                    }

                    if (marginValue !== '') {
                        url.searchParams.set(
                            'margin',
                            marginValue
                        );
                    }
                    if (sortValue !== '') {
                        url.searchParams.set(
                            'sort',
                            sortValue
                        );
                    }
                    return url.toString();
                }

                /*
                |--------------------------------------------------------------------------
                | Synchronize export links
                |--------------------------------------------------------------------------
                */

                function updateExportLinks() {
                    if (exportCsvButton) {
                        exportCsvButton.href =
                            buildExportUrl(exportCsvButton);
                    }

                    if (exportExcelButton) {
                        exportExcelButton.href =
                            buildExportUrl(exportExcelButton);
                    }

                    if (exportPdfButton) {
                        exportPdfButton.href =
                            buildExportUrl(exportPdfButton);
                    }
                }


                /*
|--------------------------------------------------------------------------
| Get currently filtered rows
|--------------------------------------------------------------------------
*/

                function getVisibleFilteredRows() {
                    return rows.filter(function(row) {
                        return row.dataset.filterMatch === 'true';
                    });
                }

                /*
                |--------------------------------------------------------------------------
                | Create one pagination button
                |--------------------------------------------------------------------------
                */

                function createPaginationButton(
                    label,
                    page,
                    options = {}
                ) {
                    const button = document.createElement('button');

                    button.type = 'button';
                    button.className = 'pagination-button';

                    if (options.active) {
                        button.classList.add('active');
                    }

                    button.disabled = Boolean(options.disabled);

                    if (options.icon) {
                        button.innerHTML =
                            '<i class="' +
                            options.icon +
                            '"></i>' +
                            (
                                options.hideText ?
                                '' :
                                '<span>' + label + '</span>'
                            );
                    } else {
                        button.textContent = label;
                    }

                    button.addEventListener(
                        'click',
                        function() {
                            if (button.disabled) {
                                return;
                            }

                            currentPage = page;

                            applyPagination();
                            saveTablePreferences();
                        }
                    );

                    return button;
                }

                /*
                |--------------------------------------------------------------------------
                | Add pagination ellipsis
                |--------------------------------------------------------------------------
                */

                function createPaginationEllipsis() {
                    const ellipsis = document.createElement('span');

                    ellipsis.className = 'pagination-ellipsis';
                    ellipsis.textContent = '…';

                    return ellipsis;
                }

                /*
                |--------------------------------------------------------------------------
                | Render pagination buttons
                |--------------------------------------------------------------------------
                */

                function renderPaginationButtons(
                    totalPages
                ) {
                    if (!paginationButtons) {
                        return;
                    }

                    paginationButtons.innerHTML = '';

                    const previousButton = createPaginationButton(
                        'Previous',
                        Math.max(1, currentPage - 1), {
                            disabled: currentPage <= 1,
                            icon: 'fa-solid fa-chevron-left'
                        }
                    );

                    paginationButtons.appendChild(
                        previousButton
                    );

                    const pageNumbers = [];

                    if (totalPages <= 7) {
                        for (
                            let page = 1; page <= totalPages; page++
                        ) {
                            pageNumbers.push(page);
                        }
                    } else {
                        pageNumbers.push(1);

                        if (currentPage > 4) {
                            pageNumbers.push('ellipsis-left');
                        }

                        const startingPage = Math.max(
                            2,
                            currentPage - 1
                        );

                        const endingPage = Math.min(
                            totalPages - 1,
                            currentPage + 1
                        );

                        for (
                            let page = startingPage; page <= endingPage; page++
                        ) {
                            pageNumbers.push(page);
                        }

                        if (currentPage < totalPages - 3) {
                            pageNumbers.push('ellipsis-right');
                        }

                        pageNumbers.push(totalPages);
                    }

                    pageNumbers.forEach(function(page) {
                        if (
                            page === 'ellipsis-left' ||
                            page === 'ellipsis-right'
                        ) {
                            paginationButtons.appendChild(
                                createPaginationEllipsis()
                            );

                            return;
                        }

                        paginationButtons.appendChild(
                            createPaginationButton(
                                String(page),
                                page, {
                                    active: page === currentPage
                                }
                            )
                        );
                    });

                    const nextButton = createPaginationButton(
                        'Next',
                        Math.min(
                            totalPages,
                            currentPage + 1
                        ), {
                            disabled: totalPages === 0 ||
                                currentPage >= totalPages,

                            icon: 'fa-solid fa-chevron-right'
                        }
                    );

                    paginationButtons.appendChild(
                        nextButton
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Apply table pagination
                |--------------------------------------------------------------------------
                */

                function applyPagination() {
                    const filteredRows =
                        getVisibleFilteredRows();

                    const pageSize = Math.max(
                        1,
                        numberValue(
                            pageSizeSelect ?
                            pageSizeSelect.value :
                            10
                        )
                    );

                    const totalRows = filteredRows.length;

                    const totalPages = Math.max(
                        1,
                        Math.ceil(
                            totalRows / pageSize
                        )
                    );

                    if (currentPage > totalPages) {
                        currentPage = totalPages;
                    }

                    if (currentPage < 1) {
                        currentPage = 1;
                    }

                    const startingIndex =
                        (currentPage - 1) * pageSize;

                    const endingIndex = Math.min(
                        startingIndex + pageSize,
                        totalRows
                    );

                    rows.forEach(function(row) {
                        row.style.display = 'none';
                    });

                    filteredRows
                        .slice(
                            startingIndex,
                            endingIndex
                        )
                        .forEach(function(row) {
                            row.style.display = '';
                        });

                    if (paginationFrom) {
                        paginationFrom.textContent =
                            totalRows === 0 ?
                            '0' :
                            String(startingIndex + 1);
                    }

                    if (paginationTo) {
                        paginationTo.textContent =
                            String(endingIndex);
                    }

                    if (paginationTotal) {
                        paginationTotal.textContent =
                            totalRows.toLocaleString('en-GB');
                    }

                    if (pagination) {
                        pagination.style.display =
                            rows.length === 0 ?
                            'none' :
                            '';
                    }

                    renderPaginationButtons(
                        totalRows === 0 ?
                        0 :
                        totalPages
                    );

                    if (empty) {
                        empty.style.display =
                            totalRows === 0 &&
                            rows.length > 0 ?
                            '' :
                            'none';
                    }
                }
                /*
                |--------------------------------------------------------------------------
                | Update dashboard totals
                |--------------------------------------------------------------------------
                */

                function updateDashboard() {
                    let totalCost = 0;
                    let totalRetail = 0;
                    let totalProfit = 0;
                    let totalUnits = 0;

                    let healthy = 0;
                    let low = 0;
                    let out = 0;
                    let missing = 0;
                    let negative = 0;

                    let visibleProducts = 0;

                    rows.forEach(function(row) {
                        if (
                            row.dataset.filterMatch !== 'true'
                        ) {
                            return;
                        }

                        totalCost += numberValue(
                            row.dataset.totalCost
                        );

                        totalRetail += numberValue(
                            row.dataset.totalRetail
                        );

                        totalProfit += numberValue(
                            row.dataset.totalProfit
                        );

                        totalUnits += numberValue(
                            row.dataset.units
                        );

                        const stockQuantity = numberValue(
                            row.dataset.stockValue
                        );

                        const costPrice = numberValue(
                            row.dataset.costPrice
                        );

                        const sellingPrice = numberValue(
                            row.dataset.sellingPrice
                        );

                        if (stockQuantity <= 0) {
                            out++;
                        } else if (stockQuantity <= 5) {
                            low++;
                        } else {
                            healthy++;
                        }

                        if (costPrice <= 0) {
                            missing++;
                        }

                        if (sellingPrice < costPrice) {
                            negative++;
                        }

                        visibleProducts++;
                    });

                    const penalty =
                        out * 4 +
                        low * 2 +
                        missing * 3 +
                        negative * 5;

                    const maxPenalty =
                        Math.max(visibleProducts, 1) * 5;

                    const health =
                        visibleProducts === 0 ?
                        0 :
                        Math.max(
                            0,
                            Math.min(
                                100,
                                Math.round(
                                    100 -
                                    (penalty / maxPenalty) * 100
                                )
                            )
                        );

                    const totalCostCard =
                        document.getElementById('totalCostCard');

                    const totalRetailCard =
                        document.getElementById('totalRetailCard');

                    const totalProfitCard =
                        document.getElementById('totalProfitCard');

                    const totalUnitsCard =
                        document.getElementById('totalUnitsCard');

                    const footerTotalCost =
                        document.getElementById('footerTotalCost');

                    const footerTotalRetail =
                        document.getElementById('footerTotalRetail');

                    const footerTotalProfit =
                        document.getElementById('footerTotalProfit');

                    const footerUnits =
                        document.getElementById('footerUnits');

                    const footerProducts =
                        document.getElementById('footerProducts');

                    const summaryProducts =
                        document.getElementById('summaryProducts');

                    const healthyProductsElement =
                        document.getElementById('healthyProducts');

                    const lowStockProductsElement =
                        document.getElementById('lowStockProducts');

                    const outStockProductsElement =
                        document.getElementById('outStockProducts');

                    const missingCostProductsElement =
                        document.getElementById('missingCostProducts');

                    const negativeMarginProductsElement =
                        document.getElementById('negativeMarginProducts');

                    const healthScoreElement =
                        document.getElementById('healthScore');

                    const healthProgressElement =
                        document.getElementById('healthProgress');

                    if (totalCostCard) {
                        totalCostCard.textContent =
                            formatCurrency(totalCost);
                    }

                    if (totalRetailCard) {
                        totalRetailCard.textContent =
                            formatCurrency(totalRetail);
                    }

                    if (totalProfitCard) {
                        totalProfitCard.textContent =
                            formatCurrency(totalProfit);
                    }

                    if (totalUnitsCard) {
                        totalUnitsCard.textContent =
                            totalUnits.toLocaleString('en-GB');
                    }

                    if (footerTotalCost) {
                        footerTotalCost.textContent =
                            formatCurrency(totalCost);
                    }

                    if (footerTotalRetail) {
                        footerTotalRetail.textContent =
                            formatCurrency(totalRetail);
                    }

                    if (footerTotalProfit) {
                        footerTotalProfit.textContent =
                            formatCurrency(totalProfit);
                    }

                    if (footerUnits) {
                        footerUnits.textContent =
                            totalUnits.toLocaleString('en-GB');
                    }

                    if (footerProducts) {
                        footerProducts.textContent =
                            visibleProducts.toLocaleString('en-GB');
                    }

                    if (summaryProducts) {
                        summaryProducts.textContent =
                            visibleProducts.toLocaleString('en-GB');
                    }

                    if (healthyProductsElement) {
                        healthyProductsElement.textContent = healthy;
                    }

                    if (lowStockProductsElement) {
                        lowStockProductsElement.textContent = low;
                    }

                    if (outStockProductsElement) {
                        outStockProductsElement.textContent = out;
                    }

                    if (missingCostProductsElement) {
                        missingCostProductsElement.textContent = missing;
                    }

                    if (negativeMarginProductsElement) {
                        negativeMarginProductsElement.textContent = negative;
                    }

                    if (healthScoreElement) {
                        healthScoreElement.textContent =
                            health + '%';
                    }

                    if (healthProgressElement) {
                        healthProgressElement.style.width =
                            health + '%';

                        if (health >= 80) {
                            healthProgressElement.style.background =
                                'linear-gradient(90deg, #22c55e, #4f46e5)';
                        } else if (health >= 50) {
                            healthProgressElement.style.background =
                                'linear-gradient(90deg, #f59e0b, #f97316)';
                        } else {
                            healthProgressElement.style.background =
                                'linear-gradient(90deg, #ef4444, #dc2626)';
                        }
                    }
                    if (totalProfitCard) {
                        totalProfitCard.classList.toggle(
                            'negative',
                            totalProfit < 0
                        );
                    }

                    if (footerTotalProfit) {
                        footerTotalProfit.classList.toggle(
                            'negative',
                            totalProfit < 0
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Sort product rows
                |--------------------------------------------------------------------------
                */

                function sortRows() {
                    const selectedSort = sort.value;

                    rows.sort(function(firstRow, secondRow) {
                        if (selectedSort === 'profit') {
                            return (
                                numberValue(secondRow.dataset.profit) -
                                numberValue(firstRow.dataset.profit)
                            );
                        }

                        if (selectedSort === 'value') {
                            return (
                                numberValue(secondRow.dataset.value) -
                                numberValue(firstRow.dataset.value)
                            );
                        }

                        if (selectedSort === 'stock') {
                            return (
                                numberValue(secondRow.dataset.stockValue) -
                                numberValue(firstRow.dataset.stockValue)
                            );
                        }

                        return (
                            numberValue(firstRow.dataset.originalOrder) -
                            numberValue(secondRow.dataset.originalOrder)
                        );
                    });

                    rows.forEach(function(row) {
                        tbody.insertBefore(row, empty);
                    });
                }


                /*
|--------------------------------------------------------------------------
| Read saved views
|--------------------------------------------------------------------------
*/

                function getSavedViews() {
                    try {
                        const storedViews =
                            window.localStorage.getItem(
                                savedViewsStorageKey
                            );

                        if (!storedViews) {
                            return [];
                        }

                        const parsedViews = JSON.parse(
                            storedViews
                        );

                        return Array.isArray(parsedViews) ?
                            parsedViews : [];
                    } catch (error) {
                        console.warn(
                            'Saved valuation views could not be loaded.',
                            error
                        );

                        return [];
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Store saved views
                |--------------------------------------------------------------------------
                */

                function storeSavedViews(views) {
                    try {
                        window.localStorage.setItem(
                            savedViewsStorageKey,
                            JSON.stringify(views)
                        );

                        return true;
                    } catch (error) {
                        console.warn(
                            'Saved valuation views could not be stored.',
                            error
                        );

                        return false;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Render saved views dropdown
                |--------------------------------------------------------------------------
                */

                function renderSavedViews(
                    selectedId = ''
                ) {
                    if (!savedViewSelect) {
                        return;
                    }

                    const views = getSavedViews();

                    savedViewSelect.innerHTML = '';

                    const defaultOption =
                        document.createElement('option');

                    defaultOption.value = '';
                    defaultOption.textContent = 'Saved Views';

                    savedViewSelect.appendChild(
                        defaultOption
                    );

                    views
                        .sort(function(firstView, secondView) {
                            return firstView.name.localeCompare(
                                secondView.name
                            );
                        })
                        .forEach(function(view) {
                            const option =
                                document.createElement('option');

                            option.value = view.id;
                            option.textContent = view.name;

                            savedViewSelect.appendChild(option);
                        });

                    savedViewSelect.value = selectedId;

                    if (deleteSavedViewButton) {
                        deleteSavedViewButton.disabled =
                            savedViewSelect.value === '';
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Open saved view modal
                |--------------------------------------------------------------------------
                */

                function openSavedViewModal() {
                    if (
                        !savedViewModal ||
                        !savedViewName
                    ) {
                        return;
                    }

                    savedViewName.value = '';

                    if (savedViewModalError) {
                        savedViewModalError.hidden = true;
                        savedViewModalError.textContent = '';
                    }

                    savedViewModal.classList.add('open');
                    savedViewModal.setAttribute(
                        'aria-hidden',
                        'false'
                    );

                    document.body.style.overflow = 'hidden';

                    window.setTimeout(function() {
                        savedViewName.focus();
                    }, 50);
                }

                /*
                |--------------------------------------------------------------------------
                | Close saved view modal
                |--------------------------------------------------------------------------
                */

                function closeSavedViewModal() {
                    if (!savedViewModal) {
                        return;
                    }

                    savedViewModal.classList.remove('open');
                    savedViewModal.setAttribute(
                        'aria-hidden',
                        'true'
                    );

                    document.body.style.overflow = '';
                }

                /*
                |--------------------------------------------------------------------------
                | Show modal validation error
                |--------------------------------------------------------------------------
                */

                function showSavedViewError(message) {
                    if (!savedViewModalError) {
                        return;
                    }

                    savedViewModalError.textContent = message;
                    savedViewModalError.hidden = false;
                }

                /*
                |--------------------------------------------------------------------------
                | Save current filters as a named view
                |--------------------------------------------------------------------------
                */

                function saveCurrentView() {
                    if (!savedViewName) {
                        return;
                    }

                    const viewName =
                        savedViewName.value.trim();

                    if (viewName === '') {
                        showSavedViewError(
                            'Please enter a name for this saved view.'
                        );

                        savedViewName.focus();

                        return;
                    }

                    const views = getSavedViews();

                    const duplicateView = views.find(
                        function(view) {
                            return (
                                view.name.toLowerCase() ===
                                viewName.toLowerCase()
                            );
                        }
                    );

                    if (duplicateView) {
                        showSavedViewError(
                            'A saved view with this name already exists.'
                        );

                        savedViewName.focus();

                        return;
                    }

                    const newView = {
                        id: 'view-' +
                            Date.now() +
                            '-' +
                            Math.random()
                            .toString(36)
                            .slice(2, 8),

                        name: viewName,

                        filters: {
                            search: search ? search.value : '',
                            stock: stock ? stock.value : '',
                            margin: margin ? margin.value : '',
                            sort: sort ? sort.value : '',

                            pageSize: pageSizeSelect ?
                                pageSizeSelect.value : '10'
                        },

                        createdAt: new Date().toISOString()
                    };

                    views.push(newView);

                    if (!storeSavedViews(views)) {
                        showSavedViewError(
                            'The saved view could not be stored.'
                        );

                        return;
                    }

                    renderSavedViews(newView.id);
                    closeSavedViewModal();
                }

                /*
                |--------------------------------------------------------------------------
                | Apply selected saved view
                |--------------------------------------------------------------------------
                */

                function applySavedView(viewId) {
                    const views = getSavedViews();

                    const selectedView = views.find(
                        function(view) {
                            return view.id === viewId;
                        }
                    );

                    if (
                        !selectedView ||
                        !selectedView.filters
                    ) {
                        return;
                    }

                    const filters = selectedView.filters;

                    search.value =
                        typeof filters.search === 'string' ?
                        filters.search :
                        '';

                    stock.value = [
                            '',
                            'available',
                            'low',
                            'out'
                        ].includes(filters.stock) ?
                        filters.stock :
                        '';

                    margin.value = [
                            '',
                            'positive',
                            'negative'
                        ].includes(filters.margin) ?
                        filters.margin :
                        '';

                    sort.value = [
                            '',
                            'profit',
                            'value',
                            'stock'
                        ].includes(filters.sort) ?
                        filters.sort :
                        '';

                    if (
                        pageSizeSelect && [
                            '10',
                            '25',
                            '50',
                            '100'
                        ].includes(
                            String(filters.pageSize)
                        )
                    ) {
                        pageSizeSelect.value =
                            String(filters.pageSize);
                    }

                    currentPage = 1;

                    filterRows(true);
                }

                /*
                |--------------------------------------------------------------------------
                | Delete selected saved view
                |--------------------------------------------------------------------------
                */

                function deleteSelectedSavedView() {
                    if (
                        !savedViewSelect ||
                        savedViewSelect.value === ''
                    ) {
                        return;
                    }

                    const selectedId =
                        savedViewSelect.value;

                    const views = getSavedViews();

                    const selectedView = views.find(
                        function(view) {
                            return view.id === selectedId;
                        }
                    );

                    if (!selectedView) {
                        return;
                    }

                    const confirmed = window.confirm(
                        'Delete the saved view "' +
                        selectedView.name +
                        '"?'
                    );

                    if (!confirmed) {
                        return;
                    }

                    const remainingViews = views.filter(
                        function(view) {
                            return view.id !== selectedId;
                        }
                    );

                    storeSavedViews(remainingViews);
                    renderSavedViews('');
                }
                /*
|--------------------------------------------------------------------------
| Filter product rows
|--------------------------------------------------------------------------
*/

                function filterRows(
                    resetPage = true
                ) {
                    const keyword = search.value
                        .trim()
                        .toLowerCase();

                    const selectedStock = stock.value;
                    const selectedMargin = margin.value;

                    rows.forEach(function(row) {
                        const searchableText = (
                            row.dataset.search || ''
                        ).toLowerCase();

                        const stockStatus =
                            row.dataset.stock || '';

                        const marginValue = numberValue(
                            row.dataset.margin
                        );

                        const matchesSearch = !keyword ||
                            searchableText.includes(keyword);

                        const matchesStock = !selectedStock ||
                            stockStatus === selectedStock;

                        let matchesMargin = true;

                        if (selectedMargin === 'positive') {
                            matchesMargin =
                                marginValue > 0;
                        }

                        if (selectedMargin === 'negative') {
                            matchesMargin =
                                marginValue < 0;
                        }

                        const shouldShow =
                            matchesSearch &&
                            matchesStock &&
                            matchesMargin;

                        row.dataset.filterMatch =
                            shouldShow ?
                            'true' :
                            'false';
                    });

                    if (resetPage) {
                        currentPage = 1;
                    }

                    sortRows();
                    updateDashboard();
                    updateExportLinks();
                    applyPagination();
                    saveTablePreferences();
                }

                /*
                |--------------------------------------------------------------------------
                | Events
                |--------------------------------------------------------------------------
                */

                search.addEventListener(
                    'input',
                    function() {
                        filterRows(true);
                    }
                );

                stock.addEventListener(
                    'change',
                    function() {
                        filterRows(true);
                    }
                );

                margin.addEventListener(
                    'change',
                    function() {
                        filterRows(true);
                    }
                );

                sort.addEventListener(
                    'change',
                    function() {
                        filterRows(true);
                    }
                );

                if (pageSizeSelect) {
                    pageSizeSelect.addEventListener(
                        'change',
                        function() {
                            currentPage = 1;

                            applyPagination();
                            saveTablePreferences();
                        }
                    );
                }

                if (saveCurrentViewButton) {
                    saveCurrentViewButton.addEventListener(
                        'click',
                        openSavedViewModal
                    );
                }

                if (confirmSaveViewButton) {
                    confirmSaveViewButton.addEventListener(
                        'click',
                        saveCurrentView
                    );
                }

                if (savedViewName) {
                    savedViewName.addEventListener(
                        'keydown',
                        function(event) {
                            if (event.key === 'Enter') {
                                event.preventDefault();

                                saveCurrentView();
                            }
                        }
                    );
                }

                document
                    .querySelectorAll(
                        '[data-close-saved-view-modal]'
                    )
                    .forEach(function(button) {
                        button.addEventListener(
                            'click',
                            closeSavedViewModal
                        );
                    });

                document.addEventListener(
                    'keydown',
                    function(event) {
                        if (
                            event.key === 'Escape' &&
                            savedViewModal &&
                            savedViewModal.classList
                            .contains('open')
                        ) {
                            closeSavedViewModal();
                        }
                    }
                );

                if (savedViewSelect) {
                    savedViewSelect.addEventListener(
                        'change',
                        function() {
                            const selectedId =
                                savedViewSelect.value;

                            if (deleteSavedViewButton) {
                                deleteSavedViewButton.disabled =
                                    selectedId === '';
                            }

                            if (selectedId !== '') {
                                applySavedView(selectedId);
                            }
                        }
                    );
                }

                if (deleteSavedViewButton) {
                    deleteSavedViewButton.addEventListener(
                        'click',
                        deleteSelectedSavedView
                    );
                }

                reset.addEventListener(
                    'click',
                    function() {
                        search.value = '';
                        stock.value = '';
                        margin.value = '';
                        sort.value = '';

                        if (pageSizeSelect) {
                            pageSizeSelect.value = '10';
                        }

                        currentPage = 1;

                        clearTablePreferences();
                        renderSavedViews();
                        restoreTablePreferences();
                        filterRows(true);
                        search.focus();
                    }
                );

                /*
|--------------------------------------------------------------------------
| Refresh filters immediately before export
|--------------------------------------------------------------------------
*/

                [
                    exportCsvButton,
                    exportExcelButton,
                    exportPdfButton
                ].forEach(function(button) {
                    if (!button) {
                        return;
                    }

                    button.addEventListener(
                        'click',
                        function() {
                            button.href = buildExportUrl(button);
                        }
                    );
                });

                /*
                |--------------------------------------------------------------------------
                | Charts
                |--------------------------------------------------------------------------
                */

                if (typeof Chart !== 'undefined') {
                    const currencyTooltip = {
                        callbacks: {
                            label: function(context) {
                                const value = numberValue(context.raw);

                                return formatCurrency(value);
                            }
                        }
                    };

                    const inventoryValueCanvas =
                        document.getElementById('inventoryValueChart');

                    const profitCanvas =
                        document.getElementById('profitChart');

                    const stockCanvas =
                        document.getElementById('stockChart');

                    const marginCanvas =
                        document.getElementById('marginChart');

                    const categoryCanvas =
                        document.getElementById('categoryChart');

                    if (inventoryValueCanvas) {
                        new Chart(inventoryValueCanvas, {
                            type: 'bar',

                            data: {
                                labels: inventoryLabels,

                                datasets: [{
                                    label: 'Inventory Value',
                                    data: inventoryData,
                                    borderWidth: 1,
                                    borderRadius: 8,
                                    maxBarThickness: 48
                                }]
                            },

                            options: {
                                responsive: true,
                                maintainAspectRatio: false,

                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: currencyTooltip
                                },

                                scales: {
                                    y: {
                                        beginAtZero: true,

                                        ticks: {
                                            callback: function(value) {
                                                return '£' + Number(value).toLocaleString('en-GB');
                                            }
                                        }
                                    },

                                    x: {
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 0
                                        }
                                    }
                                }
                            }
                        });
                    }

                    if (profitCanvas) {
                        new Chart(profitCanvas, {
                            type: 'bar',

                            data: {
                                labels: profitLabels,

                                datasets: [{
                                    label: 'Expected Profit',
                                    data: profitData,
                                    borderWidth: 1,
                                    borderRadius: 8,
                                    maxBarThickness: 48
                                }]
                            },

                            options: {
                                responsive: true,
                                maintainAspectRatio: false,

                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: currencyTooltip
                                },

                                scales: {
                                    y: {
                                        beginAtZero: true,

                                        ticks: {
                                            callback: function(value) {
                                                return '£' + Number(value).toLocaleString('en-GB');
                                            }
                                        }
                                    },

                                    x: {
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 0
                                        }
                                    }
                                }
                            }
                        });
                    }

                    if (stockCanvas) {
                        new Chart(stockCanvas, {
                            type: 'doughnut',

                            data: {
                                labels: stockLabels,

                                datasets: [{
                                    label: 'Products',
                                    data: stockData,
                                    borderWidth: 2
                                }]
                            },

                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '66%',

                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }
                        });
                    }

                    if (marginCanvas) {
                        new Chart(marginCanvas, {
                            type: 'pie',

                            data: {
                                labels: marginLabels,

                                datasets: [{
                                    label: 'Products',
                                    data: marginData,
                                    borderWidth: 2
                                }]
                            },

                            options: {
                                responsive: true,
                                maintainAspectRatio: false,

                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }
                        });
                    }

                    if (categoryCanvas) {
                        new Chart(categoryCanvas, {
                            type: 'bar',

                            data: {
                                labels: categoryLabels,

                                datasets: [{
                                    label: 'Inventory Value',
                                    data: categoryData,
                                    borderWidth: 1,
                                    borderRadius: 8,
                                    maxBarThickness: 42
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
                                    tooltip: currencyTooltip
                                },

                                scales: {
                                    x: {
                                        beginAtZero: true,

                                        ticks: {
                                            callback: function(value) {
                                                return '£' + Number(value).toLocaleString('en-GB');
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                } else {
                    console.error('Chart.js failed to load.');
                }

                filterRows();
                updateExportLinks();
            });
        </script>

        @endpush