@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('page-heading', 'Dashboard')

@section('content')
<div id="dashboardLoadingOverlay" class="dashboard-loading-overlay d-none">
    <div class="dashboard-loading-spinner"></div>
    <span>Loading dashboard...</span>
</div>
<div class="admin-page-header">

    <div>
        <span class="admin-page-eyebrow">
            Store overview
        </span>

        <h2>
            Welcome back, {{ auth()->user()?->name ?? 'Administrator' }}
        </h2>

        <p>
            Here is a live overview of your products, orders,
            customers, reviews and paid revenue.
        </p>
    </div>


    <div class="admin-page-actions">

        <a
            href="{{ route('admin.orders.index') }}"
            class="admin-button admin-button-secondary">
            <i class="fa-solid fa-bag-shopping"></i>
            View Orders
        </a>

        <a
            href="{{ route('admin.products.create') }}"
            class="admin-button admin-button-primary">
            <i class="fa-solid fa-plus"></i>
            Add Product
        </a>

    </div>

</div>
<div id="dashboard-content">
    @include('admin.dashboard.filter-bar')
    {{-- Dashboard statistic cards --}}
    <section class="admin-statistics-grid">

        <article class="admin-stat-card">

            <div class="admin-stat-card-top">

                <span class="admin-stat-icon admin-stat-icon-blue">
                    <i class="fa-solid fa-box-open"></i>
                </span>

                <span class="admin-stat-label">
                    Total Products
                </span>

            </div>

            <strong class="admin-stat-value">
                {{ number_format($statistics['products']) }}
            </strong>

            <a href="{{ route('admin.products.index') }}">
                Manage products
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </article>

        <article class="admin-stat-card">

            <div class="admin-stat-card-top">

                <span class="admin-stat-icon admin-stat-icon-purple">
                    <i class="fa-solid fa-bag-shopping"></i>
                </span>

                <span class="admin-stat-label">
                    Total Orders
                </span>

            </div>

            <strong class="admin-stat-value">
                {{ number_format($statistics['orders']) }}
            </strong>

            <a href="{{ route('admin.orders.index') }}">
                Manage orders
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </article>

        <article class="admin-stat-card">

            <div class="admin-stat-card-top">

                <span class="admin-stat-icon admin-stat-icon-orange">
                    <i class="fa-regular fa-clock"></i>
                </span>

                <span class="admin-stat-label">
                    Pending Orders
                </span>

            </div>

            <strong class="admin-stat-value">
                {{ number_format($statistics['pending_orders']) }}
            </strong>

            <a
                href="{{ route('admin.orders.index', [
                    'order_status' => 'pending',
                ]) }}">
                View pending orders
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </article>

        <article class="admin-stat-card">

            <div class="admin-stat-card-top">

                <span class="admin-stat-icon admin-stat-icon-green">
                    <i class="fa-solid fa-users"></i>
                </span>

                <span class="admin-stat-label">
                    Customers
                </span>

            </div>

            <strong class="admin-stat-value">
                {{ number_format($statistics['customers']) }}
            </strong>

            <a href="{{ route('admin.customers.index') }}">
                Manage customers
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </article>

        <article class="admin-stat-card">

            <div class="admin-stat-card-top">

                <span class="admin-stat-icon admin-stat-icon-yellow">
                    <i class="fa-solid fa-star"></i>
                </span>

                <span class="admin-stat-label">
                    Pending Reviews
                </span>

            </div>

            <strong class="admin-stat-value">
                {{ number_format($statistics['pending_reviews']) }}
            </strong>

            <a
                href="{{ route('admin.reviews.index', [
                    'status' => 'pending',
                ]) }}">
                Moderate reviews
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </article>

        <article class="admin-stat-card">

            <div class="admin-stat-card-top">

                <span class="admin-stat-icon admin-stat-icon-dark">
                    <i class="fa-solid fa-dollar-sign"></i>
                </span>

                <span class="admin-stat-label">
                    Paid Revenue
                </span>

            </div>

            <strong class="admin-stat-value">
                ${{ number_format(
                    (float) $statistics['revenue'],
                    2
                ) }}
            </strong>

            <a href="{{ route('admin.orders.index') }}">
                View paid orders
                <i class="fa-solid fa-arrow-right"></i>
            </a>

        </article>

    </section>

    <div id="dashboardAlertsContainer">
        @include('admin.dashboard.dashboard-alerts', [
        'dashboardAlerts' => $dashboardAlerts,
        'dashboardAlertSummary' => $dashboardAlertSummary,
        ])
    </div>

    <section class="admin-panel admin-sales-chart-panel">

        <div class="admin-panel-header">

            <div>
                <span class="admin-panel-eyebrow">
                    Sales performance
                </span>

                <h3>
                    Revenue and Orders
                </h3>

                <p class="admin-chart-description">
                    Monthly paid revenue and order activity during the
                    last 12 months.
                </p>
            </div>

            <div class="admin-chart-summary">

                <div>
                    <small>
                        {{ $chartSummary['current_month'] }} revenue
                    </small>

                    <strong>
                        ${{ number_format(
                        (float) $chartSummary['revenue'],
                        2
                    ) }}
                    </strong>
                </div>

                <div>
                    <small>
                        {{ $chartSummary['current_month'] }} orders
                    </small>

                    <strong>
                        {{ number_format(
                        $chartSummary['orders']
                    ) }}
                    </strong>
                </div>

            </div>

        </div>

        <div class="admin-chart-wrapper">

            <canvas
                id="adminSalesChart"
                aria-label="Monthly revenue and orders chart"
                role="img"></canvas>

        </div>

    </section>
    @include('admin.dashboard.analytics-widgets')

    <div id="customerAnalyticsContainer">
        @include('admin.dashboard.customer-analytics', [
        'customerAnalytics' => $customerAnalytics,
        'topCustomers' => $topCustomers,
        ])
    </div>
    {{-- Main dashboard columns --}}
    <section class="admin-dashboard-layout">

        <div class="admin-dashboard-main-column">

            {{-- Recent orders --}}
            <div class="admin-panel">

                <div class="admin-panel-header">

                    <div>
                        <span class="admin-panel-eyebrow">
                            Latest activity
                        </span>

                        <h3>
                            Recent Orders
                        </h3>
                    </div>

                    <a href="{{ route('admin.orders.index') }}">
                        View all orders
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </div>

                <div class="admin-table-wrapper">

                    <table class="admin-table">

                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody id="dashboardRecentOrdersBody">
                            @include('admin.dashboard.recent-orders-rows', [
                            'recentOrders' => $recentOrders,
                            ])
                        </tbody>

                    </table>

                </div>

            </div>

            {{-- Low-stock products --}}
            <div id="lowStockProductsContainer">

                @include('admin.dashboard.low-stock-products', [
                'lowStockProducts' => $lowStockProducts,
                'lowStockThreshold' => $lowStockThreshold,
                ])

            </div>

            {{-- Latest reviews --}}
            <div id="latestReviewsContainer">

                @include('admin.dashboard.latest-reviews', [
                'latestReviews' => $latestReviews
                ])

            </div>

        </div>

        <aside class="admin-dashboard-side-column">

            {{-- Order overview --}}

            <div id="orderOverviewContainer">

                @include('admin.dashboard.order-overview', [
                'orderStatusOverview' => $orderStatusOverview,
                ])

            </div>
            {{-- Best-selling products --}}
            <div id="bestSellersContainer">

                @include('admin.dashboard.best-sellers', [
                'bestSellingProducts' => $bestSellingProducts,
                ])

            </div>

            {{-- Quick actions --}}
            <div class="admin-panel">

                <div class="admin-panel-header">

                    <div>
                        <span class="admin-panel-eyebrow">
                            Shortcuts
                        </span>

                        <h3>
                            Quick Actions
                        </h3>
                    </div>

                </div>

                <div class="admin-quick-actions">

                    <a href="{{ route('admin.products.create') }}">

                        <span class="admin-quick-action-icon">
                            <i class="fa-solid fa-plus"></i>
                        </span>

                        <span>
                            <strong>Add Product</strong>
                            <small>Create a new store product</small>
                        </span>

                        <i class="fa-solid fa-chevron-right"></i>

                    </a>

                    <a href="{{ route('admin.orders.index') }}">

                        <span class="admin-quick-action-icon">
                            <i class="fa-solid fa-bag-shopping"></i>
                        </span>

                        <span>
                            <strong>Manage Orders</strong>
                            <small>Process customer orders</small>
                        </span>

                        <i class="fa-solid fa-chevron-right"></i>

                    </a>

                    <a href="{{ route('admin.coupons.create') }}">

                        <span class="admin-quick-action-icon">
                            <i class="fa-solid fa-ticket"></i>
                        </span>

                        <span>
                            <strong>Create Coupon</strong>
                            <small>Add a discount coupon</small>
                        </span>

                        <i class="fa-solid fa-chevron-right"></i>

                    </a>

                    <a href="{{ route('admin.posts.create') }}">

                        <span class="admin-quick-action-icon">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </span>

                        <span>
                            <strong>Create Blog Post</strong>
                            <small>Publish new content</small>
                        </span>

                        <i class="fa-solid fa-chevron-right"></i>

                    </a>

                    <a href="{{ route('admin.settings.edit') }}">

                        <span class="admin-quick-action-icon">
                            <i class="fa-solid fa-gear"></i>
                        </span>

                        <span>
                            <strong>Store Settings</strong>
                            <small>Payments, shipping and tax</small>
                        </span>

                        <i class="fa-solid fa-chevron-right"></i>

                    </a>

                </div>

            </div>

            {{-- Store sections --}}
            <div class="admin-panel">

                <div class="admin-panel-header">

                    <div>
                        <span class="admin-panel-eyebrow">
                            Management
                        </span>

                        <h3>
                            Store Sections
                        </h3>
                    </div>

                </div>

                <div class="admin-section-links">

                    <a href="{{ route('admin.product-categories.index') }}">
                        <i class="fa-solid fa-layer-group"></i>

                        <span>Product Categories</span>

                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <a href="{{ route('admin.product-tags.index') }}">
                        <i class="fa-solid fa-tags"></i>

                        <span>Product Tags</span>

                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <a href="{{ route('admin.reviews.index') }}">
                        <i class="fa-solid fa-star"></i>

                        <span>Customer Reviews</span>

                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <a href="{{ route('admin.customers.index') }}">
                        <i class="fa-solid fa-users"></i>

                        <span>Customers</span>

                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </div>

            </div>

        </aside>

    </section>
