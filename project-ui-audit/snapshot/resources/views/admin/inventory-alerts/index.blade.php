@extends('admin.layouts.app')

@section('title', 'Inventory Alerts')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Current page statistics
    |--------------------------------------------------------------------------
    */

    $visibleAlerts = $alerts->getCollection();

    $visibleActiveCount = $visibleAlerts
        ->filter(fn ($alert) => $alert->isActive())
        ->count();

    $visibleResolvedCount = $visibleAlerts
        ->reject(fn ($alert) => $alert->isActive())
        ->count();

    $startNumber = $alerts->firstItem() ?? 0;
    $endNumber = $alerts->lastItem() ?? 0;

    $filtersActive =
        request()->filled('status')
        || request()->filled('type');

    $activeAlertCount = (int) $lowStockCount + (int) $outOfStockCount;
@endphp

<div class="inventory-alerts-page">

    {{-- Success notification --}}
    @if(session('success'))

        <div
            class="inventory-alert-notification"
            data-alert-notification
        >
            <span class="inventory-alert-notification-icon">
                <i class="fa-solid fa-circle-check"></i>
            </span>

            <div class="inventory-alert-notification-content">
                <strong>Action completed</strong>
                <span>{{ session('success') }}</span>
            </div>

            <button
                type="button"
                class="inventory-alert-notification-close"
                aria-label="Close notification"
                data-close-notification
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

    @endif

    {{-- Page header --}}
    <section class="inventory-alert-page-header">

        <div class="inventory-alert-page-heading">

            <span class="inventory-alert-eyebrow">
                Inventory management
            </span>

            <h1>Inventory Alerts</h1>

            <p>
                Monitor products that are running low or are completely out
                of stock, and resolve alerts after inventory is replenished.
            </p>

        </div>

        <div class="inventory-alert-header-actions">

            <div class="inventory-alert-total">

                <span class="inventory-alert-total-icon">
                    <i class="fa-solid fa-bell"></i>
                </span>

                <span class="inventory-alert-total-content">
                    <small>Active alerts</small>
                    <strong>{{ number_format($activeAlertCount) }}</strong>
                </span>

            </div>

            <a
                href="{{ route('admin.inventory-history.index') }}"
                class="inventory-alert-button inventory-alert-button-secondary"
            >
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>History</span>
            </a>

            <a
                href="{{ route('admin.inventory-reports.index') }}"
                class="inventory-alert-button inventory-alert-button-primary"
            >
                <i class="fa-solid fa-chart-column"></i>
                <span>Reports</span>
            </a>

        </div>

    </section>

    {{-- Summary cards --}}
    <section class="inventory-alert-stat-grid">

        <article class="inventory-alert-stat-card alert-stat-orange">

            <div class="inventory-alert-stat-heading">

                <span class="inventory-alert-stat-icon">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>

                <span class="inventory-alert-stat-label">
                    Low stock
                </span>

            </div>

            <strong class="inventory-alert-stat-value">
                {{ number_format($lowStockCount) }}
            </strong>

            <span class="inventory-alert-stat-meta">
                Products approaching their threshold
            </span>

        </article>

        <article class="inventory-alert-stat-card alert-stat-red">

            <div class="inventory-alert-stat-heading">

                <span class="inventory-alert-stat-icon">
                    <i class="fa-solid fa-circle-xmark"></i>
                </span>

                <span class="inventory-alert-stat-label">
                    Out of stock
                </span>

            </div>

            <strong class="inventory-alert-stat-value">
                {{ number_format($outOfStockCount) }}
            </strong>

            <span class="inventory-alert-stat-meta">
                Products with no available stock
            </span>

        </article>

        <article class="inventory-alert-stat-card alert-stat-purple">

            <div class="inventory-alert-stat-heading">

                <span class="inventory-alert-stat-icon">
                    <i class="fa-solid fa-list-check"></i>
                </span>

                <span class="inventory-alert-stat-label">
                    Total records
                </span>

            </div>

            <strong class="inventory-alert-stat-value">
                {{ number_format($alerts->total()) }}
            </strong>

            <span class="inventory-alert-stat-meta">
                Alerts matching the current filters
            </span>

        </article>

        <article class="inventory-alert-stat-card alert-stat-green">

            <div class="inventory-alert-stat-heading">

                <span class="inventory-alert-stat-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </span>

                <span class="inventory-alert-stat-label">
                    Resolved
                </span>

            </div>

            <strong class="inventory-alert-stat-value">
                {{ number_format($visibleResolvedCount) }}
            </strong>

            <span class="inventory-alert-stat-meta">
                Resolved alerts on this page
            </span>

        </article>

    </section>

    {{-- Filter panel --}}
    <section class="inventory-alert-panel inventory-alert-filter-panel">

        <div class="inventory-alert-panel-heading">

            <div>

                <span class="inventory-alert-section-label">
                    Search and filters
                </span>

                <h2>Filter inventory alerts</h2>

                <p>
                    Narrow the results by alert status or inventory condition.
                </p>

            </div>

            @if($filtersActive)

                <span class="inventory-alert-filter-active">
                    <i class="fa-solid fa-filter"></i>
                    Filters active
                </span>

            @endif

        </div>

        <form
            method="GET"
            action="{{ route('admin.inventory-alerts.index') }}"
            class="inventory-alert-filter-form"
        >

            <div class="inventory-alert-field">

                <label for="alertStatus">
                    Alert status
                </label>

                <div class="inventory-alert-input-wrap">

                    <span class="inventory-alert-input-icon">
                        <i class="fa-solid fa-circle-dot"></i>
                    </span>

                    <select
                        name="status"
                        id="alertStatus"
                        class="inventory-alert-input inventory-alert-input-icon-padding"
                    >
                        <option value="">
                            All statuses
                        </option>

                        <option
                            value="active"
                            @selected(request('status') === 'active')
                        >
                            Active
                        </option>

                        <option
                            value="resolved"
                            @selected(request('status') === 'resolved')
                        >
                            Resolved
                        </option>
                    </select>

                    <span class="inventory-alert-select-arrow">
                        <i class="fa-solid fa-chevron-down"></i>
                    </span>

                </div>

            </div>

            <div class="inventory-alert-field">

                <label for="alertType">
                    Alert type
                </label>

                <div class="inventory-alert-input-wrap">

                    <span class="inventory-alert-input-icon">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </span>

                    <select
                        name="type"
                        id="alertType"
                        class="inventory-alert-input inventory-alert-input-icon-padding"
                    >
                        <option value="">
                            All alert types
                        </option>

                        <option
                            value="low_stock"
                            @selected(request('type') === 'low_stock')
                        >
                            Low stock
                        </option>

                        <option
                            value="out_of_stock"
                            @selected(request('type') === 'out_of_stock')
                        >
                            Out of stock
                        </option>
                    </select>

                    <span class="inventory-alert-select-arrow">
                        <i class="fa-solid fa-chevron-down"></i>
                    </span>

                </div>

            </div>

            <div class="inventory-alert-filter-summary">

                <span class="inventory-alert-filter-summary-icon">
                    <i class="fa-solid fa-bell"></i>
                </span>

                <span>
                    <small>Visible alerts</small>
                    <strong>{{ number_format($alerts->count()) }}</strong>
                </span>

            </div>

            <div class="inventory-alert-filter-actions">

                <button
                    type="submit"
                    class="inventory-alert-button inventory-alert-button-primary"
                >
                    <i class="fa-solid fa-filter"></i>
                    <span>Apply filters</span>
                </button>

                <a
                    href="{{ route('admin.inventory-alerts.index') }}"
                    class="inventory-alert-button inventory-alert-button-secondary"
                >
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Reset</span>
                </a>

            </div>

        </form>

    </section>

    {{-- Alerts table --}}
    <section class="inventory-alert-panel inventory-alert-table-panel">

        <div class="inventory-alert-panel-heading">

            <div>

                <span class="inventory-alert-section-label">
                    Inventory monitoring
                </span>

                <h2>Alert records</h2>

                <p>
                    @if($alerts->total() > 0)
                        Showing {{ number_format($startNumber) }}
                        to {{ number_format($endNumber) }}
                        of {{ number_format($alerts->total()) }} alerts.
                    @else
                        No alerts match the selected filters.
                    @endif
                </p>

            </div>

            <div class="inventory-alert-table-meta">

                <span>
                    {{ number_format($visibleActiveCount) }} active
                </span>

                <span class="inventory-alert-table-meta-divider"></span>

                <span>
                    Page {{ $alerts->currentPage() }}
                </span>

            </div>

        </div>

        <div class="inventory-alert-table-scroll">

            <table class="inventory-alert-table">

                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Alert type</th>
                        <th>Stock health</th>
                        <th>Threshold</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($alerts as $alert)

                        @php
                            $isLowStock = $alert->isLowStock();
                            $isActive = $alert->isActive();

                            $product = $alert->product ?? null;
                            $variant = $alert->variant ?? null;

                            $sku =
                                $variant?->sku
                                ?? $product?->sku
                                ?? null;

                            $stockLevel = (int) $alert->stock_level;
                            $threshold = max(1, (int) $alert->threshold);

                            $stockPercentage = min(
                                100,
                                max(0, ($stockLevel / $threshold) * 100)
                            );

                            $stockClass = $isLowStock
                                ? 'inventory-stock-low'
                                : 'inventory-stock-empty';
                        @endphp

                        <tr>

                            <td>

                                <div class="inventory-alert-product">

                                    <span
                                        class="
                                            inventory-alert-product-icon
                                            {{ $isLowStock
                                                ? 'inventory-product-warning'
                                                : 'inventory-product-danger' }}
                                        "
                                    >
                                        <i class="fa-solid fa-box"></i>
                                    </span>

                                    <span class="inventory-alert-product-details">

                                        <strong>
                                            {{ $alert->item_name }}
                                        </strong>

                                        <small>
                                            @if($sku)
                                                SKU: {{ $sku }}
                                            @elseif($variant)
                                                Product variant
                                            @else
                                                Product inventory
                                            @endif
                                        </small>

                                    </span>

                                </div>

                            </td>

                            <td>

                                @if($isLowStock)

                                    <span class="inventory-alert-type alert-type-low">

                                        <span class="inventory-alert-type-dot"></span>

                                        Low stock

                                    </span>

                                @else

                                    <span class="inventory-alert-type alert-type-out">

                                        <span class="inventory-alert-type-dot"></span>

                                        Out of stock

                                    </span>

                                @endif

                            </td>

                            <td>

                                <div class="inventory-alert-stock-health">

                                    <div class="inventory-alert-stock-values">

                                        <strong>
                                            {{ number_format($stockLevel) }}
                                            units
                                        </strong>

                                        <span>
                                            of {{ number_format($threshold) }}
                                        </span>

                                    </div>

                                    <div class="inventory-alert-stock-track">

                                        <span
                                            class="inventory-alert-stock-progress {{ $stockClass }}"
                                            style="width: {{ $stockPercentage }}%"
                                        ></span>

                                    </div>

                                </div>

                            </td>

                            <td>

                                <span class="inventory-alert-threshold">
                                    <i class="fa-solid fa-gauge-simple"></i>
                                    {{ number_format($alert->threshold) }}
                                </span>

                            </td>

                            <td>

                                @if($isActive)

                                    <span class="inventory-alert-status alert-status-active">
                                        <i class="fa-solid fa-circle"></i>
                                        Active
                                    </span>

                                @else

                                    <span class="inventory-alert-status alert-status-resolved">
                                        <i class="fa-solid fa-circle-check"></i>
                                        Resolved
                                    </span>

                                @endif

                            </td>

                            <td>

                                <span class="inventory-alert-date">
                                    {{ $alert->created_at->format('d M Y') }}
                                </span>

                                <span class="inventory-alert-time">
                                    <i class="fa-regular fa-clock"></i>
                                    {{ $alert->created_at->format('h:i A') }}
                                </span>

                            </td>

                            <td>

                                @if($isActive)

                                    <form
                                        action="{{ route(
                                            'admin.inventory-alerts.resolve',
                                            $alert
                                        ) }}"
                                        method="POST"
                                        class="inventory-alert-resolve-form"
                                        data-resolve-form
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="inventory-alert-resolve-button"
                                        >
                                            <i class="fa-solid fa-check"></i>
                                            <span>Resolve</span>
                                        </button>

                                    </form>

                                @else

                                    <span class="inventory-alert-resolved-button">
                                        <i class="fa-solid fa-check-double"></i>
                                        Resolved
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="inventory-alert-empty-cell"
                            >

                                <div class="inventory-alert-empty-state">

                                    <span class="inventory-alert-empty-icon">
                                        <i class="fa-solid fa-bell-slash"></i>
                                    </span>

                                    <h3>No inventory alerts found</h3>

                                    <p>
                                        There are no alerts matching the
                                        selected status and type filters.
                                    </p>

                                    <a
                                        href="{{ route('admin.inventory-alerts.index') }}"
                                        class="inventory-alert-button inventory-alert-button-secondary"
                                    >
                                        <i class="fa-solid fa-rotate-left"></i>
                                        Clear filters
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Pagination --}}
        @if($alerts->hasPages())

            <div class="inventory-alert-pagination">

                <div class="inventory-alert-pagination-info">

                    Showing
                    <strong>{{ number_format($startNumber) }}</strong>
                    to
                    <strong>{{ number_format($endNumber) }}</strong>
                    of
                    <strong>{{ number_format($alerts->total()) }}</strong>

                </div>

                <nav
                    class="inventory-alert-pagination-links"
                    aria-label="Inventory alert pagination"
                >

                    @if($alerts->onFirstPage())

                        <span class="inventory-alert-page-button disabled">
                            <i class="fa-solid fa-chevron-left"></i>
                            Previous
                        </span>

                    @else

                        <a
                            href="{{ $alerts->previousPageUrl() }}"
                            class="inventory-alert-page-button"
                        >
                            <i class="fa-solid fa-chevron-left"></i>
                            Previous
                        </a>

                    @endif

                    <div class="inventory-alert-page-numbers">

                        @php
                            $currentPage = $alerts->currentPage();
                            $lastPage = $alerts->lastPage();

                            $pageStart = max(1, $currentPage - 2);
                            $pageEnd = min($lastPage, $currentPage + 2);
                        @endphp

                        @if($pageStart > 1)

                            <a
                                href="{{ $alerts->url(1) }}"
                                class="inventory-alert-page-number"
                            >
                                1
                            </a>

                            @if($pageStart > 2)
                                <span class="inventory-alert-page-dots">…</span>
                            @endif

                        @endif

                        @for($page = $pageStart; $page <= $pageEnd; $page++)

                            @if($page === $currentPage)

                                <span class="inventory-alert-page-number active">
                                    {{ $page }}
                                </span>

                            @else

                                <a
                                    href="{{ $alerts->url($page) }}"
                                    class="inventory-alert-page-number"
                                >
                                    {{ $page }}
                                </a>

                            @endif

                        @endfor

                        @if($pageEnd < $lastPage)

                            @if($pageEnd < $lastPage - 1)
                                <span class="inventory-alert-page-dots">…</span>
                            @endif

                            <a
                                href="{{ $alerts->url($lastPage) }}"
                                class="inventory-alert-page-number"
                            >
                                {{ $lastPage }}
                            </a>

                        @endif

                    </div>

                    @if($alerts->hasMorePages())

                        <a
                            href="{{ $alerts->nextPageUrl() }}"
                            class="inventory-alert-page-button"
                        >
                            Next
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>

                    @else

                        <span class="inventory-alert-page-button disabled">
                            Next
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>

                    @endif

                </nav>

            </div>

        @endif

    </section>

