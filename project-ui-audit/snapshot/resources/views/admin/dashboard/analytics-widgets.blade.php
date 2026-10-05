<section class="admin-analytics-widgets">

    {{-- Revenue comparison --}}
    <article class="admin-panel admin-analytics-widget">

        <div class="admin-panel-header">

            <div>
                <span class="admin-panel-eyebrow">
                    Revenue comparison
                </span>

                <h3>
                    Monthly Revenue
                </h3>
            </div>

            <span
                class="admin-growth-indicator
                    admin-growth-indicator-{{
                        $revenueComparison['direction']
                    }}"
            >
                @if ($revenueComparison['direction'] === 'up')
                    <i class="fa-solid fa-arrow-trend-up"></i>
                @elseif ($revenueComparison['direction'] === 'down')
                    <i class="fa-solid fa-arrow-trend-down"></i>
                @else
                    <i class="fa-solid fa-minus"></i>
                @endif

                {{
                    number_format(
                        abs($revenueComparison['growth_percentage']),
                        1
                    )
                }}%
            </span>

        </div>

        <div class="admin-revenue-comparison">

            <div class="admin-revenue-current">

                <small>
                    {{ $revenueComparison['current_month_label'] }}
                </small>

                <strong>
                    ${{ number_format(
                        $revenueComparison['current_month_revenue'],
                        2
                    ) }}
                </strong>

                <span>
                    Current-month paid revenue
                </span>

            </div>

            <div class="admin-revenue-previous">

                <small>
                    {{ $revenueComparison['previous_month_label'] }}
                </small>

                <strong>
                    ${{ number_format(
                        $revenueComparison['previous_month_revenue'],
                        2
                    ) }}
                </strong>

                <span>
                    Previous-month paid revenue
                </span>

            </div>

        </div>

        <div class="admin-revenue-difference">

            @if ($revenueComparison['direction'] === 'up')

                <i class="fa-solid fa-circle-arrow-up"></i>

                <span>
                    Revenue increased by
                    <strong>
                        ${{ number_format(
                            abs($revenueComparison['difference']),
                            2
                        ) }}
                    </strong>
                    compared with last month.
                </span>

            @elseif ($revenueComparison['direction'] === 'down')

                <i class="fa-solid fa-circle-arrow-down"></i>

                <span>
                    Revenue decreased by
                    <strong>
                        ${{ number_format(
                            abs($revenueComparison['difference']),
                            2
                        ) }}
                    </strong>
                    compared with last month.
                </span>

            @else

                <i class="fa-solid fa-circle-minus"></i>

                <span>
                    Revenue is unchanged from last month.
                </span>

            @endif

        </div>

    </article>

    {{-- Inventory health --}}
    <article class="admin-panel admin-analytics-widget">

        <div class="admin-panel-header">

            <div>
                <span class="admin-panel-eyebrow">
                    Stock overview
                </span>

                <h3>
                    Inventory Health
                </h3>
            </div>

            <span class="admin-inventory-total">
                {{ number_format($inventoryHealth['total']) }}
                products
            </span>

        </div>

        <div class="admin-inventory-health-list">

            <div class="admin-inventory-health-item">

                <div class="admin-inventory-health-heading">

                    <span>
                        <i class="fa-solid fa-circle-check"></i>
                        In Stock
                    </span>

                    <strong>
                        {{
                            number_format(
                                $inventoryHealth['in_stock']['count']
                            )
                        }}
                    </strong>

                </div>

                <div class="admin-inventory-progress">

                    <span
                        class="admin-inventory-progress-in-stock"
                        style="width: {{
                            $inventoryHealth['in_stock']['percentage']
                        }}%;"
                    ></span>

                </div>

                <small>
                    {{
                        number_format(
                            $inventoryHealth['in_stock']['percentage'],
                            1
                        )
                    }}% of all products
                </small>

            </div>

            <div class="admin-inventory-health-item">

                <div class="admin-inventory-health-heading">

                    <span>
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Low Stock
                    </span>

                    <strong>
                        {{
                            number_format(
                                $inventoryHealth['low_stock']['count']
                            )
                        }}
                    </strong>

                </div>

                <div class="admin-inventory-progress">

                    <span
                        class="admin-inventory-progress-low-stock"
                        style="width: {{
                            $inventoryHealth['low_stock']['percentage']
                        }}%;"
                    ></span>

                </div>

                <small>
                    {{
                        number_format(
                            $inventoryHealth['low_stock']['percentage'],
                            1
                        )
                    }}% of all products
                </small>

            </div>

            <div class="admin-inventory-health-item">

                <div class="admin-inventory-health-heading">

                    <span>
                        <i class="fa-solid fa-circle-xmark"></i>
                        Out of Stock
                    </span>

                    <strong>
                        {{
                            number_format(
                                $inventoryHealth['out_of_stock']['count']
                            )
                        }}
                    </strong>

                </div>

                <div class="admin-inventory-progress">

                    <span
                        class="admin-inventory-progress-out-of-stock"
                        style="width: {{
                            $inventoryHealth['out_of_stock']['percentage']
                        }}%;"
                    ></span>

                </div>

                <small>
                    {{
                        number_format(
                            $inventoryHealth['out_of_stock']['percentage'],
                            1
                        )
                    }}% of all products
                </small>

            </div>

            <div class="admin-inventory-health-item">

                <div class="admin-inventory-health-heading">

                    <span>
                        <i class="fa-solid fa-file-pen"></i>
                        Draft Products
                    </span>

                    <strong>
                        {{
                            number_format(
                                $inventoryHealth['draft']['count']
                            )
                        }}
                    </strong>

                </div>

                <div class="admin-inventory-progress">

                    <span
                        class="admin-inventory-progress-draft"
                        style="width: {{
                            $inventoryHealth['draft']['percentage']
                        }}%;"
                    ></span>

                </div>

                <small>
                    {{
                        number_format(
                            $inventoryHealth['draft']['percentage'],
                            1
                        )
                    }}% of all products
                </small>

            </div>

        </div>

    </article>

