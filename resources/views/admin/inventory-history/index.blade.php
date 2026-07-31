@extends('admin.layouts.app')

@section('title', 'Inventory History')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Current page statistics
    |--------------------------------------------------------------------------
    |
    | These figures are calculated from the records visible on the current
    | paginated page. The total history count comes from the paginator.
    |
    */

    $visibleItems = $history->getCollection();

    $visibleAdded = $visibleItems
        ->filter(fn ($item) => $item->isStockAddition())
        ->sum(fn ($item) => abs((int) $item->quantity_change));

    $visibleRemoved = $visibleItems
        ->filter(fn ($item) => $item->isStockDeduction())
        ->sum(fn ($item) => abs((int) $item->quantity_change));

    $visibleManual = $visibleItems
        ->filter(function ($item) {
            return in_array($item->movement_type, [
                'manual_adjustment',
                'manual_addition',
                'manual_deduction',
                'restock',
                'adjustment',
            ], true);
        })
        ->count();

    $startNumber = $history->firstItem() ?? 0;
    $endNumber = $history->lastItem() ?? 0;
@endphp

<div class="inventory-history-page">

    {{-- Page heading --}}
    <section class="history-page-header">

        <div class="history-page-heading">

            <span class="history-eyebrow">
                Inventory management
            </span>

            <h1>Inventory History</h1>

            <p>
                Review every stock movement, adjustment, order deduction
                and restock recorded in your store.
            </p>

        </div>

        <div class="history-header-actions">

            <div class="history-total-badge">

                <span class="history-total-icon">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>

                <span>
                    <small>Total records</small>
                    <strong>{{ number_format($history->total()) }}</strong>
                </span>

            </div>

            <a
                href="{{ route('admin.inventory-reports.index') }}"
                class="history-button history-button-primary"
            >
                <i class="fa-solid fa-chart-column"></i>
                <span>View reports</span>
            </a>

        </div>

    </section>

    {{-- Statistics --}}
    <section class="history-stat-grid">

        <article class="history-stat-card history-stat-purple">

            <div class="history-stat-top">

                <span class="history-stat-icon">
                    <i class="fa-solid fa-list-check"></i>
                </span>

                <span class="history-stat-label">
                    Total movements
                </span>

            </div>

            <strong class="history-stat-value">
                {{ number_format($history->total()) }}
            </strong>

            <span class="history-stat-meta">
                Complete recorded audit log
            </span>

        </article>

        <article class="history-stat-card history-stat-green">

            <div class="history-stat-top">

                <span class="history-stat-icon">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </span>

                <span class="history-stat-label">
                    Stock added
                </span>

            </div>

            <strong class="history-stat-value history-value-positive">
                +{{ number_format($visibleAdded) }}
            </strong>

            <span class="history-stat-meta">
                Units on this page
            </span>

        </article>

        <article class="history-stat-card history-stat-red">

            <div class="history-stat-top">

                <span class="history-stat-icon">
                    <i class="fa-solid fa-arrow-trend-down"></i>
                </span>

                <span class="history-stat-label">
                    Stock removed
                </span>

            </div>

            <strong class="history-stat-value history-value-negative">
                -{{ number_format($visibleRemoved) }}
            </strong>

            <span class="history-stat-meta">
                Units on this page
            </span>

        </article>

        <article class="history-stat-card history-stat-orange">

            <div class="history-stat-top">

                <span class="history-stat-icon">
                    <i class="fa-solid fa-pen-to-square"></i>
                </span>

                <span class="history-stat-label">
                    Manual activity
                </span>

            </div>

            <strong class="history-stat-value">
                {{ number_format($visibleManual) }}
            </strong>

            <span class="history-stat-meta">
                Adjustments on this page
            </span>

        </article>

    </section>

    {{-- Filters --}}
    <section class="history-panel history-filter-panel">

        <div class="history-panel-heading">

            <div>

                <span class="history-section-label">
                    Search and filters
                </span>

                <h2>Filter inventory movements</h2>

                <p>
                    Search products and narrow the audit log by movement type
                    or date range.
                </p>

            </div>

            @if(request()->filled('search')
                || request()->filled('movement')
                || request()->filled('from')
                || request()->filled('to'))

                <span class="history-active-filter">
                    <i class="fa-solid fa-filter-circle-xmark"></i>
                    Filters active
                </span>

            @endif

        </div>

        <form
            method="GET"
            action="{{ route('admin.inventory-history.index') }}"
            class="history-filter-form"
        >

            <div class="history-field history-search-field">

                <label for="historySearch">
                    Search
                </label>

                <div class="history-input-wrap">

                    <span class="history-input-icon">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>

                    <input
                        type="search"
                        id="historySearch"
                        name="search"
                        class="history-input history-input-with-icon"
                        placeholder="Product, SKU, reason or order..."
                        value="{{ request('search') }}"
                    >

                </div>

            </div>

            <div class="history-field">

                <label for="movementType">
                    Movement type
                </label>

                <div class="history-input-wrap">

                    <span class="history-input-icon">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                    </span>

                    <select
                        name="movement"
                        id="movementType"
                        class="history-input history-input-with-icon"
                    >

                        <option value="">
                            All movements
                        </option>

                        @foreach($movementTypes as $value => $label)

                            <option
                                value="{{ $value }}"
                                @selected(request('movement') == $value)
                            >
                                {{ $label }}
                            </option>

                        @endforeach

                    </select>

                    <span class="history-select-arrow">
                        <i class="fa-solid fa-chevron-down"></i>
                    </span>

                </div>

            </div>

            <div class="history-field">

                <label for="historyFrom">
                    From date
                </label>

                <input
                    type="date"
                    id="historyFrom"
                    name="from"
                    class="history-input"
                    value="{{ request('from') }}"
                >

            </div>

            <div class="history-field">

                <label for="historyTo">
                    To date
                </label>

                <input
                    type="date"
                    id="historyTo"
                    name="to"
                    class="history-input"
                    value="{{ request('to') }}"
                >

            </div>

            <div class="history-filter-actions">

                <button
                    type="submit"
                    class="history-button history-button-primary"
                >
                    <i class="fa-solid fa-filter"></i>
                    <span>Apply filters</span>
                </button>

                <a
                    href="{{ route('admin.inventory-history.index') }}"
                    class="history-button history-button-secondary"
                >
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Reset</span>
                </a>

            </div>

        </form>

    </section>

    {{-- History table --}}
    <section class="history-panel history-table-panel">

        <div class="history-panel-heading history-table-heading">

            <div>

                <span class="history-section-label">
                    Stock audit log
                </span>

                <h2>Inventory movements</h2>

                <p>
                    @if($history->total() > 0)
                        Showing {{ number_format($startNumber) }}
                        to {{ number_format($endNumber) }}
                        of {{ number_format($history->total()) }} records.
                    @else
                        No inventory movements match the selected filters.
                    @endif
                </p>

            </div>

            <div class="history-table-summary">

                <span>
                    Page {{ $history->currentPage() }}
                </span>

                <span class="history-summary-divider"></span>

                <span>
                    {{ number_format($history->count()) }} visible
                </span>

            </div>

        </div>

        <div class="history-table-scroll">

            <table class="history-table">

                <thead>

                    <tr>
                        <th>Date and time</th>
                        <th>Product</th>
                        <th>Movement</th>
                        <th>Stock transition</th>
                        <th>Quantity</th>
                        <th>Order</th>
                        <th>Performed by</th>
                        <th>Reason</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($history as $item)

                        @php
                            $isAddition = $item->isStockAddition();
                            $isDeduction = $item->isStockDeduction();

                            $movementClass = $isAddition
                                ? 'history-movement-add'
                                : ($isDeduction
                                    ? 'history-movement-remove'
                                    : 'history-movement-neutral');

                            $quantityClass = $isAddition
                                ? 'history-quantity-positive'
                                : ($isDeduction
                                    ? 'history-quantity-negative'
                                    : 'history-quantity-neutral');

                            $performedBy = $item->performed_by ?: 'System';

                            $initials = collect(
                                preg_split('/\s+/', trim($performedBy))
                            )
                                ->filter()
                                ->take(2)
                                ->map(fn ($word) => strtoupper(
                                    mb_substr($word, 0, 1)
                                ))
                                ->implode('');

                            $initials = $initials ?: 'S';

                            $stockBefore = (int) $item->stock_before;
                            $stockAfter = (int) $item->stock_after;
                        @endphp

                        <tr>

                            <td class="history-date-cell">

                                <span class="history-date-main">
                                    {{ $item->created_at->format('d M Y') }}
                                </span>

                                <span class="history-date-time">
                                    <i class="fa-regular fa-clock"></i>
                                    {{ $item->created_at->format('h:i A') }}
                                </span>

                            </td>

                            <td>

                                <div class="history-product">

                                    <span class="history-product-icon">
                                        <i class="fa-solid fa-box"></i>
                                    </span>

                                    <span class="history-product-content">

                                        <strong>
                                            {{ $item->item_name }}
                                        </strong>

                                        @if($item->product?->sku)

                                            <small>
                                                SKU: {{ $item->product->sku }}
                                            </small>

                                        @elseif($item->variant?->sku)

                                            <small>
                                                SKU: {{ $item->variant->sku }}
                                            </small>

                                        @else

                                            <small>
                                                Product movement
                                            </small>

                                        @endif

                                    </span>

                                </div>

                            </td>

                            <td>

                                <span class="history-movement {{ $movementClass }}">

                                    <span class="history-movement-dot"></span>

                                    {{ $item->movement_label }}

                                </span>

                            </td>

                            <td>

                                <div class="history-transition">

                                    <span class="history-stock-number">
                                        {{ number_format($stockBefore) }}
                                    </span>

                                    <span class="history-transition-arrow">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </span>

                                    <span class="history-stock-number history-stock-after">
                                        {{ number_format($stockAfter) }}
                                    </span>

                                </div>

                            </td>

                            <td>

                                <span class="history-quantity {{ $quantityClass }}">

                                    @if($isAddition)
                                        <i class="fa-solid fa-arrow-up"></i>
                                    @elseif($isDeduction)
                                        <i class="fa-solid fa-arrow-down"></i>
                                    @else
                                        <i class="fa-solid fa-minus"></i>
                                    @endif

                                    {{ $item->formatted_quantity_change }}

                                </span>

                            </td>

                            <td>

                                @if($item->order)

                                    <a
                                        href="{{ route('admin.orders.show', $item->order) }}"
                                        class="history-order-link"
                                    >
                                        <i class="fa-solid fa-receipt"></i>

                                        <span>
                                            {{ $item->order_reference }}
                                        </span>
                                    </a>

                                @else

                                    <span class="history-empty-value">
                                        —
                                    </span>

                                @endif

                            </td>

                            <td>

                                <div class="history-user">

                                    <span class="history-user-avatar">
                                        {{ $initials }}
                                    </span>

                                    <span class="history-user-details">

                                        <strong>
                                            {{ $performedBy }}
                                        </strong>

                                        <small>
                                            {{ $item->user ? 'Administrator' : 'System activity' }}
                                        </small>

                                    </span>

                                </div>

                            </td>

                            <td>

                                @if($item->reason)

                                    <span
                                        class="history-reason"
                                        title="{{ $item->reason }}"
                                    >
                                        {{ $item->reason }}
                                    </span>

                                @else

                                    <span class="history-empty-value">
                                        No reason provided
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="history-empty-cell"
                            >

                                <div class="history-empty-state">

                                    <span class="history-empty-icon">
                                        <i class="fa-solid fa-box-open"></i>
                                    </span>

                                    <h3>No inventory history found</h3>

                                    <p>
                                        No stock movements match your current
                                        search and filter selection.
                                    </p>

                                    <a
                                        href="{{ route('admin.inventory-history.index') }}"
                                        class="history-button history-button-secondary"
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

        {{-- Custom pagination --}}
        @if($history->hasPages())

            <div class="history-pagination">

                <div class="history-pagination-info">

                    Showing
                    <strong>{{ number_format($startNumber) }}</strong>
                    to
                    <strong>{{ number_format($endNumber) }}</strong>
                    of
                    <strong>{{ number_format($history->total()) }}</strong>

                </div>

                <nav
                    class="history-pagination-links"
                    aria-label="Inventory history pagination"
                >

                    @if($history->onFirstPage())

                        <span class="history-page-button disabled">
                            <i class="fa-solid fa-chevron-left"></i>
                            Previous
                        </span>

                    @else

                        <a
                            href="{{ $history->previousPageUrl() }}"
                            class="history-page-button"
                        >
                            <i class="fa-solid fa-chevron-left"></i>
                            Previous
                        </a>

                    @endif

                    <div class="history-page-numbers">

                        @php
                            $currentPage = $history->currentPage();
                            $lastPage = $history->lastPage();

                            $pageStart = max(1, $currentPage - 2);
                            $pageEnd = min($lastPage, $currentPage + 2);
                        @endphp

                        @if($pageStart > 1)

                            <a
                                href="{{ $history->url(1) }}"
                                class="history-page-number"
                            >
                                1
                            </a>

                            @if($pageStart > 2)
                                <span class="history-page-dots">…</span>
                            @endif

                        @endif

                        @for($page = $pageStart; $page <= $pageEnd; $page++)

                            @if($page === $currentPage)

                                <span class="history-page-number active">
                                    {{ $page }}
                                </span>

                            @else

                                <a
                                    href="{{ $history->url($page) }}"
                                    class="history-page-number"
                                >
                                    {{ $page }}
                                </a>

                            @endif

                        @endfor

                        @if($pageEnd < $lastPage)

                            @if($pageEnd < $lastPage - 1)
                                <span class="history-page-dots">…</span>
                            @endif

                            <a
                                href="{{ $history->url($lastPage) }}"
                                class="history-page-number"
                            >
                                {{ $lastPage }}
                            </a>

                        @endif

                    </div>

                    @if($history->hasMorePages())

                        <a
                            href="{{ $history->nextPageUrl() }}"
                            class="history-page-button"
                        >
                            Next
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>

                    @else

                        <span class="history-page-button disabled">
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
    | Inventory History
    |--------------------------------------------------------------------------
    */

    .inventory-history-page {
        --history-primary: #6558f5;
        --history-primary-dark: #5547ec;
        --history-primary-soft: #f0eeff;
        --history-navy: #101729;
        --history-text: #171d2d;
        --history-muted: #758099;
        --history-border: #e4e8f0;
        --history-soft-border: #edf0f5;
        --history-card: #ffffff;
        --history-bg: #f5f7fb;
        --history-green: #16875d;
        --history-green-soft: #eaf9f2;
        --history-red: #d84646;
        --history-red-soft: #fff0f0;
        --history-orange: #c56b11;
        --history-orange-soft: #fff5e7;
        --history-blue: #3375d6;
        --history-blue-soft: #edf5ff;

        display: flex;
        flex-direction: column;
        gap: 20px;
        min-width: 0;
    }

    .inventory-history-page *,
    .inventory-history-page *::before,
    .inventory-history-page *::after {
        box-sizing: border-box;
    }

    /*
    |--------------------------------------------------------------------------
    | Page Header
    |--------------------------------------------------------------------------
    */

    .history-page-header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        min-height: 150px;
        padding: 28px 30px;
        border: 1px solid var(--history-border);
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

    .history-page-header::before {
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

    .history-page-heading,
    .history-header-actions {
        position: relative;
        z-index: 1;
    }

    .history-page-heading {
        min-width: 0;
    }

    .history-eyebrow,
    .history-section-label {
        display: block;
        margin-bottom: 7px;
        color: var(--history-primary);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .history-page-heading h1 {
        margin: 0 0 8px;
        color: var(--history-text);
        font-size: clamp(27px, 3vw, 35px);
        font-weight: 800;
        line-height: 1.12;
        letter-spacing: -0.04em;
    }

    .history-page-heading p {
        max-width: 650px;
        margin: 0;
        color: var(--history-muted);
        font-size: 13px;
        line-height: 1.65;
    }

    .history-header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    .history-total-badge {
        display: flex;
        align-items: center;
        gap: 11px;
        min-height: 50px;
        padding: 8px 14px 8px 9px;
        border: 1px solid var(--history-border);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.92);
        box-shadow: 0 4px 12px rgba(24, 31, 52, 0.04);
    }

    .history-total-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: var(--history-primary-soft);
        color: var(--history-primary);
        font-size: 13px;
    }

    .history-total-badge > span:last-child {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .history-total-badge small {
        color: var(--history-muted);
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .history-total-badge strong {
        color: var(--history-text);
        font-size: 16px;
        line-height: 1;
    }

    /*
    |--------------------------------------------------------------------------
    | Buttons
    |--------------------------------------------------------------------------
    */

    .history-button {
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

    .history-button:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .history-button-primary {
        border-color: var(--history-primary);
        background: var(--history-primary);
        color: #ffffff;
        box-shadow: 0 8px 16px rgba(101, 88, 245, 0.18);
    }

    .history-button-primary:hover {
        border-color: var(--history-primary-dark);
        background: var(--history-primary-dark);
        color: #ffffff;
        box-shadow: 0 10px 20px rgba(101, 88, 245, 0.24);
    }

    .history-button-secondary {
        border-color: #dce1ea;
        background: #ffffff;
        color: #4d576c;
    }

    .history-button-secondary:hover {
        border-color: #c8cfdb;
        background: #f9fafc;
        color: var(--history-text);
    }

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    .history-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .history-stat-card {
        position: relative;
        min-width: 0;
        min-height: 132px;
        padding: 18px;
        border: 1px solid var(--history-border);
        border-radius: 14px;
        background: var(--history-card);
        box-shadow: 0 6px 18px rgba(24, 31, 52, 0.04);
        overflow: hidden;
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .history-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(24, 31, 52, 0.07);
    }

    .history-stat-card::after {
        content: "";
        position: absolute;
        top: -32px;
        right: -32px;
        width: 92px;
        height: 92px;
        border-radius: 50%;
        opacity: 0.75;
    }

    .history-stat-purple::after {
        background: var(--history-primary-soft);
    }

    .history-stat-green::after {
        background: var(--history-green-soft);
    }

    .history-stat-red::after {
        background: var(--history-red-soft);
    }

    .history-stat-orange::after {
        background: var(--history-orange-soft);
    }

    .history-stat-top,
    .history-stat-value,
    .history-stat-meta {
        position: relative;
        z-index: 1;
    }

    .history-stat-top {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 15px;
    }

    .history-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 9px;
        font-size: 12px;
    }

    .history-stat-purple .history-stat-icon {
        background: var(--history-primary-soft);
        color: var(--history-primary);
    }

    .history-stat-green .history-stat-icon {
        background: var(--history-green-soft);
        color: var(--history-green);
    }

    .history-stat-red .history-stat-icon {
        background: var(--history-red-soft);
        color: var(--history-red);
    }

    .history-stat-orange .history-stat-icon {
        background: var(--history-orange-soft);
        color: var(--history-orange);
    }

    .history-stat-label {
        color: #5f697d;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.025em;
        text-transform: uppercase;
    }

    .history-stat-value {
        display: block;
        margin-bottom: 7px;
        color: var(--history-text);
        font-size: 27px;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.035em;
    }

    .history-value-positive {
        color: var(--history-green);
    }

    .history-value-negative {
        color: var(--history-red);
    }

    .history-stat-meta {
        display: block;
        color: #8a93a5;
        font-size: 10px;
    }

    /*
    |--------------------------------------------------------------------------
    | Panels
    |--------------------------------------------------------------------------
    */

    .history-panel {
        min-width: 0;
        border: 1px solid var(--history-border);
        border-radius: 15px;
        background: var(--history-card);
        box-shadow: 0 7px 22px rgba(24, 31, 52, 0.04);
        overflow: hidden;
    }

    .history-panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 19px 21px;
        border-bottom: 1px solid var(--history-soft-border);
        background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
    }

    .history-panel-heading > div:first-child {
        min-width: 0;
    }

    .history-panel-heading h2 {
        margin: 0 0 5px;
        color: var(--history-text);
        font-size: 15px;
        font-weight: 800;
        line-height: 1.3;
        letter-spacing: -0.02em;
    }

    .history-panel-heading p {
        margin: 0;
        color: var(--history-muted);
        font-size: 11px;
        line-height: 1.55;
    }

    .history-active-filter {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        flex-shrink: 0;
        padding: 7px 10px;
        border-radius: 999px;
        background: var(--history-primary-soft);
        color: var(--history-primary);
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.035em;
    }

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    .history-filter-form {
        display: grid;
        grid-template-columns:
            minmax(220px, 1.5fr)
            minmax(170px, 1fr)
            minmax(145px, 0.8fr)
            minmax(145px, 0.8fr)
            auto;
        align-items: end;
        gap: 13px;
        padding: 20px 21px;
    }

    .history-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        min-width: 0;
    }

    .history-field label {
        color: #5d6678;
        font-size: 10px;
        font-weight: 800;
    }

    .history-input-wrap {
        position: relative;
        min-width: 0;
    }

    .history-input {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid #dce1e9;
        border-radius: 9px;
        background: #ffffff;
        color: var(--history-text);
        font-family: inherit;
        font-size: 11px;
        outline: none;
        transition:
            border-color 0.2s ease,
            box-shadow 0.2s ease;
    }

    .history-input:hover {
        border-color: #c8cfda;
    }

    .history-input:focus {
        border-color: var(--history-primary);
        box-shadow: 0 0 0 3px rgba(101, 88, 245, 0.1);
    }

    .history-input::placeholder {
        color: #9aa3b3;
    }

    .history-input-with-icon {
        padding-left: 37px;
    }

    select.history-input {
        padding-right: 34px;
        appearance: none;
        cursor: pointer;
    }

    .history-input-icon {
        position: absolute;
        top: 50%;
        left: 13px;
        z-index: 1;
        color: #929bad;
        font-size: 11px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .history-select-arrow {
        position: absolute;
        top: 50%;
        right: 13px;
        color: #929bad;
        font-size: 9px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .history-filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /*
    |--------------------------------------------------------------------------
    | Table Heading
    |--------------------------------------------------------------------------
    */

    .history-table-summary {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
        color: #7a8497;
        font-size: 10px;
        font-weight: 700;
    }

    .history-summary-divider {
        width: 1px;
        height: 15px;
        background: var(--history-border);
    }

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    .history-table-scroll {
        width: 100%;
        min-width: 0;
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: #cbd2dd transparent;
    }

    .history-table-scroll::-webkit-scrollbar {
        height: 8px;
    }

    .history-table-scroll::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #cbd2dd;
    }

    .history-table {
        width: 100%;
        min-width: 1150px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .history-table th,
    .history-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--history-soft-border);
        text-align: left;
        vertical-align: middle;
    }

    .history-table th {
        background: #f8f9fc;
        color: #707a8e;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: 0.055em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .history-table td {
        color: #4c566a;
        font-size: 11px;
        line-height: 1.5;
    }

    .history-table tbody tr {
        transition: background-color 0.18s ease;
    }

    .history-table tbody tr:hover {
        background: #fbfbfe;
    }

    .history-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .history-date-main {
        display: block;
        margin-bottom: 4px;
        color: #31394a;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .history-date-time {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #929bad;
        font-size: 9px;
        white-space: nowrap;
    }

    /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

    .history-product {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 180px;
    }

    .history-product-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: var(--history-primary-soft);
        color: var(--history-primary);
        font-size: 11px;
    }

    .history-product-content {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 0;
    }

    .history-product-content strong {
        max-width: 170px;
        overflow: hidden;
        color: var(--history-text);
        font-size: 11px;
        font-weight: 800;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .history-product-content small {
        color: #939cad;
        font-size: 9px;
    }

    /*
    |--------------------------------------------------------------------------
    | Movement badges
    |--------------------------------------------------------------------------
    */

    .history-movement {
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

    .history-movement-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
    }

    .history-movement-add {
        border-color: #cdeedf;
        background: var(--history-green-soft);
        color: var(--history-green);
    }

    .history-movement-remove {
        border-color: #ffd3d3;
        background: var(--history-red-soft);
        color: var(--history-red);
    }

    .history-movement-neutral {
        border-color: #dfe3ff;
        background: var(--history-primary-soft);
        color: var(--history-primary);
    }

    /*
    |--------------------------------------------------------------------------
    | Stock transition
    |--------------------------------------------------------------------------
    */

    .history-transition {
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .history-stock-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 31px;
        min-height: 27px;
        padding: 4px 7px;
        border-radius: 7px;
        background: #f1f3f7;
        color: #667085;
        font-size: 10px;
        font-weight: 800;
    }

    .history-stock-after {
        background: var(--history-blue-soft);
        color: var(--history-blue);
    }

    .history-transition-arrow {
        color: #a4acb9;
        font-size: 8px;
    }

    /*
    |--------------------------------------------------------------------------
    | Quantity
    |--------------------------------------------------------------------------
    */

    .history-quantity {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-width: 48px;
        min-height: 27px;
        padding: 5px 8px;
        border-radius: 7px;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .history-quantity-positive {
        background: var(--history-green-soft);
        color: var(--history-green);
    }

    .history-quantity-negative {
        background: var(--history-red-soft);
        color: var(--history-red);
    }

    .history-quantity-neutral {
        background: #f1f3f7;
        color: #667085;
    }

    /*
    |--------------------------------------------------------------------------
    | Order, user and reason
    |--------------------------------------------------------------------------
    */

    .history-order-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--history-primary);
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }

    .history-order-link:hover {
        color: var(--history-primary-dark);
        text-decoration: underline;
    }

    .history-user {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 125px;
    }

    .history-user-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 29px;
        height: 29px;
        border-radius: 8px;
        background: var(--history-navy);
        color: #ffffff;
        font-size: 9px;
        font-weight: 800;
    }

    .history-user-details {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .history-user-details strong {
        color: #303849;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .history-user-details small {
        color: #949dad;
        font-size: 8px;
        white-space: nowrap;
    }

    .history-reason {
        display: block;
        max-width: 190px;
        overflow: hidden;
        color: #505a6d;
        font-size: 10px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .history-empty-value {
        color: #a1a9b7;
        font-size: 10px;
    }

    /*
    |--------------------------------------------------------------------------
    | Empty state
    |--------------------------------------------------------------------------
    */

    .history-empty-cell {
        padding: 0 !important;
    }

    .history-empty-state {
        display: flex;
        align-items: center;
        flex-direction: column;
        padding: 58px 24px;
        text-align: center;
    }

    .history-empty-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        margin-bottom: 14px;
        border-radius: 15px;
        background: var(--history-primary-soft);
        color: var(--history-primary);
        font-size: 18px;
    }

    .history-empty-state h3 {
        margin: 0 0 7px;
        color: var(--history-text);
        font-size: 15px;
    }

    .history-empty-state p {
        max-width: 370px;
        margin: 0 0 17px;
        color: var(--history-muted);
        font-size: 11px;
        line-height: 1.6;
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    .history-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 15px 18px;
        border-top: 1px solid var(--history-soft-border);
        background: #fbfcfe;
    }

    .history-pagination-info {
        color: #7d879a;
        font-size: 10px;
    }

    .history-pagination-info strong {
        color: #3f4859;
    }

    .history-pagination-links,
    .history-page-numbers {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .history-page-button,
    .history-page-number {
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
        transition:
            border-color 0.2s ease,
            background-color 0.2s ease,
            color 0.2s ease;
    }

    .history-page-button {
        gap: 6px;
        padding: 7px 10px;
    }

    .history-page-number {
        min-width: 31px;
        padding: 6px;
    }

    .history-page-button:hover,
    .history-page-number:hover {
        border-color: var(--history-primary);
        color: var(--history-primary);
        text-decoration: none;
    }

    .history-page-number.active {
        border-color: var(--history-primary);
        background: var(--history-primary);
        color: #ffffff;
    }

    .history-page-button.disabled {
        opacity: 0.48;
        cursor: not-allowed;
    }

    .history-page-dots {
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
        .history-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .history-filter-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .history-search-field {
            grid-column: 1 / -1;
        }

        .history-filter-actions {
            grid-column: 1 / -1;
            justify-content: flex-end;
        }
    }

    @media (max-width: 900px) {
        .history-page-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .history-header-actions {
            width: 100%;
            justify-content: space-between;
        }

        .history-pagination {
            align-items: flex-start;
            flex-direction: column;
        }

        .history-pagination-links {
            width: 100%;
            justify-content: space-between;
        }
    }

    @media (max-width: 650px) {
        .inventory-history-page {
            gap: 15px;
        }

        .history-page-header {
            min-height: auto;
            padding: 22px 18px;
            border-radius: 14px;
        }

        .history-page-heading h1 {
            font-size: 26px;
        }

        .history-header-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .history-total-badge,
        .history-header-actions .history-button {
            width: 100%;
        }

        .history-stat-grid,
        .history-filter-form {
            grid-template-columns: minmax(0, 1fr);
        }

        .history-search-field,
        .history-filter-actions {
            grid-column: auto;
        }

        .history-filter-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .history-filter-actions .history-button {
            width: 100%;
        }

        .history-panel-heading {
            align-items: flex-start;
            flex-direction: column;
            padding: 17px;
        }

        .history-filter-form {
            padding: 17px;
        }

        .history-table-summary {
            width: 100%;
            justify-content: space-between;
        }

        .history-panel {
            border-radius: 13px;
        }

        .history-pagination-links {
            align-items: stretch;
            flex-direction: column;
        }

        .history-page-numbers {
            flex-wrap: wrap;
            justify-content: center;
            order: -1;
            width: 100%;
        }

        .history-page-button {
            width: 100%;
        }
    }

    @media (max-width: 420px) {
        .history-stat-grid {
            gap: 10px;
        }

        .history-filter-actions {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush