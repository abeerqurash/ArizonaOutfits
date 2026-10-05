<div
    class="admin-dashboard-filter"
    id="adminDashboardFilter"
    data-filter-url="{{ route('admin.dashboard.filter') }}">
    <div class="admin-dashboard-filter-heading">
        <div>
            <span class="admin-panel-eyebrow">
                Dashboard period
            </span>

            <h3>
                Filter dashboard data
            </h3>
        </div>

        <div
            class="admin-dashboard-filter-status"
            id="dashboardFilterStatus"
            aria-live="polite"></div>
    </div>

    <div class="admin-dashboard-filter-buttons">

        <button
            type="button"
            class="admin-dashboard-filter-button"
            data-dashboard-range="today">
            Today
        </button>

        <button
            type="button"
            class="admin-dashboard-filter-button"
            data-dashboard-range="7days">
            Last 7 Days
        </button>

        <button
            type="button"
            class="admin-dashboard-filter-button"
            data-dashboard-range="30days">
            Last 30 Days
        </button>

        <button
            type="button"
            class="admin-dashboard-filter-button active"
            data-dashboard-range="month">
            This Month
        </button>

        <button
            type="button"
            class="admin-dashboard-filter-button"
            data-dashboard-range="year">
            This Year
        </button>

    </div>
</div>

<style>
    .admin-dashboard-filter {
        margin-bottom: 24px;
        padding: 20px 22px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
    }

    .admin-dashboard-filter-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 16px;
    }

    .admin-dashboard-filter-heading h3 {
        margin: 4px 0 0;
        color: #0f172a;
        font-size: 18px;
    }

    .admin-dashboard-filter-buttons {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .admin-dashboard-filter-button {
        appearance: none;
        padding: 10px 15px;
        background: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition:
            background-color 0.2s ease,
            border-color 0.2s ease,
            color 0.2s ease,
            transform 0.2s ease;
    }

    .admin-dashboard-filter-button:hover {
        color: #0f172a;
        border-color: #64748b;
        transform: translateY(-1px);
    }

    .admin-dashboard-filter-button.active {
        background: #111827;
        color: #ffffff;
        border-color: #111827;
    }

    .admin-dashboard-filter-button:disabled {
        cursor: wait;
        opacity: 0.65;
        transform: none;
    }

    .admin-dashboard-filter-status {
        min-height: 20px;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
    }

    .admin-dashboard-filter-status.is-loading {
        color: #7c3aed;
    }

    .admin-dashboard-filter-status.is-error {
        color: #dc2626;
    }

    .admin-dashboard-filter-status.is-success {
        color: #15803d;
    }

    @media (max-width: 700px) {
        .admin-dashboard-filter-heading {
            align-items: flex-start;
            flex-direction: column;
            gap: 8px;
        }

        .admin-dashboard-filter-buttons {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }

        .admin-dashboard-filter-button {
            width: 100%;
        }
    }

    @media (max-width: 430px) {
        .admin-dashboard-filter-buttons {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterContainer = document.getElementById(
            'adminDashboardFilter'
        );

        if (!filterContainer) {
            return;
        }

        const filterUrl = filterContainer.dataset.filterUrl;

        const buttons = Array.from(
            filterContainer.querySelectorAll(
                '[data-dashboard-range]'
            )
        );

        const statusElement = document.getElementById(
            'dashboardFilterStatus'
        );

        const numberFormatter = new Intl.NumberFormat('en-PK');

        const moneyFormatter = new Intl.NumberFormat('en-PK', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        function setStatus(message, type = '') {
            if (!statusElement) {
                return;
            }

            statusElement.textContent = message;

            statusElement.classList.remove(
                'is-loading',
                'is-error',
                'is-success'
            );

            if (type) {
                statusElement.classList.add('is-' + type);
            }
        }

        function setLoading(isLoading) {
            buttons.forEach(function(button) {
                button.disabled = isLoading;
            });

            filterContainer.classList.toggle(
                'is-loading',
                isLoading
            );
        }

        function findStatisticCard(label) {
            const cards = document.querySelectorAll(
                '.admin-stat-card'
            );

            for (const card of cards) {
                const labelElement = card.querySelector(
                    '.admin-stat-label'
                );

                if (
                    labelElement &&
                    labelElement.textContent.trim() === label
                ) {
                    return card;
                }
            }

            return null;
        }

        function updateStatistic(label, value, type = 'number') {
            const card = findStatisticCard(label);

            if (!card) {
                return;
            }

            const valueElement = card.querySelector(
                '.admin-stat-value'
            );

            if (!valueElement) {
                return;
            }

            if (type === 'money') {
                valueElement.textContent =
                    '$' + moneyFormatter.format(Number(value || 0));

                return;
            }

            valueElement.textContent = numberFormatter.format(
                Number(value || 0)
            );
        }

        function updateStatistics(statistics) {
            updateStatistic(
                'Total Products',
                statistics.products
            );

            updateStatistic(
                'Total Orders',
                statistics.orders
            );

            updateStatistic(
                'Pending Orders',
                statistics.pending_orders
            );

            updateStatistic(
                'Customers',
                statistics.customers
            );

            updateStatistic(
                'Pending Reviews',
                statistics.pending_reviews
            );

            updateStatistic(
                'Paid Revenue',
                statistics.revenue,
                'money'
            );
        }

        function updateChartSummary(summary) {
            const summaryItems = document.querySelectorAll(
                '.admin-chart-summary > div'
            );

            if (summaryItems.length < 2) {
                return;
            }

            const revenueLabel = summaryItems[0].querySelector(
                'small'
            );

            const revenueValue = summaryItems[0].querySelector(
                'strong'
            );

            const ordersLabel = summaryItems[1].querySelector(
                'small'
            );

            const ordersValue = summaryItems[1].querySelector(
                'strong'
            );

            if (revenueLabel) {
                revenueLabel.textContent =
                    summary.label + ' revenue';
            }

            if (revenueValue) {
                revenueValue.textContent =
                    '$' +
                    moneyFormatter.format(
                        Number(summary.revenue || 0)
                    );
            }

            if (ordersLabel) {
                ordersLabel.textContent =
                    summary.label + ' orders';
            }

            if (ordersValue) {
                ordersValue.textContent =
                    numberFormatter.format(
                        Number(summary.orders || 0)
                    );
            }
        }

        function updateChart(chartData) {
            if (
                typeof Chart === 'undefined' ||
                !chartData
            ) {
                return;
            }

            const chart = Chart.getChart('adminSalesChart');

            if (!chart) {
                return;
            }

            chart.data.labels = chartData.labels || [];

            if (chart.data.datasets[0]) {
                chart.data.datasets[0].data =
                    chartData.revenue || [];
            }

            if (chart.data.datasets[1]) {
                chart.data.datasets[1].data =
                    chartData.orders || [];
            }

            chart.update();
        }

        async function loadDashboardRange(button) {
            const range = button.dataset.dashboardRange;

            setLoading(true);
            setStatus('Updating dashboard…', 'loading');

            try {
                const requestUrl = new URL(
                    filterUrl,
                    window.location.origin
                );

                requestUrl.searchParams.set('range', range);

                const response = await fetch(
                    requestUrl.toString(), {
                        method: 'GET',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    }
                );

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(
                        data.message ||
                        'Dashboard data could not be loaded.'
                    );
                }

                updateStatistics(data.statistics);
                updateChartSummary(data.chart_summary);
                updateChart(data.chart_data);
                updateRecentOrders(data.recent_orders_html);
                updateDashboardAlerts(data.dashboard_alerts_html);
                updateLatestReviews(data.latest_reviews_html);
                updateOrderOverview(data.order_overview_html);
                updateBestSellers(data.best_sellers_html);
                updateLowStockProducts(
                    data.low_stock_products_html
                );
                updateCustomerAnalytics(
    response.customer_analytics_html
);

                buttons.forEach(function(filterButton) {
                    filterButton.classList.toggle(
                        'active',
                        filterButton === button
                    );
                });

                setStatus(
                    'Showing ' + data.range_label,
                    'success'
                );
            } catch (error) {
                console.error(
                    'Dashboard filter error:',
                    error
                );

                setStatus(
                    error.message ||
                    'Something went wrong while filtering.',
                    'error'
                );
            } finally {
                setLoading(false);
            }
        }

        buttons.forEach(function(button) {
            button.addEventListener('click', function() {
                if (
                    button.disabled ||
                    button.classList.contains('active')
                ) {
                    return;
                }

                loadDashboardRange(button);
            });
        });
    });

    function updateRecentOrders(html) {
        const recentOrdersBody = document.getElementById(
            'dashboardRecentOrdersBody'
        );

        if (!recentOrdersBody || typeof html !== 'string') {
            return;
        }

        recentOrdersBody.innerHTML = html;
    }

    function updateDashboardAlerts(html) {
        const alertsContainer = document.getElementById(
            'dashboardAlertsContainer'
        );

        if (
            !alertsContainer ||
            typeof html !== 'string'
        ) {
            return;
        }

        alertsContainer.innerHTML = html;
    }

    function updateLatestReviews(html) {
        const container = document.getElementById(
            "latestReviewsContainer"
        );

        if (!container || typeof html !== "string") {
            return;
        }

        container.innerHTML = html;
    }

    function updateOrderOverview(html) {
        const container = document.getElementById(
            'orderOverviewContainer'
        );

        if (container && typeof html === 'string') {
            container.innerHTML = html;
        }
    }

    function updateBestSellers(html) {
        const container = document.getElementById(
            'bestSellersContainer'
        );

        if (container && typeof html === 'string') {
            container.innerHTML = html;
        }
    }

    function updateLowStockProducts(html) {
        const container = document.getElementById(
            'lowStockProductsContainer'
        );

        if (container && typeof html === 'string') {
            container.innerHTML = html;
        }
    }

    function updateCustomerAnalytics(html) {
        const container = document.getElementById(
            'customerAnalyticsContainer'
        );

        if (container && typeof html === 'string') {
            container.innerHTML = html;
        }
    }
</script>