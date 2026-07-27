<section class="admin-customer-analytics">

    <div class="admin-customer-kpi-grid">

        <article class="admin-customer-kpi-card">

            <span class="admin-customer-kpi-icon">
                <i class="fa-solid fa-user-plus"></i>
            </span>

            <div>
                <small>New customers this month</small>

                <strong>
                    {{ number_format($customerAnalytics['new_this_month']) }}
                </strong>

                <p>
                    Customers registered during
                    {{ now()->format('F') }}
                </p>
            </div>

        </article>

        <article class="admin-customer-kpi-card">

            <span class="admin-customer-kpi-icon">
                <i class="fa-solid fa-rotate"></i>
            </span>

            <div>
                <small>Returning customers</small>

                <strong>
                    {{
                        number_format(
                            $customerAnalytics['returning_customers']
                        )
                    }}
                </strong>

                <p>
                    Customers with two or more paid orders
                </p>
            </div>

        </article>

        <article class="admin-customer-kpi-card">

            <span class="admin-customer-kpi-icon">
                <i class="fa-solid fa-chart-line"></i>
            </span>

            <div>
                <small>Repeat purchase rate</small>

                <strong>
                    {{
                        number_format(
                            $customerAnalytics['repeat_purchase_rate'],
                            1
                        )
                    }}%
                </strong>

                <p>
                    Based on customers who completed purchases
                </p>
            </div>

        </article>

        <article class="admin-customer-kpi-card">

            <span class="admin-customer-kpi-icon">
                <i class="fa-solid fa-wallet"></i>
            </span>

            <div>
                <small>Average customer spend</small>

                <strong>
                    Rs.
                    {{
                        number_format(
                            $customerAnalytics['average_customer_spend'],
                            2
                        )
                    }}
                </strong>

                <p>
                    Average paid revenue per customer
                </p>
            </div>

        </article>

    </div>

    <article class="admin-panel admin-top-customers-panel">

        <div class="admin-panel-header">

            <div>
                <span class="admin-panel-eyebrow">
                    Customer leaderboard
                </span>

                <h3>
                    Top Customers
                </h3>

                <p>
                    Customers ranked by successfully paid revenue.
                </p>
            </div>

            <span class="admin-top-customer-count">
                {{
                    number_format(
                        $customerAnalytics['paying_customers']
                    )
                }}
                paying customers
            </span>

        </div>

        @if ($topCustomers->count() > 0)

            <div class="admin-top-customers-table-wrapper">

                <table class="admin-top-customers-table">

                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Orders</th>
                            <th>Total spent</th>
                            <th>Average order</th>
                            <th>Last order</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($topCustomers as $customer)

                            @php
                                $customerName = trim(
                                    $customer->name ?: 'Customer'
                                );

                                $nameParts = preg_split(
                                    '/\s+/',
                                    $customerName
                                );

                                $initials = '';

                                foreach (
                                    array_slice($nameParts, 0, 2)
                                    as $namePart
                                ) {
                                    $initials .= mb_strtoupper(
                                        mb_substr($namePart, 0, 1)
                                    );
                                }

                                $lastOrderDate = $customer->last_order_at
                                    ? \Illuminate\Support\Carbon::parse(
                                        $customer->last_order_at
                                    )
                                    : null;
                            @endphp

                            <tr>

                                <td>

                                    <div class="admin-customer-identity">

                                        <span class="admin-customer-avatar">
                                            {{ $initials ?: 'C' }}
                                        </span>

                                        <div>
                                            <strong>
                                                {{ $customerName }}
                                            </strong>

                                            <small>
                                                {{ $customer->email }}
                                            </small>
                                        </div>

                                    </div>

                                </td>

                                <td>
                                    <strong>
                                        {{
                                            number_format(
                                                $customer->orders_count
                                            )
                                        }}
                                    </strong>
                                </td>

                                <td>
                                    <strong>
                                        Rs.
                                        {{
                                            number_format(
                                                $customer->total_spent,
                                                2
                                            )
                                        }}
                                    </strong>
                                </td>

                                <td>
                                    Rs.
                                    {{
                                        number_format(
                                            $customer->average_order_value,
                                            2
                                        )
                                    }}
                                </td>

                                <td>

                                    @if ($lastOrderDate)

                                        <span
                                            title="{{
                                                $lastOrderDate->format(
                                                    'd M Y, h:i A'
                                                )
                                            }}"
                                        >
                                            {{ $lastOrderDate->diffForHumans() }}
                                        </span>

                                    @else

                                        <span>—</span>

                                    @endif

                                </td>

                                <td>

                                    @if (
                                        \Illuminate\Support\Facades\Route::has(
                                            'admin.customers.show'
                                        )
                                    )

                                        <a
                                            class="admin-customer-view-button"
                                            href="{{
                                                route(
                                                    'admin.customers.show',
                                                    $customer->id
                                                )
                                            }}"
                                            title="View customer"
                                        >
                                            <i
                                                class="fa-solid
                                                fa-arrow-up-right-from-square"
                                            ></i>
                                        </a>

                                    @else

                                        <span
                                            class="
                                                admin-customer-view-button
                                                admin-customer-view-disabled
                                            "
                                            title="Customer profile coming soon"
                                        >
                                            <i class="fa-solid fa-user"></i>
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="admin-top-customers-empty">

                <span>
                    <i class="fa-solid fa-users"></i>
                </span>

                <strong>No customer purchases yet</strong>

                <p>
                    Customers will appear here after completing paid orders.
                </p>

            </div>

        @endif

    </article>

