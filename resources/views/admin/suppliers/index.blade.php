@extends('admin.layouts.app')

@section('title', 'Suppliers')

@section('content')

<div class="suppliers-page">

    {{-- Page Header --}}
    <section class="suppliers-header">

        <div class="suppliers-header-content">

            <div class="suppliers-header-icon">

                <i class="fa-solid fa-truck-field"></i>

            </div>

            <div>

                <span class="suppliers-eyebrow">
                    Purchasing management
                </span>

                <h1>
                    Suppliers
                </h1>

                <p>
                    Manage supplier relationships, purchasing history,
                    contact information and commercial terms.
                </p>

            </div>

        </div>

        <div class="suppliers-header-actions">

            <a
                href="{{ route('admin.purchase-orders.index') }}"
                class="supplier-header-button secondary">

                <i class="fa-solid fa-file-invoice-dollar"></i>

                Purchase Orders

            </a>

            <a
                href="{{ route('admin.suppliers.create') }}"
                class="supplier-header-button primary">

                <i class="fa-solid fa-plus"></i>

                Add Supplier

            </a>

        </div>

    </section>

    {{-- Success Message --}}
    @if (session('success'))

        <div class="supplier-alert success">

            <i class="fa-solid fa-circle-check"></i>

            <span>
                {{ session('success') }}
            </span>

        </div>

    @endif

    {{-- Error Message --}}
    @if (session('error'))

        <div class="supplier-alert error">

            <i class="fa-solid fa-circle-exclamation"></i>

            <span>
                {{ session('error') }}
            </span>

        </div>

    @endif

    {{-- Summary Cards --}}
    <section class="supplier-summary-grid">

        <article class="supplier-summary-card">

            <div class="supplier-summary-card-top">

                <span class="supplier-summary-icon total">

                    <i class="fa-solid fa-building"></i>

                </span>

                <span class="supplier-summary-label">
                    Total Suppliers
                </span>

            </div>

            <strong class="supplier-summary-value">
                {{ number_format($totalSuppliers) }}
            </strong>

            <span class="supplier-summary-description">
                All registered supplier accounts
            </span>

        </article>

        <article class="supplier-summary-card">

            <div class="supplier-summary-card-top">

                <span class="supplier-summary-icon active">

                    <i class="fa-solid fa-circle-check"></i>

                </span>

                <span class="supplier-summary-label">
                    Active Suppliers
                </span>

            </div>

            <strong class="supplier-summary-value">
                {{ number_format($activeSuppliers) }}
            </strong>

            <span class="supplier-summary-description">
                Suppliers currently available for purchasing
            </span>

        </article>

        <article class="supplier-summary-card">

            <div class="supplier-summary-card-top">

                <span class="supplier-summary-icon preferred">

                    <i class="fa-solid fa-star"></i>

                </span>

                <span class="supplier-summary-label">
                    Preferred Suppliers
                </span>

            </div>

            <strong class="supplier-summary-value">
                {{ number_format($preferredSuppliers) }}
            </strong>

            <span class="supplier-summary-description">
                Priority supplier relationships
            </span>

        </article>

        <article class="supplier-summary-card">

            <div class="supplier-summary-card-top">

                <span class="supplier-summary-icon spend">

                    <i class="fa-solid fa-sterling-sign"></i>

                </span>

                <span class="supplier-summary-label">
                    Total Supplier Spend
                </span>

            </div>

            <strong class="supplier-summary-value">
                £{{ number_format($totalSupplierSpend, 2) }}
            </strong>

            <span class="supplier-summary-description">
                Excluding cancelled purchase orders
            </span>

        </article>

    </section>

    {{-- Status Cards --}}
    <section class="supplier-status-grid">

        <a
            href="{{ route(
                'admin.suppliers.index',
                ['status' => 'active']
            ) }}"
            class="supplier-status-card active">

            <span class="supplier-status-icon">

                <i class="fa-solid fa-user-check"></i>

            </span>

            <div>

                <span>
                    Active
                </span>

                <strong>
                    {{ number_format($activeSuppliers) }}
                </strong>

            </div>

        </a>

        <a
            href="{{ route(
                'admin.suppliers.index',
                ['status' => 'inactive']
            ) }}"
            class="supplier-status-card inactive">

            <span class="supplier-status-icon">

                <i class="fa-solid fa-user-clock"></i>

            </span>

            <div>

                <span>
                    Inactive
                </span>

                <strong>
                    {{ number_format($inactiveSuppliers) }}
                </strong>

            </div>

        </a>

        <a
            href="{{ route(
                'admin.suppliers.index',
                ['status' => 'blocked']
            ) }}"
            class="supplier-status-card blocked">

            <span class="supplier-status-icon">

                <i class="fa-solid fa-user-slash"></i>

            </span>

            <div>

                <span>
                    Blocked
                </span>

                <strong>
                    {{ number_format($blockedSuppliers) }}
                </strong>

            </div>

        </a>

        <a
            href="{{ route(
                'admin.suppliers.index',
                ['preferred' => 'yes']
            ) }}"
            class="supplier-status-card preferred">

            <span class="supplier-status-icon">

                <i class="fa-solid fa-award"></i>

            </span>

            <div>

                <span>
                    Preferred
                </span>

                <strong>
                    {{ number_format($preferredSuppliers) }}
                </strong>

            </div>

        </a>

    </section>

    {{-- Suppliers Panel --}}
    <section class="suppliers-panel">

        <div class="suppliers-panel-header">

            <div>

                <span class="suppliers-eyebrow">
                    Supplier directory
                </span>

                <h2>
                    All Suppliers
                </h2>

                <p>
                    Search, filter and review your supplier accounts.
                </p>

            </div>

            <span class="supplier-result-count">

                <strong>
                    {{ number_format($suppliers->total()) }}
                </strong>

                results

            </span>

        </div>

        {{-- Filters --}}
        <form
            method="GET"
            action="{{ route('admin.suppliers.index') }}"
            class="supplier-filter-toolbar">

            <div class="supplier-search">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search company, code, contact, email..."
                    autocomplete="off">

            </div>

            <select name="status">

                <option value="">
                    All Statuses
                </option>

                <option
                    value="active"
                    @selected(request('status') === 'active')>

                    Active

                </option>

                <option
                    value="inactive"
                    @selected(request('status') === 'inactive')>

                    Inactive

                </option>

                <option
                    value="blocked"
                    @selected(request('status') === 'blocked')>

                    Blocked

                </option>

            </select>

            <select name="country">

                <option value="">
                    All Countries
                </option>

                @foreach ($countries as $country)

                    <option
                        value="{{ $country }}"
                        @selected(
                            request('country') === $country
                        )>

                        {{ $country }}

                    </option>

                @endforeach

            </select>

            <select name="preferred">

                <option value="">
                    All Supplier Types
                </option>

                <option
                    value="yes"
                    @selected(request('preferred') === 'yes')>

                    Preferred Only

                </option>

                <option
                    value="no"
                    @selected(request('preferred') === 'no')>

                    Non-preferred

                </option>

            </select>

            <select name="sort">

                <option value="">
                    Newest First
                </option>

                <option
                    value="oldest"
                    @selected(request('sort') === 'oldest')>

                    Oldest First

                </option>

                <option
                    value="company-asc"
                    @selected(request('sort') === 'company-asc')>

                    Company A–Z

                </option>

                <option
                    value="company-desc"
                    @selected(request('sort') === 'company-desc')>

                    Company Z–A

                </option>

                <option
                    value="highest-spend"
                    @selected(request('sort') === 'highest-spend')>

                    Highest Spend

                </option>

                <option
                    value="highest-rating"
                    @selected(request('sort') === 'highest-rating')>

                    Highest Rating

                </option>

            </select>

            <button
                type="submit"
                class="supplier-filter-button">

                <i class="fa-solid fa-filter"></i>

                Apply Filters

            </button>

            <a
                href="{{ route('admin.suppliers.index') }}"
                class="supplier-reset-button">

                <i class="fa-solid fa-rotate-left"></i>

                Reset

            </a>

        </form>

        {{-- Active Filter Information --}}
        @if (
            request()->filled('search')
            || request()->filled('status')
            || request()->filled('country')
            || request()->filled('preferred')
            || request()->filled('sort')
        )

            <div class="supplier-active-filters">

                <div>

                    <i class="fa-solid fa-filter-circle-xmark"></i>

                    <span>
                        Filtered supplier results are currently displayed.
                    </span>

                </div>

                <a href="{{ route('admin.suppliers.index') }}">
                    Clear all filters
                </a>

            </div>

        @endif

        {{-- Suppliers Table --}}
        <div class="supplier-table-wrapper">

            <table class="supplier-table">

                <thead>

                    <tr>

                        <th>Supplier</th>
                        <th>Primary Contact</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Commercial Terms</th>
                        <th class="number-column">Purchase Orders</th>
                        <th class="number-column">Total Spend</th>
                        <th class="number-column">Rating</th>
                        <th class="action-column"></th>

                    </tr>

                </thead>

                <tbody>

                    @forelse ($suppliers as $supplier)

                        @php
                            $rating = (float) (
                                $supplier->average_rating ?? 0
                            );

                            $fullStars = max(
                                0,
                                min(
                                    5,
                                    (int) round($rating)
                                )
                            );
                        @endphp

                        <tr>

                            <td>

                                <div class="supplier-information">

                                    <span class="supplier-avatar">

                                        {{
                                            strtoupper(
                                                substr(
                                                    trim(
                                                        $supplier->company_name
                                                    ),
                                                    0,
                                                    1
                                                )
                                            )
                                        }}

                                    </span>

                                    <div>

                                        <div class="supplier-name-row">

                                            <a
                                                href="{{ route(
                                                    'admin.suppliers.show',
                                                    $supplier
                                                ) }}">

                                                {{ $supplier->company_name }}

                                            </a>

                                            @if ($supplier->is_preferred)

                                                <span
                                                    class="preferred-badge"
                                                    title="Preferred supplier">

                                                    <i class="fa-solid fa-star"></i>

                                                    Preferred

                                                </span>

                                            @endif

                                        </div>

                                        <span class="supplier-code">
                                            {{
                                                $supplier->supplier_code
                                                ?: 'No supplier code'
                                            }}
                                        </span>

                                        @if ($supplier->website)

                                            <a
                                                href="{{ $supplier->website }}"
                                                class="supplier-website"
                                                target="_blank"
                                                rel="noopener">

                                                <i class="fa-solid fa-globe"></i>

                                                Website

                                            </a>

                                        @endif

                                    </div>

                                </div>

                            </td>

                            <td>

                                <div class="supplier-contact">

                                    <strong>
                                        {{
                                            $supplier->contact_person
                                            ?: 'Not assigned'
                                        }}
                                    </strong>

                                    @if ($supplier->email)

                                        <a
                                            href="mailto:{{ $supplier->email }}">

                                            <i class="fa-regular fa-envelope"></i>

                                            {{ $supplier->email }}

                                        </a>

                                    @else

                                        <span>
                                            No email assigned
                                        </span>

                                    @endif

                                    @if ($supplier->phone)

                                        <a href="tel:{{ $supplier->phone }}">

                                            <i class="fa-solid fa-phone"></i>

                                            {{ $supplier->phone }}

                                        </a>

                                    @endif

                                </div>

                            </td>

                            <td>

                                <div class="supplier-location">

                                    <strong>
                                        {{
                                            $supplier->country
                                            ?: 'Not assigned'
                                        }}
                                    </strong>

                                    <span>

                                        {{
                                            collect([
                                                $supplier->city,
                                                $supplier->state,
                                            ])
                                                ->filter()
                                                ->implode(', ')
                                                ?: 'No city assigned'
                                        }}

                                    </span>

                                </div>

                            </td>

                            <td>

                                <span
                                    class="supplier-status-badge {{
                                        $supplier->status
                                    }}">

                                    <span class="supplier-status-dot"></span>

                                    {{ $supplier->status_label }}

                                </span>

                            </td>

                            <td>

                                <div class="supplier-terms">

                                    <strong>
                                        {{
                                            $supplier->payment_terms
                                            ?: 'Not configured'
                                        }}
                                    </strong>

                                    <span>

                                        @if (
                                            $supplier->lead_time_days
                                            !== null
                                        )

                                            {{
                                                number_format(
                                                    $supplier->lead_time_days
                                                )
                                            }}
                                            day lead time

                                        @else

                                            Lead time not set

                                        @endif

                                    </span>

                                    <small>
                                        {{ $supplier->currency }}
                                    </small>

                                </div>

                            </td>

                            <td class="number-column">

                                <strong class="supplier-order-count">

                                    {{ number_format(
                                        $supplier->purchase_orders_count
                                    ) }}

                                </strong>

                                <span class="number-description">
                                    orders
                                </span>

                            </td>

                            <td class="number-column">

                                <strong class="supplier-spend-value">

                                    £{{ number_format(
                                        $supplier->total_spend ?? 0,
                                        2
                                    ) }}

                                </strong>

                            </td>

                            <td class="number-column">

                                @if ($rating > 0)

                                    <div class="supplier-rating">

                                        <div class="supplier-rating-stars">

                                            @for (
                                                $star = 1;
                                                $star <= 5;
                                                $star++
                                            )

                                                <i class="{{
                                                    $star <= $fullStars
                                                        ? 'fa-solid'
                                                        : 'fa-regular'
                                                }} fa-star"></i>

                                            @endfor

                                        </div>

                                        <strong>
                                            {{ number_format($rating, 2) }}
                                        </strong>

                                        <span>
                                            {{
                                                number_format(
                                                    $supplier->ratings_count
                                                )
                                            }}
                                            reviews
                                        </span>

                                    </div>

                                @else

                                    <span class="supplier-no-rating">
                                        Not rated
                                    </span>

                                @endif

                            </td>

                            <td class="action-column">

                                <div class="supplier-row-actions">

                                    <a
                                        href="{{ route(
                                            'admin.suppliers.show',
                                            $supplier
                                        ) }}"
                                        class="supplier-row-action view"
                                        title="View supplier">

                                        <i class="fa-regular fa-eye"></i>

                                    </a>

                                    <a
                                        href="{{ route(
                                            'admin.suppliers.edit',
                                            $supplier
                                        ) }}"
                                        class="supplier-row-action edit"
                                        title="Edit supplier">

                                        <i class="fa-regular fa-pen-to-square"></i>

                                    </a>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="9"
                                class="supplier-empty-state">

                                <div class="supplier-empty-icon">

                                    <i class="fa-solid fa-truck-field"></i>

                                </div>

                                <h3>
                                    No suppliers found
                                </h3>

                                <p>
                                    Add your first supplier or change the
                                    selected search filters.
                                </p>

                                <a
                                    href="{{ route(
                                        'admin.suppliers.create'
                                    ) }}">

                                    <i class="fa-solid fa-plus"></i>

                                    Add Supplier

                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Pagination --}}
        @if ($suppliers->hasPages())

            <div class="supplier-pagination">

                <div class="supplier-pagination-summary">

                    Showing

                    <strong>
                        {{ number_format($suppliers->firstItem()) }}
                    </strong>

                    to

                    <strong>
                        {{ number_format($suppliers->lastItem()) }}
                    </strong>

                    of

                    <strong>
                        {{ number_format($suppliers->total()) }}
                    </strong>

                    suppliers

                </div>

                <div>
                    {{ $suppliers->links() }}
                </div>

            </div>

        @endif

    </section>

