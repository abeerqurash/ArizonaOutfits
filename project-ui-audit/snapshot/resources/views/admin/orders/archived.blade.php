@extends('admin.layouts.app')

@section('title', 'Archived Orders')


@push('page-styles')
<style>
    /*
    |--------------------------------------------------------------------------
    | Archived Orders
    |--------------------------------------------------------------------------
    */

    .archived-orders-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }


    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    .archived-orders-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
    }

    .archived-orders-heading {
        min-width: 0;
    }

    .archived-orders-eyebrow {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 6px;
        color: #635bff;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .11em;
        text-transform: uppercase;
    }

    .archived-orders-eyebrow i {
        font-size: 9px;
    }

    .archived-orders-heading h1 {
        margin: 0;
        color: #172033;
        font-size: 24px;
        font-weight: 800;
        line-height: 1.2;
    }

    .archived-orders-heading p {
        max-width: 620px;
        margin: 7px 0 0;
        color: #64748b;
        font-size: 12px;
        line-height: 1.6;
    }

    .archived-orders-header-actions {
        display: flex;
        align-items: center;
        flex: 0 0 auto;
        gap: 8px;
    }

    .archived-orders-button {
        display: inline-flex;
        min-height: 38px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 13px;
        border: 1px solid #dfe4ec;
        border-radius: 8px;
        background: #fff;
        color: #334155;
        font-size: 11px;
        font-weight: 750;
        text-decoration: none;
        cursor: pointer;
        transition:
            border-color .18s ease,
            background .18s ease,
            color .18s ease,
            transform .18s ease,
            box-shadow .18s ease;
    }

    .archived-orders-button:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        color: #172033;
        transform: translateY(-1px);
    }

    .archived-orders-button i {
        font-size: 11px;
    }

    .archived-orders-button-primary {
        border-color: #635bff;
        background: #635bff;
        color: #fff;
    }

    .archived-orders-button-primary:hover {
        border-color: #554cf2;
        background: #554cf2;
        color: #fff;
        box-shadow: 0 6px 15px rgba(99, 91, 255, .18);
    }

    .archived-orders-button-success {
        border-color: #059669;
        background: #059669;
        color: #fff;
    }

    .archived-orders-button-success:hover {
        border-color: #047857;
        background: #047857;
        color: #fff;
        box-shadow: 0 6px 15px rgba(5, 150, 105, .16);
    }


    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    .archived-orders-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .archived-orders-stat {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 12px;
        padding: 15px;
        border: 1px solid #e4e8f0;
        border-radius: 11px;
        background: #fff;
        box-shadow: 0 2px 5px rgba(15, 23, 42, .025);
    }

    .archived-orders-stat-icon {
        display: grid;
        flex: 0 0 40px;
        width: 40px;
        height: 40px;
        place-items: center;
        border-radius: 10px;
        font-size: 14px;
    }

    .archived-orders-stat-icon.archived {
        background: #eef2ff;
        color: #635bff;
    }

    .archived-orders-stat-icon.paid {
        background: #ecfdf5;
        color: #059669;
    }

    .archived-orders-stat-icon.pending {
        background: #fffbeb;
        color: #d97706;
    }

    .archived-orders-stat-icon.refunded {
        background: #f1f5f9;
        color: #64748b;
    }

    .archived-orders-stat-copy {
        min-width: 0;
    }

    .archived-orders-stat-copy span {
        display: block;
        margin-bottom: 3px;
        color: #64748b;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .07em;
        text-transform: uppercase;
    }

    .archived-orders-stat-copy strong {
        display: block;
        color: #172033;
        font-size: 20px;
        font-weight: 800;
        line-height: 1.1;
    }


    /*
    |--------------------------------------------------------------------------
    | Panel
    |--------------------------------------------------------------------------
    */

    .archived-orders-panel {
        overflow: hidden;
        border: 1px solid #e4e8f0;
        border-radius: 11px;
        background: #fff;
        box-shadow: 0 2px 5px rgba(15, 23, 42, .025);
    }

    .archived-orders-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 14px 16px;
        border-bottom: 1px solid #edf0f5;
    }

    .archived-orders-panel-title {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 9px;
    }

    .archived-orders-panel-title-icon {
        display: grid;
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        place-items: center;
        border-radius: 8px;
        background: #eef2ff;
        color: #635bff;
        font-size: 11px;
    }

    .archived-orders-panel-title h2 {
        margin: 0;
        color: #172033;
        font-size: 14px;
        font-weight: 800;
    }

    .archived-orders-panel-title p {
        margin: 2px 0 0;
        color: #94a3b8;
        font-size: 10px;
    }

    .archived-orders-count {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 8px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }


    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    .archived-orders-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid #edf0f5;
        background: #fbfcfe;
    }

    .archived-orders-search-form {
        display: flex;
        min-width: 0;
        flex: 1 1 620px;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .archived-orders-search {
        position: relative;
        min-width: 0;
        flex: 1;
    }

    .archived-orders-search i {
        position: absolute;
        top: 50%;
        left: 12px;
        color: #94a3b8;
        font-size: 11px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .archived-orders-search input {
        width: 100%;
        min-height: 38px;
        box-sizing: border-box;
        padding: 0 12px 0 34px;
        border: 1px solid #dfe4ec;
        border-radius: 8px;
        outline: none;
        background: #fff;
        color: #172033;
        font-size: 11px;
        transition:
            border-color .18s ease,
            box-shadow .18s ease;
    }

    .archived-orders-search input::placeholder {
        color: #94a3b8;
    }

    .archived-orders-search input:focus {
        border-color: #8b84ff;
        box-shadow: 0 0 0 3px rgba(99, 91, 255, .09);
    }

    .archived-orders-toolbar-actions {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        gap: 8px;
    }


    /*
    |--------------------------------------------------------------------------
    | Bulk bar
    |--------------------------------------------------------------------------
    */

    .archived-orders-bulk {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 16px;
        border-bottom: 1px solid #edf0f5;
    }

    .archived-orders-bulk-info {
        display: flex;
        align-items: center;
        gap: 7px;
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
    }

    .archived-orders-bulk-info i {
        color: #635bff;
    }

    .archived-orders-selected-count {
        color: #172033;
        font-weight: 850;
    }


    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    .archived-orders-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .archived-orders-table {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
    }

    .archived-orders-table th {
        padding: 10px 12px;
        border-bottom: 1px solid #e9edf3;
        background: #f8fafc;
        color: #64748b;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .055em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .archived-orders-table td {
        padding: 12px;
        border-bottom: 1px solid #edf0f5;
        color: #475569;
        font-size: 11px;
        vertical-align: middle;
    }

    .archived-orders-table tbody tr {
        transition: background .15s ease;
    }

    .archived-orders-table tbody tr:hover {
        background: #fbfcff;
    }

    .archived-orders-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .archived-orders-check {
        width: 15px;
        height: 15px;
        margin: 0;
        accent-color: #635bff;
        cursor: pointer;
    }

    .archived-orders-order {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .archived-orders-order-icon {
        display: grid;
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        place-items: center;
        border-radius: 8px;
        background: #eef2ff;
        color: #635bff;
        font-size: 11px;
    }

    .archived-orders-order-copy {
        min-width: 0;
    }

    .archived-orders-order-number {
        display: block;
        margin-bottom: 2px;
        color: #172033;
        font-size: 11px;
        font-weight: 850;
    }

    .archived-orders-order-copy small,
    .archived-orders-customer small,
    .archived-orders-date small {
        display: block;
        color: #94a3b8;
        font-size: 9px;
        line-height: 1.45;
    }

    .archived-orders-customer strong {
        display: block;
        max-width: 190px;
        overflow: hidden;
        margin-bottom: 2px;
        color: #334155;
        font-size: 11px;
        font-weight: 750;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .archived-orders-customer small {
        max-width: 210px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .archived-orders-total {
        color: #172033;
        font-size: 11px;
        font-weight: 850;
        white-space: nowrap;
    }


    /*
    |--------------------------------------------------------------------------
    | Status pills
    |--------------------------------------------------------------------------
    */

    .archived-orders-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 7px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
    }

    .archived-orders-status::before {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
        content: "";
    }

    .archived-orders-status.success {
        background: #ecfdf5;
        color: #047857;
    }

    .archived-orders-status.warning {
        background: #fffbeb;
        color: #b45309;
    }

    .archived-orders-status.danger {
        background: #fff1f2;
        color: #be123c;
    }

    .archived-orders-status.info {
        background: #eef2ff;
        color: #554cf2;
    }

    .archived-orders-status.neutral {
        background: #f1f5f9;
        color: #64748b;
    }

    .archived-orders-date {
        color: #334155;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }


    /*
    |--------------------------------------------------------------------------
    | Restore
    |--------------------------------------------------------------------------
    */

    .archived-orders-restore {
        display: inline-flex;
        min-height: 31px;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 9px;
        border: 1px solid #bbf7d0;
        border-radius: 7px;
        background: #f0fdf4;
        color: #047857;
        font-size: 9px;
        font-weight: 850;
        white-space: nowrap;
        cursor: pointer;
        transition:
            background .18s ease,
            border-color .18s ease,
            transform .18s ease;
    }

    .archived-orders-restore:hover {
        border-color: #86efac;
        background: #dcfce7;
        transform: translateY(-1px);
    }


    /*
    |--------------------------------------------------------------------------
    | Empty state
    |--------------------------------------------------------------------------
    */

    .archived-orders-empty {
        padding: 58px 20px;
        text-align: center;
    }

    .archived-orders-empty-icon {
        display: grid;
        width: 50px;
        height: 50px;
        margin: 0 auto 12px;
        place-items: center;
        border-radius: 12px;
        background: #eef2ff;
        color: #635bff;
        font-size: 18px;
    }

    .archived-orders-empty h3 {
        margin: 0;
        color: #172033;
        font-size: 14px;
        font-weight: 800;
    }

    .archived-orders-empty p {
        max-width: 400px;
        margin: 6px auto 14px;
        color: #64748b;
        font-size: 11px;
        line-height: 1.6;
    }


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    .archived-orders-pagination {
        padding: 14px 16px;
        border-top: 1px solid #edf0f5;
    }


    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1050px) {
        .archived-orders-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 760px) {
        .archived-orders-header {
            align-items: stretch;
            flex-direction: column;
        }

        .archived-orders-header-actions {
            width: 100%;
        }

        .archived-orders-header-actions .archived-orders-button {
            width: 100%;
        }

        .archived-orders-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .archived-orders-search-form {
            flex: none;
            width: 100%;
        }

        .archived-orders-toolbar-actions {
            width: 100%;
        }

        .archived-orders-toolbar-actions .archived-orders-button {
            flex: 1;
        }
    }

    @media (max-width: 560px) {
        .archived-orders-page {
            gap: 14px;
        }

        .archived-orders-heading h1 {
            font-size: 20px;
        }

        .archived-orders-stats {
            grid-template-columns: 1fr;
        }

        .archived-orders-stat {
            padding: 13px;
        }

        .archived-orders-search-form {
            align-items: stretch;
            flex-direction: column;
        }

        .archived-orders-toolbar-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .archived-orders-bulk {
            align-items: stretch;
            flex-direction: column;
        }

        .archived-orders-bulk .archived-orders-button {
            width: 100%;
        }
    }
</style>
@endpush


@section('content')

<div class="archived-orders-page">


    {{-- ============================================================
        HEADER
    ============================================================ --}}

    <header class="archived-orders-header">

        <div class="archived-orders-heading">

            <div class="archived-orders-eyebrow">
                <i class="fa-solid fa-box-archive"></i>
                <span>Order Management</span>
            </div>

            <h1>Archived Orders</h1>

            <p>
                Review orders removed from the active workflow and restore
                them when required. Archived records remain preserved for
                operational and payment history.
            </p>

        </div>


        <div class="archived-orders-header-actions">

            <a
                href="{{ route('admin.orders.index') }}"
                class="archived-orders-button"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Active Orders
            </a>

        </div>

    </header>


    {{-- ============================================================
        STATISTICS
    ============================================================ --}}

    <section class="archived-orders-stats">

        <article class="archived-orders-stat">

            <div class="archived-orders-stat-icon archived">
                <i class="fa-solid fa-box-archive"></i>
            </div>

            <div class="archived-orders-stat-copy">
                <span>Total Archived</span>
                <strong>
                    {{ number_format($archivedStats['total'] ?? 0) }}
                </strong>
            </div>

        </article>


        <article class="archived-orders-stat">

            <div class="archived-orders-stat-icon paid">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div class="archived-orders-stat-copy">
                <span>Paid</span>
                <strong>
                    {{ number_format($archivedStats['paid'] ?? 0) }}
                </strong>
            </div>

        </article>


        <article class="archived-orders-stat">

            <div class="archived-orders-stat-icon pending">
                <i class="fa-solid fa-clock"></i>
            </div>

            <div class="archived-orders-stat-copy">
                <span>Pending Payment</span>
                <strong>
                    {{ number_format($archivedStats['pending'] ?? 0) }}
                </strong>
            </div>

        </article>


        <article class="archived-orders-stat">

            <div class="archived-orders-stat-icon refunded">
                <i class="fa-solid fa-arrow-rotate-left"></i>
            </div>

            <div class="archived-orders-stat-copy">
                <span>Refunded</span>
                <strong>
                    {{ number_format($archivedStats['refunded'] ?? 0) }}
                </strong>
            </div>

        </article>

    </section>


    {{-- ============================================================
        ARCHIVED ORDERS PANEL
    ============================================================ --}}

    <section class="archived-orders-panel">

        <div class="archived-orders-panel-header">

            <div class="archived-orders-panel-title">

                <div class="archived-orders-panel-title-icon">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>

                <div>
                    <h2>Archived Order Records</h2>

                    <p>
                        Preserved orders outside the active order queue.
                    </p>
                </div>

            </div>


            <div class="archived-orders-count">

                <i class="fa-solid fa-database"></i>

                {{ number_format($orders->total()) }}

                {{ \Illuminate\Support\Str::plural(
                    'record',
                    $orders->total()
                ) }}

            </div>

        </div>


        {{-- ========================================================
            SEARCH
        ======================================================== --}}

        <div class="archived-orders-toolbar">

            <form
                method="GET"
                action="{{ route('admin.orders.archived') }}"
                class="archived-orders-search-form"
            >

                <div class="archived-orders-search">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search order, tracking, customer, email or phone..."
                        autocomplete="off"
                    >

                </div>


                <div class="archived-orders-toolbar-actions">

                    <button
                        type="submit"
                        class="archived-orders-button archived-orders-button-primary"
                    >
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Search
                    </button>


                    @if(request()->filled('search'))

                        <a
                            href="{{ route('admin.orders.archived') }}"
                            class="archived-orders-button"
                        >
                            <i class="fa-solid fa-xmark"></i>
                            Clear
                        </a>

                    @endif

                </div>

            </form>

        </div>


        @if($orders->count())


            {{-- ====================================================
                BULK RESTORE FORM
            ==================================================== --}}

            <form
                method="POST"
                action="{{ route('admin.orders.bulk-restore') }}"
                id="archivedBulkRestoreForm"
            >

                @csrf
                @method('PATCH')


                <div class="archived-orders-bulk">

                    <div class="archived-orders-bulk-info">

                        <i class="fa-solid fa-circle-info"></i>

                        <span>
                            <span
                                class="archived-orders-selected-count"
                                id="archivedSelectedCount"
                            >
                                0
                            </span>

                            selected
                        </span>

                    </div>


                    <button
                        type="submit"
                        class="archived-orders-button archived-orders-button-success"
                        id="archivedBulkRestoreButton"
                    >
                        <i class="fa-solid fa-rotate-left"></i>
                        Restore Selected
                    </button>

                </div>


                {{-- =================================================
                    TABLE
                ================================================= --}}

                <div class="archived-orders-table-wrap">

                    <table class="archived-orders-table">

                        <thead>

                            <tr>

                                <th style="width:42px;">

                                    <input
                                        type="checkbox"
                                        id="archivedSelectAll"
                                        class="archived-orders-check"
                                        aria-label="Select all archived orders"
                                    >

                                </th>

                                <th>Order</th>

                                <th>Customer</th>

                                <th>Total</th>

                                <th>Payment</th>

                                <th>Order Status</th>

                                <th>Archived</th>

                                <th style="text-align:right;">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($orders as $order)

                                @php

                                    $customerName =
                                        $order->billing_name
                                        ?: $order->shipping_name
                                        ?: $order->user?->name
                                        ?: 'Guest';

                                    $customerEmail =
                                        $order->billing_email
                                        ?: $order->shipping_email
                                        ?: $order->user?->email
                                        ?: '';

                                    $paymentStatus = strtolower(
                                        (string) (
                                            $order->payment_status
                                            ?: 'pending'
                                        )
                                    );

                                    $orderStatus = strtolower(
                                        (string) (
                                            $order->order_status
                                            ?: 'pending'
                                        )
                                    );


                                    /*
                                     * Payment badge.
                                     */

                                    $paymentClass = match (true) {

                                        in_array(
                                            $paymentStatus,
                                            [
                                                'paid',
                                                'completed',
                                                'succeeded',
                                            ],
                                            true
                                        ) => 'success',

                                        $paymentStatus === 'pending'
                                            => 'warning',

                                        in_array(
                                            $paymentStatus,
                                            [
                                                'failed',
                                                'declined',
                                                'cancelled',
                                            ],
                                            true
                                        ) => 'danger',

                                        $paymentStatus === 'refunded'
                                            => 'neutral',

                                        default
                                            => 'neutral',
                                    };


                                    /*
                                     * Order badge.
                                     */

                                    $orderClass = match (true) {

                                        in_array(
                                            $orderStatus,
                                            [
                                                'delivered',
                                                'completed',
                                            ],
                                            true
                                        ) => 'success',

                                        in_array(
                                            $orderStatus,
                                            [
                                                'confirmed',
                                                'processing',
                                                'packed',
                                            ],
                                            true
                                        ) => 'info',

                                        in_array(
                                            $orderStatus,
                                            [
                                                'shipped',
                                                'out_for_delivery',
                                            ],
                                            true
                                        ) => 'info',

                                        $orderStatus === 'pending'
                                            => 'warning',

                                        in_array(
                                            $orderStatus,
                                            [
                                                'cancelled',
                                                'refunded',
                                            ],
                                            true
                                        ) => 'danger',

                                        default
                                            => 'neutral',
                                    };

                                @endphp


                                <tr>

                                    <td>

                                        <input
                                            type="checkbox"
                                            name="order_ids[]"
                                            value="{{ $order->id }}"
                                            class="archived-orders-check archived-order-checkbox"
                                            aria-label="Select order {{ $order->order_number ?: $order->id }}"
                                        >

                                    </td>


                                    {{-- Order --}}

                                    <td>

                                        <div class="archived-orders-order">

                                            <span class="archived-orders-order-icon">
                                                <i class="fa-solid fa-bag-shopping"></i>
                                            </span>


                                            <div class="archived-orders-order-copy">

                                                <span class="archived-orders-order-number">

                                                    {{ $order->order_number
                                                        ?: '#' . $order->id }}

                                                </span>


                                                <small>
                                                    ID #{{ $order->id }}
                                                </small>


                                                @if($order->tracking_number)

                                                    <small>
                                                        {{ $order->tracking_number }}
                                                    </small>

                                                @endif

                                            </div>

                                        </div>

                                    </td>


                                    {{-- Customer --}}

                                    <td>

                                        <div class="archived-orders-customer">

                                            <strong>
                                                {{ $customerName }}
                                            </strong>

                                            @if($customerEmail)

                                                <small title="{{ $customerEmail }}">
                                                    {{ $customerEmail }}
                                                </small>

                                            @else

                                                <small>
                                                    No email
                                                </small>

                                            @endif

                                        </div>

                                    </td>


                                    {{-- Total --}}

                                    <td>

                                        <span class="archived-orders-total">

                                            {{ strtoupper(
                                                $order->currency ?: 'USD'
                                            ) }}

                                            {{ number_format(
                                                (float) $order->total,
                                                2
                                            ) }}

                                        </span>

                                    </td>


                                    {{-- Payment --}}

                                    <td>

                                        <span
                                            class="archived-orders-status {{ $paymentClass }}"
                                        >

                                            {{ ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $paymentStatus
                                                )
                                            ) }}

                                        </span>

                                    </td>


                                    {{-- Order status --}}

                                    <td>

                                        <span
                                            class="archived-orders-status {{ $orderClass }}"
                                        >

                                            {{ ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $orderStatus
                                                )
                                            ) }}

                                        </span>

                                    </td>


                                    {{-- Archived --}}

                                    <td>

                                        <div class="archived-orders-date">

                                            @if($order->deleted_at)

                                                {{ $order->deleted_at->format(
                                                    'M d, Y'
                                                ) }}

                                                <small>
                                                    {{ $order->deleted_at->format(
                                                        'h:i A'
                                                    ) }}
                                                </small>

                                            @else

                                                —

                                            @endif

                                        </div>

                                    </td>


                                    {{-- Action --}}

                                    <td style="text-align:right;">

                                        <button
                                            type="button"
                                            class="archived-orders-restore"
                                            data-restore-button
                                            data-form="restoreArchivedOrder{{ $order->id }}"
                                            data-order="{{ $order->order_number ?: '#' . $order->id }}"
                                        >
                                            <i class="fa-solid fa-rotate-left"></i>
                                            Restore
                                        </button>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </form>


            {{-- ====================================================
                INDIVIDUAL RESTORE FORMS
            ==================================================== --}}

            @foreach($orders as $order)

                <form
                    id="restoreArchivedOrder{{ $order->id }}"
                    method="POST"
                    action="{{ route(
                        'admin.orders.restore',
                        $order->id
                    ) }}"
                    style="display:none;"
                >

                    @csrf
                    @method('PATCH')

                </form>

            @endforeach


            {{-- ====================================================
                PAGINATION
            ==================================================== --}}

            @if($orders->hasPages())

                <div class="archived-orders-pagination">
                    {{ $orders->links() }}
                </div>

            @endif


        @else


            {{-- ====================================================
                EMPTY
            ==================================================== --}}

            <div class="archived-orders-empty">

                <div class="archived-orders-empty-icon">

                    @if(request()->filled('search'))
                        <i class="fa-solid fa-magnifying-glass"></i>
                    @else
                        <i class="fa-solid fa-box-archive"></i>
                    @endif

                </div>


                @if(request()->filled('search'))

                    <h3>No matching archived orders</h3>

                    <p>
                        No archived order matched
                        “{{ request('search') }}”.
                        Try another order number, customer,
                        email or tracking number.
                    </p>

                    <a
                        href="{{ route('admin.orders.archived') }}"
                        class="archived-orders-button"
                    >
                        <i class="fa-solid fa-xmark"></i>
                        Clear Search
                    </a>

                @else

                    <h3>No archived orders</h3>

                    <p>
                        Archived orders will appear here while their
                        order, payment and operational history remains
                        preserved.
                    </p>

                    <a
                        href="{{ route('admin.orders.index') }}"
                        class="archived-orders-button"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        Active Orders
                    </a>

                @endif

            </div>

        @endif

    </section>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    'use strict';

    const selectAll =
        document.getElementById('archivedSelectAll');

    const checkboxes =
        Array.from(
            document.querySelectorAll(
                '.archived-order-checkbox'
            )
        );

    const selectedCount =
        document.getElementById(
            'archivedSelectedCount'
        );

    const bulkForm =
        document.getElementById(
            'archivedBulkRestoreForm'
        );


    /*
    |--------------------------------------------------------------------------
    | Selected count
    |--------------------------------------------------------------------------
    */

    function updateSelectedCount() {

        const checkedCount =
            checkboxes.filter(function (checkbox) {
                return checkbox.checked;
            }).length;

        if (selectedCount) {
            selectedCount.textContent =
                String(checkedCount);
        }

        if (selectAll) {

            selectAll.checked =
                checkboxes.length > 0 &&
                checkedCount === checkboxes.length;

            selectAll.indeterminate =
                checkedCount > 0 &&
                checkedCount < checkboxes.length;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Select all
    |--------------------------------------------------------------------------
    */

    if (selectAll) {

        selectAll.addEventListener(
            'change',
            function () {

                checkboxes.forEach(
                    function (checkbox) {
                        checkbox.checked =
                            selectAll.checked;
                    }
                );

                updateSelectedCount();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Individual checkboxes
    |--------------------------------------------------------------------------
    */

    checkboxes.forEach(
        function (checkbox) {

            checkbox.addEventListener(
                'change',
                updateSelectedCount
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Individual restore
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '[data-restore-button]'
        )
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    const formId =
                        button.dataset.form;

                    const order =
                        button.dataset.order;

                    const form =
                        document.getElementById(
                            formId
                        );

                    if (!form) {
                        return;
                    }

                    const confirmed =
                        window.confirm(
                            'Restore order ' +
                            order +
                            ' to the active order list?'
                        );

                    if (!confirmed) {
                        return;
                    }

                    form.submit();

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | Bulk restore
    |--------------------------------------------------------------------------
    */

    if (bulkForm) {

        bulkForm.addEventListener(
            'submit',
            function (event) {

                const selected =
                    checkboxes.filter(
                        function (checkbox) {
                            return checkbox.checked;
                        }
                    );

                if (selected.length === 0) {

                    event.preventDefault();

                    window.alert(
                        'Select at least one archived order to restore.'
                    );

                    return;

                }

                const confirmed =
                    window.confirm(
                        'Restore ' +
                        selected.length +
                        ' selected order' +
                        (
                            selected.length === 1
                                ? ''
                                : 's'
                        ) +
                        ' to the active order list?'
                    );

                if (!confirmed) {
                    event.preventDefault();
                }

            }
        );

    }


    updateSelectedCount();

});
</script>

@endsection