</section>

<style>
    .admin-customer-analytics {
        margin-bottom: 24px;
    }

    .admin-customer-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .admin-customer-kpi-card {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        min-width: 0;
        padding: 20px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 15px;
    }

    .admin-customer-kpi-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 44px;
        width: 44px;
        height: 44px;
        color: #4338ca;
        background: #eef2ff;
        border-radius: 12px;
        font-size: 17px;
    }

    .admin-customer-kpi-card > div {
        min-width: 0;
    }

    .admin-customer-kpi-card small {
        display: block;
        margin-bottom: 7px;
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
    }

    .admin-customer-kpi-card strong {
        display: block;
        color: #0f172a;
        font-size: 23px;
        line-height: 1.2;
    }

    .admin-customer-kpi-card p {
        margin: 7px 0 0;
        color: #94a3b8;
        font-size: 11px;
        line-height: 1.5;
    }

    .admin-top-customers-panel {
        overflow: hidden;
    }

    .admin-top-customers-panel .admin-panel-header p {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .admin-top-customer-count {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        color: #334155;
        background: #f1f5f9;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .admin-top-customers-table-wrapper {
        overflow-x: auto;
    }

    .admin-top-customers-table {
        width: 100%;
        min-width: 850px;
        border-collapse: collapse;
    }

    .admin-top-customers-table th {
        padding: 13px 18px;
        color: #64748b;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        font-size: 11px;
        font-weight: 700;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .admin-top-customers-table td {
        padding: 15px 18px;
        color: #475569;
        border-bottom: 1px solid #eef2f7;
        font-size: 13px;
        vertical-align: middle;
    }

    .admin-top-customers-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .admin-top-customers-table tbody tr:hover {
        background: #f8fafc;
    }

    .admin-customer-identity {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 220px;
    }

    .admin-customer-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 40px;
        width: 40px;
        height: 40px;
        color: #3730a3;
        background: #e0e7ff;
        border-radius: 50%;
        font-size: 13px;
        font-weight: 800;
    }

    .admin-customer-identity > div {
        min-width: 0;
    }

    .admin-customer-identity strong {
        display: block;
        max-width: 210px;
        overflow: hidden;
        color: #0f172a;
        font-size: 13px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .admin-customer-identity small {
        display: block;
        max-width: 210px;
        margin-top: 4px;
        overflow: hidden;
        color: #94a3b8;
        font-size: 11px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .admin-customer-view-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        color: #475569;
        background: #f1f5f9;
        border-radius: 9px;
        text-decoration: none;
    }

    a.admin-customer-view-button:hover {
        color: #ffffff;
        background: #0f172a;
    }

    .admin-customer-view-disabled {
        cursor: not-allowed;
        opacity: 0.55;
    }

    .admin-top-customers-empty {
        display: flex;
        align-items: center;
        flex-direction: column;
        padding: 46px 20px;
        text-align: center;
    }

    .admin-top-customers-empty > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        margin-bottom: 14px;
        color: #64748b;
        background: #f1f5f9;
        border-radius: 50%;
        font-size: 20px;
    }

    .admin-top-customers-empty strong {
        color: #0f172a;
        font-size: 15px;
    }

    .admin-top-customers-empty p {
        margin: 7px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    @media (max-width: 1200px) {
        .admin-customer-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 650px) {
        .admin-customer-kpi-grid {
            grid-template-columns: 1fr;
        }

        .admin-customer-kpi-card {
            padding: 16px;
        }
    }
</style>