</div>

@endsection

@push('page-styles')

<style>
    .suppliers-page {
        --supplier-text: #111827;
        --supplier-muted: #64748b;
        --supplier-border: #e5e7eb;
        --supplier-soft-border: #eef2f7;
        --supplier-indigo: #4f46e5;
        --supplier-indigo-dark: #4338ca;
        --supplier-indigo-soft: #eef2ff;
        --supplier-green: #15803d;
        --supplier-green-soft: #ecfdf3;
        --supplier-orange: #c2410c;
        --supplier-orange-soft: #fff7ed;
        --supplier-red: #b91c1c;
        --supplier-red-soft: #fef2f2;
        --supplier-blue: #0369a1;
        --supplier-blue-soft: #f0f9ff;
        --supplier-yellow: #a16207;
        --supplier-yellow-soft: #fefce8;

        display: flex;
        flex-direction: column;
        gap: 22px;
        min-width: 0;
        color: var(--supplier-text);
    }

    .suppliers-page *,
    .suppliers-page *::before,
    .suppliers-page *::after {
        box-sizing: border-box;
    }

    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    .suppliers-header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 27px 29px;
        border: 1px solid var(--supplier-border);
        border-radius: 19px;
        background:
            radial-gradient(
                circle at top right,
                rgba(79, 70, 229, 0.14),
                transparent 38%
            ),
            linear-gradient(
                135deg,
                #ffffff 0%,
                #f8f9ff 100%
            );
        box-shadow:
            0 1px 2px rgba(15, 23, 42, 0.04),
            0 12px 30px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .suppliers-header::after {
        content: "";
        position: absolute;
        top: -65px;
        right: -45px;
        width: 190px;
        height: 190px;
        border: 29px solid rgba(79, 70, 229, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }

    .suppliers-header-content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 17px;
        min-width: 0;
    }

    .suppliers-header-icon {
        width: 58px;
        height: 58px;
        flex: 0 0 58px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: var(--supplier-indigo-soft);
        color: var(--supplier-indigo);
        font-size: 22px;
    }

    .suppliers-eyebrow {
        display: block;
        margin-bottom: 6px;
        color: var(--supplier-indigo);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .suppliers-header h1 {
        margin: 0 0 7px;
        color: var(--supplier-text);
        font-size: 29px;
        line-height: 1.2;
    }

    .suppliers-header p {
        max-width: 650px;
        margin: 0;
        color: var(--supplier-muted);
        font-size: 12px;
        line-height: 1.65;
    }

    .suppliers-header-actions {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 9px;
        flex-shrink: 0;
    }

    .supplier-header-button {
        min-height: 43px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 15px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: 0.2s ease;
    }

    .supplier-header-button:hover {
        transform: translateY(-1px);
    }

    .supplier-header-button.primary {
        border-color: var(--supplier-indigo);
        background: var(--supplier-indigo);
        color: #ffffff;
    }

    .supplier-header-button.primary:hover {
        border-color: var(--supplier-indigo-dark);
        background: var(--supplier-indigo-dark);
    }

    .supplier-header-button.secondary {
        border-color: #c7d2fe;
        background: var(--supplier-indigo-soft);
        color: var(--supplier-indigo);
    }

    /*
    |--------------------------------------------------------------------------
    | Alerts
    |--------------------------------------------------------------------------
    */

    .supplier-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        border-radius: 11px;
        font-size: 11px;
        font-weight: 700;
    }

    .supplier-alert.success {
        border: 1px solid #bbf7d0;
        background: #f0fdf4;
        color: var(--supplier-green);
    }

    .supplier-alert.error {
        border: 1px solid #fecaca;
        background: var(--supplier-red-soft);
        color: var(--supplier-red);
    }

    /*
    |--------------------------------------------------------------------------
    | Summary Cards
    |--------------------------------------------------------------------------
    */

    .supplier-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 17px;
    }

    .supplier-summary-card {
        position: relative;
        min-width: 0;
        padding: 20px;
        border: 1px solid var(--supplier-border);
        border-radius: 15px;
        background: #ffffff;
        box-shadow: 0 7px 20px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .supplier-summary-card::after {
        content: "";
        position: absolute;
        right: -28px;
        bottom: -36px;
        width: 90px;
        height: 90px;
        border: 16px solid rgba(148, 163, 184, 0.07);
        border-radius: 50%;
    }

    .supplier-summary-card-top {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 17px;
    }

    .supplier-summary-icon {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        font-size: 15px;
    }

    .supplier-summary-icon.total {
        background: var(--supplier-indigo-soft);
        color: var(--supplier-indigo);
    }

    .supplier-summary-icon.active {
        background: var(--supplier-green-soft);
        color: var(--supplier-green);
    }

    .supplier-summary-icon.preferred {
        background: var(--supplier-yellow-soft);
        color: var(--supplier-yellow);
    }

    .supplier-summary-icon.spend {
        background: var(--supplier-blue-soft);
        color: var(--supplier-blue);
    }

    .supplier-summary-label {
        color: var(--supplier-muted);
        font-size: 11px;
        font-weight: 700;
    }

    .supplier-summary-value {
        position: relative;
        z-index: 1;
        display: block;
        margin-bottom: 8px;
        color: var(--supplier-text);
        font-size: 25px;
        line-height: 1.2;
    }

    .supplier-summary-description {
        position: relative;
        z-index: 1;
        color: var(--supplier-muted);
        font-size: 9px;
        line-height: 1.5;
    }

    /*
    |--------------------------------------------------------------------------
    | Status Cards
    |--------------------------------------------------------------------------
    */

    .supplier-status-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 13px;
    }

    .supplier-status-card {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 14px 15px;
        border: 1px solid var(--supplier-border);
        border-radius: 12px;
        background: #ffffff;
        color: var(--supplier-text);
        text-decoration: none;
        transition: 0.2s ease;
    }

    .supplier-status-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
    }

    .supplier-status-icon {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .supplier-status-card.active .supplier-status-icon {
        background: var(--supplier-green-soft);
        color: var(--supplier-green);
    }

    .supplier-status-card.inactive .supplier-status-icon {
        background: #f1f5f9;
        color: #475569;
    }

    .supplier-status-card.blocked .supplier-status-icon {
        background: var(--supplier-red-soft);
        color: var(--supplier-red);
    }

    .supplier-status-card.preferred .supplier-status-icon {
        background: var(--supplier-yellow-soft);
        color: var(--supplier-yellow);
    }

    .supplier-status-card span {
        display: block;
        margin-bottom: 3px;
        color: var(--supplier-muted);
        font-size: 9px;
    }

    .supplier-status-card strong {
        font-size: 18px;
    }

    /*
    |--------------------------------------------------------------------------
    | Main Panel
    |--------------------------------------------------------------------------
    */

    .suppliers-panel {
        border: 1px solid var(--supplier-border);
        border-radius: 17px;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .suppliers-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 21px 23px;
        border-bottom: 1px solid var(--supplier-border);
    }

    .suppliers-panel-header h2 {
        margin: 0 0 5px;
        color: var(--supplier-text);
        font-size: 19px;
    }

    .suppliers-panel-header p {
        margin: 0;
        color: var(--supplier-muted);
        font-size: 10px;
    }

    .supplier-result-count {
        min-width: 80px;
        padding: 8px 11px;
        border-radius: 9px;
        background: var(--supplier-indigo-soft);
        color: var(--supplier-indigo);
        font-size: 9px;
        font-weight: 700;
        text-align: center;
    }

    .supplier-result-count strong {
        margin-right: 3px;
        font-size: 15px;
    }

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    .supplier-filter-toolbar {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 16px 23px;
        border-bottom: 1px solid var(--supplier-border);
        background: #f8fafc;
    }

    .supplier-search {
        position: relative;
        width: min(100%, 315px);
        flex: 0 0 315px;
    }

    .supplier-search i {
        position: absolute;
        top: 50%;
        left: 13px;
        color: #94a3b8;
        font-size: 12px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .supplier-search input {
        width: 100%;
        height: 42px;
        padding: 0 13px 0 38px;
        border: 1px solid #d7dce5;
        border-radius: 9px;
        background: #ffffff;
        color: var(--supplier-text);
        font-family: inherit;
        font-size: 11px;
        outline: none;
    }

    .supplier-search input:focus,
    .supplier-filter-toolbar select:focus {
        border-color: var(--supplier-indigo);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .supplier-filter-toolbar select {
        min-width: 135px;
        height: 42px;
        padding: 0 29px 0 11px;
        border: 1px solid #d7dce5;
        border-radius: 9px;
        background: #ffffff;
        color: #374151;
        font-family: inherit;
        font-size: 10px;
        cursor: pointer;
        outline: none;
    }

    .supplier-filter-button,
    .supplier-reset-button {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 13px;
        border-radius: 9px;
        font-family: inherit;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }

    .supplier-filter-button {
        border: 1px solid var(--supplier-indigo);
        background: var(--supplier-indigo);
        color: #ffffff;
        cursor: pointer;
    }

    .supplier-reset-button {
        border: 1px solid #d7dce5;
        background: #ffffff;
        color: #475569;
    }

    .supplier-active-filters {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 11px 23px;
        border-bottom: 1px solid #c7d2fe;
        background: var(--supplier-indigo-soft);
        color: var(--supplier-indigo);
        font-size: 9px;
        font-weight: 700;
    }

    .supplier-active-filters div {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .supplier-active-filters a {
        color: var(--supplier-indigo);
        font-weight: 800;
        text-decoration: none;
    }

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    .supplier-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .supplier-table {
        width: 100%;
        min-width: 1450px;
        border-collapse: collapse;
    }

    .supplier-table th,
    .supplier-table td {
        padding: 14px 15px;
        border-bottom: 1px solid var(--supplier-soft-border);
        vertical-align: middle;
        text-align: left;
    }

    .supplier-table th {
        background: #f8fafc;
        color: var(--supplier-muted);
        font-size: 8px;
        font-weight: 800;
        letter-spacing: 0.055em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .supplier-table td {
        color: #374151;
        font-size: 10px;
    }

    .supplier-table tbody tr {
        transition: background 0.2s ease;
    }

    .supplier-table tbody tr:hover {
        background: #fafbff;
    }

    .supplier-table .number-column {
        text-align: right;
        white-space: nowrap;
    }

    .supplier-table .action-column {
        width: 88px;
        text-align: right;
    }

    /*
    |--------------------------------------------------------------------------
    | Supplier Information
    |--------------------------------------------------------------------------
    */

    .supplier-information {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 250px;
    }

    .supplier-avatar {
        width: 43px;
        height: 43px;
        flex: 0 0 43px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background:
            linear-gradient(
                135deg,
                var(--supplier-indigo-soft),
                #ddd6fe
            );
        color: var(--supplier-indigo);
        font-size: 15px;
        font-weight: 850;
    }

    .supplier-name-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 3px;
    }

    .supplier-name-row > a {
        max-width: 220px;
        overflow: hidden;
        color: var(--supplier-text);
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .supplier-name-row > a:hover {
        color: var(--supplier-indigo);
    }

    .preferred-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 6px;
        border-radius: 999px;
        background: var(--supplier-yellow-soft);
        color: var(--supplier-yellow);
        font-size: 7px;
        font-weight: 800;
    }

    .supplier-code {
        display: block;
        margin-bottom: 3px;
        color: var(--supplier-muted);
        font-size: 8px;
    }

    .supplier-website {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: var(--supplier-indigo);
        font-size: 8px;
        font-weight: 700;
        text-decoration: none;
    }

    /*
    |--------------------------------------------------------------------------
    | Contact and Location
    |--------------------------------------------------------------------------
    */

    .supplier-contact,
    .supplier-location,
    .supplier-terms {
        min-width: 150px;
    }

    .supplier-contact strong,
    .supplier-location strong,
    .supplier-terms strong {
        display: block;
        margin-bottom: 4px;
        color: var(--supplier-text);
        font-size: 10px;
    }

    .supplier-contact a,
    .supplier-contact span,
    .supplier-location span,
    .supplier-terms span,
    .supplier-terms small {
        display: block;
        margin-top: 3px;
        color: var(--supplier-muted);
        font-size: 8px;
        text-decoration: none;
    }

    .supplier-contact a i {
        width: 12px;
        margin-right: 2px;
    }

    .supplier-contact a:hover {
        color: var(--supplier-indigo);
    }

    .supplier-terms small {
        color: var(--supplier-indigo);
        font-weight: 800;
    }

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    .supplier-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 800;
        white-space: nowrap;
    }

    .supplier-status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .supplier-status-badge.active {
        background: var(--supplier-green-soft);
        color: var(--supplier-green);
    }

    .supplier-status-badge.inactive {
        background: #f1f5f9;
        color: #475569;
    }

    .supplier-status-badge.blocked {
        background: var(--supplier-red-soft);
        color: var(--supplier-red);
    }

    /*
    |--------------------------------------------------------------------------
    | Numbers and Rating
    |--------------------------------------------------------------------------
    */

    .supplier-order-count {
        display: block;
        color: var(--supplier-text);
        font-size: 12px;
    }

    .number-description {
        display: block;
        margin-top: 2px;
        color: var(--supplier-muted);
        font-size: 7px;
    }

    .supplier-spend-value {
        color: var(--supplier-green);
        font-size: 11px;
    }

    .supplier-rating {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 2px;
    }

    .supplier-rating-stars {
        display: flex;
        justify-content: flex-end;
        gap: 2px;
        color: #eab308;
        font-size: 8px;
    }

    .supplier-rating > strong {
        color: var(--supplier-text);
        font-size: 10px;
    }

    .supplier-rating > span {
        color: var(--supplier-muted);
        font-size: 7px;
    }

    .supplier-no-rating {
        display: inline-flex;
        padding: 5px 7px;
        border-radius: 7px;
        background: #f1f5f9;
        color: var(--supplier-muted);
        font-size: 8px;
        font-weight: 700;
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    .supplier-row-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
    }

    .supplier-row-action {
        width: 33px;
        height: 33px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--supplier-border);
        border-radius: 8px;
        background: #ffffff;
        color: #64748b;
        text-decoration: none;
        transition: 0.2s ease;
    }

    .supplier-row-action.view:hover {
        border-color: var(--supplier-indigo);
        background: var(--supplier-indigo);
        color: #ffffff;
    }

    .supplier-row-action.edit:hover {
        border-color: var(--supplier-blue);
        background: var(--supplier-blue);
        color: #ffffff;
    }

    /*
    |--------------------------------------------------------------------------
    | Empty State
    |--------------------------------------------------------------------------
    */

    .supplier-empty-state {
        padding: 55px 20px !important;
        text-align: center !important;
    }

    .supplier-empty-icon {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        border-radius: 15px;
        background: var(--supplier-indigo-soft);
        color: var(--supplier-indigo);
        font-size: 21px;
    }

    .supplier-empty-state h3 {
        margin: 0 0 6px;
        color: var(--supplier-text);
        font-size: 16px;
    }

    .supplier-empty-state p {
        margin: 0 0 14px;
        color: var(--supplier-muted);
        font-size: 10px;
    }

    .supplier-empty-state > a {
        min-height: 39px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 13px;
        border-radius: 9px;
        background: var(--supplier-indigo);
        color: #ffffff;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    .supplier-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 16px 22px;
        border-top: 1px solid var(--supplier-border);
        background: #ffffff;
    }

    .supplier-pagination-summary {
        color: var(--supplier-muted);
        font-size: 9px;
    }

    .supplier-pagination-summary strong {
        color: var(--supplier-text);
    }

    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1250px) {
        .suppliers-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .supplier-summary-grid,
        .supplier-status-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .supplier-filter-toolbar {
            align-items: stretch;
            flex-wrap: wrap;
        }

        .supplier-search {
            width: 100%;
            flex-basis: 100%;
        }

        .supplier-filter-toolbar select {
            flex: 1 1 150px;
        }
    }

    @media (max-width: 700px) {
        .suppliers-header {
            padding: 22px 18px;
        }

        .suppliers-header-content {
            align-items: flex-start;
        }

        .suppliers-header-icon {
            width: 48px;
            height: 48px;
            flex-basis: 48px;
        }

        .suppliers-header h1 {
            font-size: 24px;
        }

        .suppliers-header-actions,
        .supplier-header-button {
            width: 100%;
        }

        .suppliers-header-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .supplier-summary-grid,
        .supplier-status-grid {
            grid-template-columns: 1fr;
        }

        .suppliers-panel-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .supplier-result-count {
            width: 100%;
        }

        .supplier-filter-toolbar,
        .supplier-filter-toolbar select,
        .supplier-filter-button,
        .supplier-reset-button {
            width: 100%;
        }

        .supplier-active-filters {
            align-items: flex-start;
            flex-direction: column;
        }

        .supplier-pagination {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

@endpush