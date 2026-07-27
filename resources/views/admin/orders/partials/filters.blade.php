<form
    id="ordersFilterForm"
    action="{{ route('admin.orders.index') }}"
    method="GET"
    class="admin-orders-filter-form"
>

    <div class="admin-orders-search-group">

        <i class="fa-solid fa-magnifying-glass"></i>

        <input
            type="search"
            id="ordersSearchInput"
            name="search"
            value="{{ $filters['search'] ?? '' }}"
            placeholder="Order, customer, email, phone or tracking"
            autocomplete="off"
            aria-label="Search orders"
        >

    </div>

    <select
        name="order_status"
        id="ordersOrderStatus"
        aria-label="Filter by order status"
    >
        <option value="">All order statuses</option>

        @foreach ([
            'pending' => 'Pending',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'completed' => 'Completed',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ] as $value => $label)

            <option
                value="{{ $value }}"
                @selected(
                    ($filters['order_status'] ?? '') === $value
                )
            >
                {{ $label }}
            </option>

        @endforeach
    </select>

    <select
        name="payment_status"
        id="ordersPaymentStatus"
        aria-label="Filter by payment status"
    >
        <option value="">All payment statuses</option>

        @foreach ([
            'pending' => 'Pending',
            'paid' => 'Paid',
            'completed' => 'Completed',
            'succeeded' => 'Succeeded',
            'failed' => 'Failed',
            'declined' => 'Declined',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
        ] as $value => $label)

            <option
                value="{{ $value }}"
                @selected(
                    ($filters['payment_status'] ?? '') === $value
                )
            >
                {{ $label }}
            </option>

        @endforeach
    </select>

    <select
        name="date_range"
        id="ordersDateRange"
        aria-label="Filter orders by date"
    >
        <option value="">All dates</option>

        <option
            value="today"
            @selected(
                ($filters['date_range'] ?? '') === 'today'
            )
        >
            Today
        </option>

        <option
            value="7days"
            @selected(
                ($filters['date_range'] ?? '') === '7days'
            )
        >
            Last 7 days
        </option>

        <option
            value="30days"
            @selected(
                ($filters['date_range'] ?? '') === '30days'
            )
        >
            Last 30 days
        </option>

        <option
            value="month"
            @selected(
                ($filters['date_range'] ?? '') === 'month'
            )
        >
            This month
        </option>

        <option
            value="year"
            @selected(
                ($filters['date_range'] ?? '') === 'year'
            )
        >
            This year
        </option>

        <option
            value="custom"
            @selected(
                ($filters['date_range'] ?? '') === 'custom'
            )
        >
            Custom dates
        </option>
    </select>

    <select
        name="sort"
        id="ordersSort"
        aria-label="Sort orders"
    >
        <option
            value="newest"
            @selected(
                ($filters['sort'] ?? 'newest') === 'newest'
            )
        >
            Newest first
        </option>

        <option
            value="oldest"
            @selected(
                ($filters['sort'] ?? '') === 'oldest'
            )
        >
            Oldest first
        </option>

        <option
            value="total_high"
            @selected(
                ($filters['sort'] ?? '') === 'total_high'
            )
        >
            Highest total
        </option>

        <option
            value="total_low"
            @selected(
                ($filters['sort'] ?? '') === 'total_low'
            )
        >
            Lowest total
        </option>
    </select>

    <div
        id="ordersCustomDateFields"
        class="admin-orders-custom-date-fields {{
            ($filters['date_range'] ?? '') === 'custom'
                ? 'is-visible'
                : ''
        }}"
    >

        <div class="admin-orders-date-field">

            <label for="ordersDateFrom">
                From date
            </label>

            <input
                type="date"
                id="ordersDateFrom"
                name="date_from"
                value="{{ $filters['date_from'] ?? '' }}"
                max="{{ now()->format('Y-m-d') }}"
                @disabled(
                    ($filters['date_range'] ?? '') !== 'custom'
                )
            >

        </div>

        <div class="admin-orders-date-field">

            <label for="ordersDateTo">
                To date
            </label>

            <input
                type="date"
                id="ordersDateTo"
                name="date_to"
                value="{{ $filters['date_to'] ?? '' }}"
                max="{{ now()->format('Y-m-d') }}"
                @disabled(
                    ($filters['date_range'] ?? '') !== 'custom'
                )
            >

        </div>

    </div>

    <div class="admin-orders-filter-actions">

        <button type="submit">
            <i class="fa-solid fa-filter"></i>
            Apply
        </button>

        <a
            href="{{ route('admin.orders.index') }}"
            id="ordersResetFilters"
        >
            <i class="fa-solid fa-rotate-left"></i>
            Reset
        </a>

    </div>

</form>