</section>

<style>
    .admin-analytics-widgets {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
        margin-bottom: 24px;
    }

    .admin-analytics-widget {
        min-width: 0;
    }

    .admin-growth-indicator,
    .admin-inventory-total {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
    }

    .admin-growth-indicator-up {
        color: #15803d;
        background: #dcfce7;
    }

    .admin-growth-indicator-down {
        color: #b91c1c;
        background: #fee2e2;
    }

    .admin-growth-indicator-same {
        color: #475569;
        background: #f1f5f9;
    }

    .admin-inventory-total {
        color: #334155;
        background: #f1f5f9;
    }

    .admin-revenue-comparison {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        padding: 24px;
    }

    .admin-revenue-current,
    .admin-revenue-previous {
        display: flex;
        flex-direction: column;
        gap: 7px;
        padding: 18px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
    }

    .admin-revenue-current {
        background: #f8fafc;
    }

    .admin-revenue-comparison small {
        color: #64748b;
        font-size: 12px;
        font-weight: 600;
    }

    .admin-revenue-comparison strong {
        color: #0f172a;
        font-size: 25px;
        line-height: 1.2;
    }

    .admin-revenue-comparison span {
        color: #64748b;
        font-size: 13px;
    }

    .admin-revenue-difference {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin: 0 24px 24px;
        padding: 14px;
        color: #475569;
        background: #f8fafc;
        border-radius: 12px;
        font-size: 13px;
        line-height: 1.6;
    }

    .admin-revenue-difference i {
        margin-top: 3px;
    }

    .admin-inventory-health-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
        padding: 24px;
    }

    .admin-inventory-health-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .admin-inventory-health-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .admin-inventory-health-heading span {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: #334155;
        font-size: 14px;
        font-weight: 600;
    }

    .admin-inventory-health-heading strong {
        color: #0f172a;
        font-size: 15px;
    }

    .admin-inventory-health-item small {
        color: #64748b;
        font-size: 12px;
    }

    .admin-inventory-progress {
        position: relative;
        width: 100%;
        height: 8px;
        overflow: hidden;
        background: #e2e8f0;
        border-radius: 999px;
    }

    .admin-inventory-progress span {
        display: block;
        min-width: 0;
        height: 100%;
        border-radius: inherit;
    }

    .admin-inventory-progress-in-stock {
        background: #22c55e;
    }

    .admin-inventory-progress-low-stock {
        background: #f59e0b;
    }

    .admin-inventory-progress-out-of-stock {
        background: #ef4444;
    }

    .admin-inventory-progress-draft {
        background: #64748b;
    }

    @media (max-width: 1050px) {
        .admin-analytics-widgets {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .admin-revenue-comparison {
            grid-template-columns: 1fr;
            padding: 16px;
        }

        .admin-revenue-difference {
            margin: 0 16px 16px;
        }

        .admin-inventory-health-list {
            padding: 16px;
        }
    }
</style>