</div>

@endsection

@push('page-styles')
<style>
    /*
    |--------------------------------------------------------------------------
    | Inventory Alerts
    |--------------------------------------------------------------------------
    */

    .inventory-alerts-page {
        --alerts-primary: #6558f5;
        --alerts-primary-dark: #5547ec;
        --alerts-primary-soft: #f0eeff;
        --alerts-text: #171d2d;
        --alerts-muted: #758099;
        --alerts-border: #e4e8f0;
        --alerts-soft-border: #edf0f5;
        --alerts-card: #ffffff;
        --alerts-green: #16875d;
        --alerts-green-soft: #eaf9f2;
        --alerts-red: #d84646;
        --alerts-red-soft: #fff0f0;
        --alerts-orange: #c56b11;
        --alerts-orange-soft: #fff5e7;
        --alerts-blue: #3375d6;
        --alerts-blue-soft: #edf5ff;
        --alerts-navy: #101729;

        display: flex;
        flex-direction: column;
        gap: 20px;
        min-width: 0;
    }

    .inventory-alerts-page *,
    .inventory-alerts-page *::before,
    .inventory-alerts-page *::after {
        box-sizing: border-box;
    }

    /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    */

    .inventory-alert-notification {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid #cdeedf;
        border-radius: 12px;
        background: var(--alerts-green-soft);
        color: var(--alerts-green);
        box-shadow: 0 5px 16px rgba(22, 135, 93, 0.08);
    }

    .inventory-alert-notification-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: #ffffff;
        font-size: 14px;
    }

    .inventory-alert-notification-content {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 3px;
    }

    .inventory-alert-notification-content strong {
        color: #126e4d;
        font-size: 11px;
    }

    .inventory-alert-notification-content span {
        color: #43846d;
        font-size: 10px;
    }

    .inventory-alert-notification-close {
        width: 30px;
        height: 30px;
        border: 0;
        border-radius: 8px;
        background: transparent;
        color: #43846d;
        cursor: pointer;
    }

    .inventory-alert-notification-close:hover {
        background: rgba(22, 135, 93, 0.1);
    }

    /*
    |--------------------------------------------------------------------------
    | Page header
    |--------------------------------------------------------------------------
    */

    .inventory-alert-page-header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        min-height: 150px;
        padding: 28px 30px;
        border: 1px solid var(--alerts-border);
        border-radius: 16px;
        background:
            radial-gradient(
                circle at 92% 10%,
                rgba(101, 88, 245, 0.13),
                transparent 32%
            ),
            linear-gradient(135deg, #ffffff 0%, #f9f8ff 100%);
        box-shadow: 0 8px 24px rgba(24, 31, 52, 0.045);
        overflow: hidden;
    }

    .inventory-alert-page-header::before {
        content: "";
        position: absolute;
        top: -80px;
        right: -35px;
        width: 210px;
        height: 210px;
        border: 30px solid rgba(101, 88, 245, 0.045);
        border-radius: 50%;
        pointer-events: none;
    }

    .inventory-alert-page-heading,
    .inventory-alert-header-actions {
        position: relative;
        z-index: 1;
    }

    .inventory-alert-eyebrow,
    .inventory-alert-section-label {
        display: block;
        margin-bottom: 7px;
        color: var(--alerts-primary);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .inventory-alert-page-heading {
        min-width: 0;
    }

    .inventory-alert-page-heading h1 {
        margin: 0 0 8px;
        color: var(--alerts-text);
        font-size: clamp(27px, 3vw, 35px);
        font-weight: 800;
        line-height: 1.12;
        letter-spacing: -0.04em;
    }

    .inventory-alert-page-heading p {
        max-width: 660px;
        margin: 0;
        color: var(--alerts-muted);
        font-size: 13px;
        line-height: 1.65;
    }

    .inventory-alert-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .inventory-alert-total {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 50px;
        padding: 8px 14px 8px 9px;
        border: 1px solid var(--alerts-border);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 4px 12px rgba(24, 31, 52, 0.04);
    }

    .inventory-alert-total-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: var(--alerts-red-soft);
        color: var(--alerts-red);
        font-size: 13px;
    }

    .inventory-alert-total-content {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .inventory-alert-total-content small {
        color: var(--alerts-muted);
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .inventory-alert-total-content strong {
        color: var(--alerts-text);
        font-size: 16px;
        line-height: 1;
    }

    /*
    |--------------------------------------------------------------------------
    | Buttons
    |--------------------------------------------------------------------------
    */

    .inventory-alert-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 10px 15px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-family: inherit;
        font-size: 11px;
        font-weight: 800;
        line-height: 1;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
        transition:
            transform 0.2s ease,
            border-color 0.2s ease,
            background-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .inventory-alert-button:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .inventory-alert-button-primary {
        border-color: var(--alerts-primary);
        background: var(--alerts-primary);
        color: #ffffff;
        box-shadow: 0 8px 16px rgba(101, 88, 245, 0.18);
    }

    .inventory-alert-button-primary:hover {
        border-color: var(--alerts-primary-dark);
        background: var(--alerts-primary-dark);
        color: #ffffff;
    }

    .inventory-alert-button-secondary {
        border-color: #dce1ea;
        background: #ffffff;
        color: #4d576c;
    }

    .inventory-alert-button-secondary:hover {
        border-color: #c8cfdb;
        background: #f9fafc;
        color: var(--alerts-text);
    }

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    .inventory-alert-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .inventory-alert-stat-card {
        position: relative;
        min-width: 0;
        min-height: 132px;
        padding: 18px;
        border: 1px solid var(--alerts-border);
        border-radius: 14px;
        background: var(--alerts-card);
        box-shadow: 0 6px 18px rgba(24, 31, 52, 0.04);
        overflow: hidden;
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .inventory-alert-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(24, 31, 52, 0.07);
    }

    .inventory-alert-stat-card::after {
        content: "";
        position: absolute;
        top: -32px;
        right: -32px;
        width: 92px;
        height: 92px;
        border-radius: 50%;
        opacity: 0.75;
    }

    .alert-stat-orange::after {
        background: var(--alerts-orange-soft);
    }

    .alert-stat-red::after {
        background: var(--alerts-red-soft);
    }

    .alert-stat-purple::after {
        background: var(--alerts-primary-soft);
    }

    .alert-stat-green::after {
        background: var(--alerts-green-soft);
    }

    .inventory-alert-stat-heading,
    .inventory-alert-stat-value,
    .inventory-alert-stat-meta {
        position: relative;
        z-index: 1;
    }

    .inventory-alert-stat-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 15px;
    }

    .inventory-alert-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 9px;
        font-size: 12px;
    }

    .alert-stat-orange .inventory-alert-stat-icon {
        background: var(--alerts-orange-soft);
        color: var(--alerts-orange);
    }

    .alert-stat-red .inventory-alert-stat-icon {
        background: var(--alerts-red-soft);
        color: var(--alerts-red);
    }

    .alert-stat-purple .inventory-alert-stat-icon {
        background: var(--alerts-primary-soft);
        color: var(--alerts-primary);
    }

    .alert-stat-green .inventory-alert-stat-icon {
        background: var(--alerts-green-soft);
        color: var(--alerts-green);
    }

    .inventory-alert-stat-label {
        color: #5f697d;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.025em;
        text-transform: uppercase;
    }

    .inventory-alert-stat-value {
        display: block;
        margin-bottom: 7px;
        color: var(--alerts-text);
        font-size: 27px;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.035em;
    }

    .inventory-alert-stat-meta {
        display: block;
        color: #8a93a5;
        font-size: 10px;
        line-height: 1.5;
    }

    /*
    |--------------------------------------------------------------------------
    | Panels
    |--------------------------------------------------------------------------
    */

    .inventory-alert-panel {
        min-width: 0;
        border: 1px solid var(--alerts-border);
        border-radius: 15px;
        background: var(--alerts-card);
        box-shadow: 0 7px 22px rgba(24, 31, 52, 0.04);
        overflow: hidden;
    }

    .inventory-alert-panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 19px 21px;
        border-bottom: 1px solid var(--alerts-soft-border);
        background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
    }

    .inventory-alert-panel-heading > div:first-child {
        min-width: 0;
    }

    .inventory-alert-panel-heading h2 {
        margin: 0 0 5px;
        color: var(--alerts-text);
        font-size: 15px;
        font-weight: 800;
        line-height: 1.3;
        letter-spacing: -0.02em;
    }

    .inventory-alert-panel-heading p {
        margin: 0;
        color: var(--alerts-muted);
        font-size: 11px;
        line-height: 1.55;
    }

    .inventory-alert-filter-active {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        flex-shrink: 0;
        padding: 7px 10px;
        border-radius: 999px;
        background: var(--alerts-primary-soft);
        color: var(--alerts-primary);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 0.035em;
        text-transform: uppercase;
    }

    /*
    |--------------------------------------------------------------------------
    | Filter form
    |--------------------------------------------------------------------------
    */

    .inventory-alert-filter-form {
        display: grid;
        grid-template-columns:
            minmax(190px, 1fr)
            minmax(190px, 1fr)
            minmax(150px, 0.7fr)
            auto;
        align-items: end;
        gap: 13px;
        padding: 20px 21px;
    }

    .inventory-alert-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        min-width: 0;
    }

    .inventory-alert-field label {
        color: #5d6678;
        font-size: 10px;
        font-weight: 800;
    }

    .inventory-alert-input-wrap {
        position: relative;
        min-width: 0;
    }

    .inventory-alert-input {
        width: 100%;
        min-height: 42px;
        padding: 10px 34px 10px 12px;
        border: 1px solid #dce1e9;
        border-radius: 9px;
        background: #ffffff;
        color: var(--alerts-text);
        font-family: inherit;
        font-size: 11px;
        outline: none;
        appearance: none;
        cursor: pointer;
        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .inventory-alert-input:hover {
        border-color: #c8cfda;
    }

    .inventory-alert-input:focus {
        border-color: var(--alerts-primary);
        box-shadow: 0 0 0 3px rgba(101, 88, 245, 0.1);
    }

    .inventory-alert-input-icon-padding {
        padding-left: 37px;
    }

    .inventory-alert-input-icon {
        position: absolute;
        top: 50%;
        left: 13px;
        z-index: 1;
        color: #929bad;
        font-size: 11px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .inventory-alert-select-arrow {
        position: absolute;
        top: 50%;
        right: 13px;
        color: #929bad;
        font-size: 9px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .inventory-alert-filter-summary {
        display: flex;
        align-items: center;
        gap: 9px;
        min-height: 42px;
        padding: 7px 11px;
        border: 1px solid var(--alerts-border);
        border-radius: 9px;
        background: #f9fafc;
    }

    .inventory-alert-filter-summary-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: var(--alerts-primary-soft);
        color: var(--alerts-primary);
        font-size: 10px;
    }

    .inventory-alert-filter-summary > span:last-child {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .inventory-alert-filter-summary small {
        color: #8b94a5;
        font-size: 8px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .inventory-alert-filter-summary strong {
        color: var(--alerts-text);
        font-size: 12px;
    }

    .inventory-alert-filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /*
    |--------------------------------------------------------------------------
    | Table heading
    |--------------------------------------------------------------------------
    */

    .inventory-alert-table-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
        color: #7a8497;
        font-size: 10px;
        font-weight: 700;
    }

    .inventory-alert-table-meta-divider {
        width: 1px;
        height: 15px;
        background: var(--alerts-border);
    }

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    .inventory-alert-table-scroll {
        width: 100%;
        min-width: 0;
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: #cbd2dd transparent;
    }

    .inventory-alert-table-scroll::-webkit-scrollbar {
        height: 8px;
    }

    .inventory-alert-table-scroll::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #cbd2dd;
    }

    .inventory-alert-table {
        width: 100%;
        min-width: 1080px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .inventory-alert-table th,
    .inventory-alert-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--alerts-soft-border);
        text-align: left;
        vertical-align: middle;
    }

    .inventory-alert-table th {
        background: #f8f9fc;
        color: #707a8e;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 0.055em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .inventory-alert-table td {
        color: #4c566a;
        font-size: 11px;
        line-height: 1.5;
    }

    .inventory-alert-table tbody tr {
        transition: background-color 0.18s ease;
    }

    .inventory-alert-table tbody tr:hover {
        background: #fbfbfe;
    }

    .inventory-alert-table tbody tr:last-child td {
        border-bottom: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

    .inventory-alert-product {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 190px;
    }

    .inventory-alert-product-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        font-size: 11px;
    }

    .inventory-product-warning {
        background: var(--alerts-orange-soft);
        color: var(--alerts-orange);
    }

    .inventory-product-danger {
        background: var(--alerts-red-soft);
        color: var(--alerts-red);
    }

    .inventory-alert-product-details {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 0;
    }

    .inventory-alert-product-details strong {
        max-width: 190px;
        overflow: hidden;
        color: var(--alerts-text);
        font-size: 11px;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .inventory-alert-product-details small {
        color: #939cad;
        font-size: 9px;
    }

    /*
    |--------------------------------------------------------------------------
    | Alert type
    |--------------------------------------------------------------------------
    */

    .inventory-alert-type {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 25px;
        padding: 5px 9px;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .inventory-alert-type-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
    }

    .alert-type-low {
        border-color: #f7d9ad;
        background: var(--alerts-orange-soft);
        color: var(--alerts-orange);
    }

    .alert-type-out {
        border-color: #ffd3d3;
        background: var(--alerts-red-soft);
        color: var(--alerts-red);
    }

    /*
    |--------------------------------------------------------------------------
    | Stock health
    |--------------------------------------------------------------------------
    */

    .inventory-alert-stock-health {
        width: 145px;
    }

    .inventory-alert-stock-values {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 7px;
    }

    .inventory-alert-stock-values strong {
        color: #333b4d;
        font-size: 10px;
    }

    .inventory-alert-stock-values span {
        color: #99a1b0;
        font-size: 8px;
    }

    .inventory-alert-stock-track {
        width: 100%;
        height: 5px;
        border-radius: 999px;
        background: #edf0f5;
        overflow: hidden;
    }

    .inventory-alert-stock-progress {
        display: block;
        height: 100%;
        min-width: 3px;
        border-radius: inherit;
    }

    .inventory-stock-low {
        background: #f3a43b;
    }

    .inventory-stock-empty {
        background: var(--alerts-red);
    }

    .inventory-alert-threshold {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 27px;
        padding: 5px 8px;
        border-radius: 7px;
        background: var(--alerts-blue-soft);
        color: var(--alerts-blue);
        font-size: 10px;
        font-weight: 800;
    }

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    .inventory-alert-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 25px;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .inventory-alert-status i {
        font-size: 6px;
    }

    .alert-status-active {
        background: var(--alerts-red-soft);
        color: var(--alerts-red);
    }

    .alert-status-resolved {
        background: var(--alerts-green-soft);
        color: var(--alerts-green);
    }

    /*
    |--------------------------------------------------------------------------
    | Date and actions
    |--------------------------------------------------------------------------
    */

    .inventory-alert-date {
        display: block;
        margin-bottom: 4px;
        color: #31394a;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .inventory-alert-time {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #929bad;
        font-size: 9px;
        white-space: nowrap;
    }

    .inventory-alert-resolve-form {
        margin: 0;
    }

    .inventory-alert-resolve-button,
    .inventory-alert-resolved-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 31px;
        padding: 7px 10px;
        border-radius: 8px;
        font-family: inherit;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .inventory-alert-resolve-button {
        border: 1px solid #cdeedf;
        background: var(--alerts-green-soft);
        color: var(--alerts-green);
        cursor: pointer;
        transition:
            transform 0.2s ease,
            background-color 0.2s ease;
    }

    .inventory-alert-resolve-button:hover {
        transform: translateY(-1px);
        background: #daf5e8;
    }

    .inventory-alert-resolved-button {
        border: 1px solid #e1e5ec;
        background: #f4f5f7;
        color: #8790a0;
    }

    /*
    |--------------------------------------------------------------------------
    | Empty state
    |--------------------------------------------------------------------------
    */

    .inventory-alert-empty-cell {
        padding: 0 !important;
    }

    .inventory-alert-empty-state {
        display: flex;
        align-items: center;
        flex-direction: column;
        padding: 58px 24px;
        text-align: center;
    }

    .inventory-alert-empty-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        margin-bottom: 14px;
        border-radius: 15px;
        background: var(--alerts-primary-soft);
        color: var(--alerts-primary);
        font-size: 18px;
    }

    .inventory-alert-empty-state h3 {
        margin: 0 0 7px;
        color: var(--alerts-text);
        font-size: 15px;
    }

    .inventory-alert-empty-state p {
        max-width: 370px;
        margin: 0 0 17px;
        color: var(--alerts-muted);
        font-size: 11px;
        line-height: 1.6;
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    .inventory-alert-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 15px 18px;
        border-top: 1px solid var(--alerts-soft-border);
        background: #fbfcfe;
    }

    .inventory-alert-pagination-info {
        color: #7d879a;
        font-size: 10px;
    }

    .inventory-alert-pagination-info strong {
        color: #3f4859;
    }

    .inventory-alert-pagination-links,
    .inventory-alert-page-numbers {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .inventory-alert-page-button,
    .inventory-alert-page-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 31px;
        border: 1px solid #dde2ea;
        border-radius: 8px;
        background: #ffffff;
        color: #596377;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
    }

    .inventory-alert-page-button {
        gap: 6px;
        padding: 7px 10px;
    }

    .inventory-alert-page-number {
        min-width: 31px;
        padding: 6px;
    }

    .inventory-alert-page-button:hover,
    .inventory-alert-page-number:hover {
        border-color: var(--alerts-primary);
        color: var(--alerts-primary);
        text-decoration: none;
    }

    .inventory-alert-page-number.active {
        border-color: var(--alerts-primary);
        background: var(--alerts-primary);
        color: #ffffff;
    }

    .inventory-alert-page-button.disabled {
        opacity: 0.48;
        cursor: not-allowed;
    }

    .inventory-alert-page-dots {
        padding: 0 2px;
        color: #99a2b2;
        font-size: 10px;
    }

    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1250px) {
        .inventory-alert-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .inventory-alert-filter-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .inventory-alert-filter-actions {
            grid-column: 1 / -1;
            justify-content: flex-end;
        }
    }

    @media (max-width: 950px) {
        .inventory-alert-page-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .inventory-alert-header-actions {
            width: 100%;
            flex-wrap: wrap;
        }

        .inventory-alert-total {
            margin-right: auto;
        }

        .inventory-alert-pagination {
            align-items: flex-start;
            flex-direction: column;
        }

        .inventory-alert-pagination-links {
            width: 100%;
            justify-content: space-between;
        }
    }

    @media (max-width: 650px) {
        .inventory-alerts-page {
            gap: 15px;
        }

        .inventory-alert-page-header {
            min-height: auto;
            padding: 22px 18px;
            border-radius: 14px;
        }

        .inventory-alert-page-heading h1 {
            font-size: 26px;
        }

        .inventory-alert-header-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .inventory-alert-total,
        .inventory-alert-header-actions .inventory-alert-button {
            width: 100%;
        }

        .inventory-alert-stat-grid,
        .inventory-alert-filter-form {
            grid-template-columns: minmax(0, 1fr);
        }

        .inventory-alert-filter-actions {
            display: grid;
            grid-column: auto;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .inventory-alert-filter-actions .inventory-alert-button {
            width: 100%;
        }

        .inventory-alert-panel-heading {
            align-items: flex-start;
            flex-direction: column;
            padding: 17px;
        }

        .inventory-alert-filter-form {
            padding: 17px;
        }

        .inventory-alert-table-meta {
            width: 100%;
            justify-content: space-between;
        }

        .inventory-alert-panel {
            border-radius: 13px;
        }

        .inventory-alert-pagination-links {
            align-items: stretch;
            flex-direction: column;
        }

        .inventory-alert-page-numbers {
            flex-wrap: wrap;
            justify-content: center;
            order: -1;
            width: 100%;
        }

        .inventory-alert-page-button {
            width: 100%;
        }
    }

    @media (max-width: 420px) {
        .inventory-alert-stat-grid {
            gap: 10px;
        }

        .inventory-alert-filter-actions {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const notification = document.querySelector(
            '[data-alert-notification]'
        );

        const closeNotification = document.querySelector(
            '[data-close-notification]'
        );

        if (notification && closeNotification) {
            closeNotification.addEventListener('click', function () {
                notification.remove();
            });
        }

        document.querySelectorAll('[data-resolve-form]').forEach(
            function (form) {
                form.addEventListener('submit', function (event) {
                    const confirmed = window.confirm(
                        'Mark this inventory alert as resolved?'
                    );

                    if (!confirmed) {
                        event.preventDefault();
                    }
                });
            }
        );
    });
</script>
@endpush