</div>
<style>
    .admin-sales-chart-panel {
        margin-bottom: 24px;
    }

    .admin-chart-description {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.6;
    }

    .admin-chart-summary {
        display: flex;
        align-items: center;
        gap: 28px;
    }

    .admin-chart-summary>div {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .admin-chart-summary small {
        color: #64748b;
        font-size: 12px;
    }

    .admin-chart-summary strong {
        color: #0f172a;
        font-size: 18px;
    }

    .admin-chart-wrapper {
        position: relative;
        width: 100%;
        height: 390px;
        padding: 20px 24px 24px;
    }

    @media (max-width: 900px) {
        .admin-sales-chart-panel .admin-panel-header {
            align-items: flex-start;
            flex-direction: column;
            gap: 18px;
        }

        .admin-chart-summary {
            width: 100%;
            justify-content: flex-start;
        }
    }

    @media (max-width: 600px) {
        .admin-chart-summary {
            align-items: stretch;
            flex-direction: column;
            gap: 12px;
        }

        .admin-chart-summary>div {
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        .admin-chart-wrapper {
            height: 320px;
            padding: 16px 12px 20px;
        }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script
    type="application/json"
    id="adminSalesChartData">
    @json($chartData)
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartCanvas = document.getElementById(
            'adminSalesChart'
        );

        const chartDataElement = document.getElementById(
            'adminSalesChartData'
        );

        if (
            !chartCanvas ||
            !chartDataElement ||
            typeof Chart === 'undefined'
        ) {
            return;
        }

        let chartData;

        try {
            chartData = JSON.parse(
                chartDataElement.textContent.trim()
            );
        } catch (error) {
            console.error(
                'Unable to parse dashboard chart data:',
                error
            );

            return;
        }

        window.adminSalesChart = new Chart(chartCanvas, {
            type: 'line',

            data: {
                labels: chartData.labels || [],

                datasets: [{
                        label: 'Paid Revenue',
                        data: chartData.revenue || [],
                        borderColor: '#111827',
                        backgroundColor: 'rgba(17, 24, 39, 0.10)',
                        borderWidth: 3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#111827',
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'revenueAxis'
                    },
                    {
                        label: 'Orders',
                        data: chartData.orders || [],
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124, 58, 237, 0.08)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#7c3aed',
                        fill: false,
                        tension: 0.35,
                        yAxisID: 'ordersAxis'
                    }
                ]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                interaction: {
                    mode: 'index',
                    intersect: false
                },

                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',

                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            boxHeight: 8,
                            padding: 20,

                            font: {
                                size: 12,
                                weight: '600'
                            }
                        }
                    },

                    tooltip: {
                        padding: 12,
                        displayColors: true,

                        callbacks: {
                            label: function(context) {
                                if (
                                    context.dataset.yAxisID ===
                                    'revenueAxis'
                                ) {
                                    const revenue = Number(
                                        context.parsed.y || 0
                                    );

                                    return (
                                        'Paid Revenue: $' +
                                        revenue.toLocaleString(
                                            'en-US', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2
                                            }
                                        )
                                    );
                                }

                                return (
                                    'Orders: ' +
                                    Number(
                                        context.parsed.y || 0
                                    ).toLocaleString('en-US')
                                );
                            }
                        }
                    }
                },

                scales: {
                    x: {
                        grid: {
                            display: false
                        },

                        ticks: {
                            color: '#64748b',
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 12
                        }
                    },

                    revenueAxis: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,

                        grid: {
                            color: 'rgba(148, 163, 184, 0.18)'
                        },

                        ticks: {
                            color: '#64748b',

                            callback: function(value) {
                                return (
                                    '$' +
                                    Number(value).toLocaleString(
                                        'en-US'
                                    )
                                );
                            }
                        },

                        title: {
                            display: true,
                            text: 'Paid Revenue'
                        }
                    },

                    ordersAxis: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,

                        grid: {
                            drawOnChartArea: false
                        },

                        ticks: {
                            color: '#64748b',
                            precision: 0
                        },

                        title: {
                            display: true,
                            text: 'Orders'
                        }
                    }
                }
            }
        });
    });
</script>
@endsection