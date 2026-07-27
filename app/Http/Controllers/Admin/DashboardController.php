<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DashboardController extends AdminController
{
    /**
     * Display the administrator dashboard.
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Reusable dates
        |--------------------------------------------------------------------------
        */

        $currentMonthStart = now()->copy()->startOfMonth();
        $currentMonthEnd = now()->copy()->endOfMonth();

        $previousMonthStart = now()
            ->copy()
            ->subMonthNoOverflow()
            ->startOfMonth();

        $previousMonthEnd = now()
            ->copy()
            ->subMonthNoOverflow()
            ->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | Main statistics
        |--------------------------------------------------------------------------
        */

        $statistics = [
            'products' => Product::query()->count(),

            'orders' => Order::query()->count(),

            'pending_orders' => Order::query()
                ->where('order_status', 'pending')
                ->count(),

            'customers' => User::query()
                ->where('is_admin', false)
                ->count(),

            'pending_reviews' => Review::query()
                ->where('status', 'pending')
                ->count(),

            'revenue' => (float) $this->paidRevenueQuery()
                ->sum('total'),
        ];

        /*
        |--------------------------------------------------------------------------
        | Revenue comparison
        |--------------------------------------------------------------------------
        */

        $currentMonthRevenue = (float) $this->paidRevenueQuery()
            ->whereBetween('created_at', [
                $currentMonthStart,
                $currentMonthEnd,
            ])
            ->sum('total');

        $previousMonthRevenue = (float) $this->paidRevenueQuery()
            ->whereBetween('created_at', [
                $previousMonthStart,
                $previousMonthEnd,
            ])
            ->sum('total');

        $revenueDifference = $currentMonthRevenue
            - $previousMonthRevenue;

        if ($previousMonthRevenue > 0) {
            $revenueGrowthPercentage = (
                $revenueDifference / $previousMonthRevenue
            ) * 100;
        } elseif ($currentMonthRevenue > 0) {
            $revenueGrowthPercentage = 100;
        } else {
            $revenueGrowthPercentage = 0;
        }

        $revenueComparison = [
            'current_month_label' => now()->format('F Y'),

            'previous_month_label' => now()
                ->copy()
                ->subMonthNoOverflow()
                ->format('F Y'),

            'current_month_revenue' => $currentMonthRevenue,

            'previous_month_revenue' => $previousMonthRevenue,

            'difference' => $revenueDifference,

            'growth_percentage' => round(
                $revenueGrowthPercentage,
                1
            ),

            'direction' => match (true) {
                $revenueDifference > 0 => 'up',
                $revenueDifference < 0 => 'down',
                default => 'same',
            },
        ];

        /*
        |--------------------------------------------------------------------------
        | Recent orders
        |--------------------------------------------------------------------------
        */

        $recentOrders = Order::query()
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Inventory health
        |--------------------------------------------------------------------------
        */

        $lowStockThreshold = 5;

        $inventoryCounts = [
            'in_stock' => Product::query()
                ->where('status', '!=', 'draft')
                ->where('stock', '>', $lowStockThreshold)
                ->count(),

            'low_stock' => Product::query()
                ->where('status', '!=', 'draft')
                ->where('stock', '>', 0)
                ->where('stock', '<=', $lowStockThreshold)
                ->count(),

            'out_of_stock' => Product::query()
                ->where('status', '!=', 'draft')
                ->where('stock', '<=', 0)
                ->count(),

            'draft' => Product::query()
                ->where('status', 'draft')
                ->count(),
        ];

        $totalInventoryProducts = array_sum($inventoryCounts);

        $inventoryHealth = [
            'total' => $totalInventoryProducts,

            'in_stock' => [
                'count' => $inventoryCounts['in_stock'],

                'percentage' => $this->calculatePercentage(
                    $inventoryCounts['in_stock'],
                    $totalInventoryProducts
                ),
            ],

            'low_stock' => [
                'count' => $inventoryCounts['low_stock'],

                'percentage' => $this->calculatePercentage(
                    $inventoryCounts['low_stock'],
                    $totalInventoryProducts
                ),
            ],

            'out_of_stock' => [
                'count' => $inventoryCounts['out_of_stock'],

                'percentage' => $this->calculatePercentage(
                    $inventoryCounts['out_of_stock'],
                    $totalInventoryProducts
                ),
            ],

            'draft' => [
                'count' => $inventoryCounts['draft'],

                'percentage' => $this->calculatePercentage(
                    $inventoryCounts['draft'],
                    $totalInventoryProducts
                ),
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Dashboard alerts
        |--------------------------------------------------------------------------
        */

        $failedPaymentsCount = Order::query()
            ->where(function (Builder $query) {
                $query
                    ->whereIn('payment_status', [
                        'failed',
                        'declined',
                        'cancelled',
                    ])
                    ->orWhereNotNull('payment_failed_at');
            })
            ->count();

        $dashboardAlerts = [];

        if ($inventoryCounts['out_of_stock'] > 0) {
            $dashboardAlerts[] = [
                'type' => 'danger',
                'icon' => 'fa-solid fa-circle-xmark',
                'title' => 'Products out of stock',

                'message' => $inventoryCounts['out_of_stock']
                    . ' product'
                    . ($inventoryCounts['out_of_stock'] === 1 ? '' : 's')
                    . ' currently have no available stock.',

                'action_label' => 'Manage products',
                'action_url' => route('admin.products.index'),
            ];
        }

        if ($inventoryCounts['low_stock'] > 0) {
            $dashboardAlerts[] = [
                'type' => 'warning',
                'icon' => 'fa-solid fa-triangle-exclamation',
                'title' => 'Low inventory warning',

                'message' => $inventoryCounts['low_stock']
                    . ' product'
                    . ($inventoryCounts['low_stock'] === 1 ? '' : 's')
                    . ' have '
                    . $lowStockThreshold
                    . ' or fewer units remaining.',

                'action_label' => 'Check inventory',
                'action_url' => route('admin.products.index'),
            ];
        }

        if ($statistics['pending_orders'] > 0) {
            $dashboardAlerts[] = [
                'type' => 'info',
                'icon' => 'fa-regular fa-clock',
                'title' => 'Orders require attention',

                'message' => $statistics['pending_orders']
                    . ' pending order'
                    . ($statistics['pending_orders'] === 1 ? '' : 's')
                    . ' are waiting to be processed.',

                'action_label' => 'View pending orders',

                'action_url' => route('admin.orders.index', [
                    'order_status' => 'pending',
                ]),
            ];
        }

        if ($statistics['pending_reviews'] > 0) {
            $dashboardAlerts[] = [
                'type' => 'notice',
                'icon' => 'fa-solid fa-star',
                'title' => 'Reviews awaiting moderation',

                'message' => $statistics['pending_reviews']
                    . ' customer review'
                    . ($statistics['pending_reviews'] === 1 ? '' : 's')
                    . ' require approval or rejection.',

                'action_label' => 'Moderate reviews',

                'action_url' => route('admin.reviews.index', [
                    'status' => 'pending',
                ]),
            ];
        }

        if ($failedPaymentsCount > 0) {
            $dashboardAlerts[] = [
                'type' => 'danger',
                'icon' => 'fa-solid fa-credit-card',
                'title' => 'Failed payment attempts',

                'message' => $failedPaymentsCount
                    . ' order'
                    . ($failedPaymentsCount === 1 ? '' : 's')
                    . ' have a failed or declined payment.',

                'action_label' => 'Review payments',

                'action_url' => route('admin.orders.index', [
                    'payment_status' => 'failed',
                ]),
            ];
        }

        $dashboardAlertSummary = [
            'total' => count($dashboardAlerts),

            'critical' => collect($dashboardAlerts)
                ->where('type', 'danger')
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Low-stock products
        |--------------------------------------------------------------------------
        */

        $lowStockProducts = $this->getLowStockProducts(
            $lowStockThreshold
        );

        /*
        |--------------------------------------------------------------------------
        | Best-selling products
        |--------------------------------------------------------------------------
        */

        $bestSellingProducts = $this->getBestSellingProducts();

        /*
|--------------------------------------------------------------------------
| Latest reviews
|--------------------------------------------------------------------------
*/

        $latestReviews = $this->getLatestReviews();
        /*
|--------------------------------------------------------------------------
| Order status overview
|--------------------------------------------------------------------------
*/

        $orderStatusOverview = $this->getOrderStatusOverview();

      





        /*
|--------------------------------------------------------------------------
| Customer analytics
|--------------------------------------------------------------------------
*/

        $customerAnalytics = $this->getCustomerAnalytics(
            $currentMonthStart,
            $currentMonthEnd
        );
        /*
|--------------------------------------------------------------------------
| Top customers
|--------------------------------------------------------------------------
*/

        $topCustomers = $this->getTopCustomers(
            $currentMonthStart,
            $currentMonthEnd
        );

        /*
        |--------------------------------------------------------------------------
        | Monthly chart
        |--------------------------------------------------------------------------
        */

        $chartLabels = [];
        $monthlyRevenue = [];
        $monthlyOrders = [];

        $firstChartMonth = now()
            ->copy()
            ->startOfMonth()
            ->subMonths(11);

        for ($monthIndex = 0; $monthIndex < 12; $monthIndex++) {
            $monthStart = $firstChartMonth
                ->copy()
                ->addMonths($monthIndex)
                ->startOfMonth();

            $monthEnd = $monthStart
                ->copy()
                ->endOfMonth();

            $chartLabels[] = $monthStart->format('M Y');

            $monthlyRevenue[] = (float) $this->paidRevenueQuery()
                ->whereBetween('created_at', [
                    $monthStart,
                    $monthEnd,
                ])
                ->sum('total');

            $monthlyOrders[] = Order::query()
                ->whereBetween('created_at', [
                    $monthStart,
                    $monthEnd,
                ])
                ->count();
        }

        $chartData = [
            'labels' => $chartLabels,
            'revenue' => $monthlyRevenue,
            'orders' => $monthlyOrders,
        ];

        $chartSummary = [
            'current_month' => now()->format('F Y'),

            'revenue' => $currentMonthRevenue,

            'orders' => Order::query()
                ->whereBetween('created_at', [
                    $currentMonthStart,
                    $currentMonthEnd,
                ])
                ->count(),
        ];

        return view('admin.dashboard', compact(
            'statistics',
            'recentOrders',
            'lowStockProducts',
            'bestSellingProducts',
            'latestReviews',
            'orderStatusOverview',
            'lowStockThreshold',
            'chartData',
            'chartSummary',
            'revenueComparison',
            'inventoryHealth',
            'dashboardAlerts',
            'dashboardAlertSummary',
            'customerAnalytics',
            'topCustomers'
        ));
    }

    /**
     * Query successfully paid, non-cancelled orders.
     */
    private function paidRevenueQuery(): Builder
    {
        return Order::query()
            ->whereIn('payment_status', [
                'paid',
                'completed',
                'succeeded',
            ])
            ->whereNotIn('order_status', [
                'cancelled',
                'refunded',
            ]);
    }

    /**
     * Calculate a safe percentage.
     */
    private function calculatePercentage(
        int $value,
        int $total
    ): float {
        if ($total <= 0) {
            return 0;
        }

        return round(($value / $total) * 100, 1);
    }

    /**
     * Return date-filtered dashboard data.
     */
    public function filter(Request $request)
    {
        $validated = $request->validate([
            'range' => [
                'nullable',
                'string',
                'in:today,7days,30days,month,year',
            ],
        ]);

        $range = $validated['range'] ?? 'month';

        $dateRange = $this->resolveDashboardDateRange($range);

        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        /*
    |--------------------------------------------------------------------------
    | Filtered statistics
    |--------------------------------------------------------------------------
    */

        $statistics = [
            'products' => Product::query()
                ->whereBetween('created_at', [
                    $startDate,
                    $endDate,
                ])
                ->count(),

            'orders' => Order::query()
                ->whereBetween('created_at', [
                    $startDate,
                    $endDate,
                ])
                ->count(),

            'pending_orders' => Order::query()
                ->where('order_status', 'pending')
                ->whereBetween('created_at', [
                    $startDate,
                    $endDate,
                ])
                ->count(),

            'customers' => User::query()
                ->where('is_admin', false)
                ->whereBetween('created_at', [
                    $startDate,
                    $endDate,
                ])
                ->count(),

            'pending_reviews' => Review::query()
                ->where('status', 'pending')
                ->whereBetween('created_at', [
                    $startDate,
                    $endDate,
                ])
                ->count(),

            'revenue' => (float) $this->paidRevenueQuery()
                ->whereBetween('created_at', [
                    $startDate,
                    $endDate,
                ])
                ->sum('total'),
        ];


        $recentOrders = Order::query()
            ->with('user')
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
            ->latest()
            ->limit(5)
            ->get();

        $recentOrdersHtml = view(
            'admin.dashboard.recent-orders-rows',
            compact('recentOrders')
        )->render();

        $alertData = $this->buildDashboardAlerts(
            $statistics,
            $startDate,
            $endDate
        );

        $dashboardAlerts = $alertData['alerts'];
        $dashboardAlertSummary = $alertData['summary'];

        $dashboardAlertsHtml = view(
            'admin.dashboard.dashboard-alerts',
            [
                'dashboardAlerts' => $dashboardAlerts,
                'dashboardAlertSummary' => $dashboardAlertSummary,
            ]
        )->render();

        $latestReviews = $this->getLatestReviews(
            $startDate,
            $endDate
        );

        $latestReviewsHtml = view(
            'admin.dashboard.latest-reviews',
            compact('latestReviews')
        )->render();

        $orderStatusOverview = $this->getOrderStatusOverview(
            $startDate,
            $endDate
        );

        $orderOverviewHtml = view(
            'admin.dashboard.order-overview',
            compact('orderStatusOverview')
        )->render();

        $bestSellingProducts = $this->getBestSellingProducts(
            $startDate,
            $endDate
        );

        $bestSellersHtml = view(
            'admin.dashboard.best-sellers',
            compact('bestSellingProducts')
        )->render();

        $lowStockThreshold = 5;

        $lowStockProducts = $this->getLowStockProducts(
            $lowStockThreshold
        );

        $lowStockProductsHtml = view(
            'admin.dashboard.low-stock-products',
            compact(
                'lowStockProducts',
                'lowStockThreshold'
            )
        )->render();

        /*
|--------------------------------------------------------------------------
| Customer analytics and top customers
|--------------------------------------------------------------------------
*/

        $customerAnalytics = $this->getCustomerAnalytics(
            $startDate,
            $endDate
        );

        $topCustomers = $this->getTopCustomers(
            $startDate,
            $endDate
        );

        $customerAnalyticsHtml = view(
            'admin.dashboard.customer-analytics',
            compact(
                'customerAnalytics',
                'topCustomers'
            )
        )->render();
        /*
    |--------------------------------------------------------------------------
    | Filtered chart
    |--------------------------------------------------------------------------
    */

        $chartData = $this->buildFilteredChartData(
            $range,
            $startDate,
            $endDate
        );

        $chartSummary = [
            'label' => $dateRange['label'],

            'revenue' => $statistics['revenue'],

            'orders' => $statistics['orders'],
        ];

        return response()->json([
            'success' => true,

            'range' => $range,

            'range_label' => $dateRange['label'],

            'statistics' => $statistics,

            'chart_data' => $chartData,

            'chart_summary' => $chartSummary,

            'recent_orders_html' => $recentOrdersHtml,

            'dashboard_alerts_html' => $dashboardAlertsHtml,

            'latest_reviews_html' => $latestReviewsHtml,

            'order_overview_html' => $orderOverviewHtml,

            'best_sellers_html' => $bestSellersHtml,

            'low_stock_products_html' => $lowStockProductsHtml,

            'customer_analytics_html' => $customerAnalyticsHtml,
        ]);
    }

    /**
     * Resolve the requested dashboard date range.
     */
    private function resolveDashboardDateRange(
        string $range
    ): array {
        return match ($range) {
            'today' => [
                'start' => now()->copy()->startOfDay(),
                'end' => now()->copy()->endOfDay(),
                'label' => 'Today',
            ],

            '7days' => [
                'start' => now()
                    ->copy()
                    ->subDays(6)
                    ->startOfDay(),

                'end' => now()->copy()->endOfDay(),

                'label' => 'Last 7 Days',
            ],

            '30days' => [
                'start' => now()
                    ->copy()
                    ->subDays(29)
                    ->startOfDay(),

                'end' => now()->copy()->endOfDay(),

                'label' => 'Last 30 Days',
            ],

            'year' => [
                'start' => now()->copy()->startOfYear(),
                'end' => now()->copy()->endOfDay(),
                'label' => 'This Year',
            ],

            default => [
                'start' => now()->copy()->startOfMonth(),
                'end' => now()->copy()->endOfDay(),
                'label' => 'This Month',
            ],
        };
    }

    /**
     * Build chart points for the selected dashboard period.
     */
    private function buildFilteredChartData(
        string $range,
        $startDate,
        $endDate
    ): array {
        $labels = [];
        $revenue = [];
        $orders = [];

        if ($range === 'today') {
            for ($hour = 0; $hour < 24; $hour++) {
                $periodStart = now()
                    ->copy()
                    ->startOfDay()
                    ->addHours($hour);

                $periodEnd = $periodStart
                    ->copy()
                    ->endOfHour();

                $labels[] = $periodStart->format('g A');

                $revenue[] = (float) $this->paidRevenueQuery()
                    ->whereBetween('created_at', [
                        $periodStart,
                        $periodEnd,
                    ])
                    ->sum('total');

                $orders[] = Order::query()
                    ->whereBetween('created_at', [
                        $periodStart,
                        $periodEnd,
                    ])
                    ->count();
            }

            return [
                'labels' => $labels,
                'revenue' => $revenue,
                'orders' => $orders,
            ];
        }

        if (in_array($range, ['7days', '30days', 'month'], true)) {
            $cursor = $startDate->copy()->startOfDay();

            while ($cursor->lte($endDate)) {
                $periodStart = $cursor->copy()->startOfDay();
                $periodEnd = $cursor->copy()->endOfDay();

                $labels[] = $periodStart->format('M d');

                $revenue[] = (float) $this->paidRevenueQuery()
                    ->whereBetween('created_at', [
                        $periodStart,
                        $periodEnd,
                    ])
                    ->sum('total');

                $orders[] = Order::query()
                    ->whereBetween('created_at', [
                        $periodStart,
                        $periodEnd,
                    ])
                    ->count();

                $cursor->addDay();
            }

            return [
                'labels' => $labels,
                'revenue' => $revenue,
                'orders' => $orders,
            ];
        }

        for ($month = 1; $month <= 12; $month++) {
            $periodStart = now()
                ->copy()
                ->startOfYear()
                ->month($month)
                ->startOfMonth();

            $periodEnd = $periodStart
                ->copy()
                ->endOfMonth();

            if ($periodStart->isFuture()) {
                break;
            }

            if ($periodEnd->gt(now())) {
                $periodEnd = now()->copy()->endOfDay();
            }

            $labels[] = $periodStart->format('M');

            $revenue[] = (float) $this->paidRevenueQuery()
                ->whereBetween('created_at', [
                    $periodStart,
                    $periodEnd,
                ])
                ->sum('total');

            $orders[] = Order::query()
                ->whereBetween('created_at', [
                    $periodStart,
                    $periodEnd,
                ])
                ->count();
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'orders' => $orders,
        ];
    }
    /**
     * Build dashboard alerts.
     */
    private function buildDashboardAlerts(
        array $statistics,
        ?\Carbon\CarbonInterface $startDate = null,
        ?\Carbon\CarbonInterface $endDate = null
    ): array {
        $lowStockThreshold = 5;

        /*
    |--------------------------------------------------------------------------
    | Inventory counts
    |--------------------------------------------------------------------------
    |
    | Inventory represents the current store state, so it is not filtered
    | by the selected order date range.
    |
    */

        $outOfStockCount = Product::query()
            ->where('status', '!=', 'draft')
            ->where('stock', '<=', 0)
            ->count();

        $lowStockCount = Product::query()
            ->where('status', '!=', 'draft')
            ->where('stock', '>', 0)
            ->where('stock', '<=', $lowStockThreshold)
            ->count();

        /*
    |--------------------------------------------------------------------------
    | Failed payments
    |--------------------------------------------------------------------------
    */

        $failedPaymentsQuery = Order::query()
            ->where(function (Builder $query) {
                $query
                    ->whereIn('payment_status', [
                        'failed',
                        'declined',
                        'cancelled',
                    ])
                    ->orWhereNotNull('payment_failed_at');
            });

        if ($startDate && $endDate) {
            $failedPaymentsQuery->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);
        }

        $failedPaymentsCount = $failedPaymentsQuery->count();

        /*
    |--------------------------------------------------------------------------
    | Alert collection
    |--------------------------------------------------------------------------
    */

        $dashboardAlerts = [];

        if ($outOfStockCount > 0) {
            $dashboardAlerts[] = [
                'type' => 'danger',
                'icon' => 'fa-solid fa-circle-xmark',
                'title' => 'Products out of stock',

                'message' => $outOfStockCount
                    . ' product'
                    . ($outOfStockCount === 1 ? '' : 's')
                    . ' currently have no available stock.',

                'action_label' => 'Manage products',

                'action_url' => route(
                    'admin.products.index'
                ),
            ];
        }

        if ($lowStockCount > 0) {
            $dashboardAlerts[] = [
                'type' => 'warning',
                'icon' => 'fa-solid fa-triangle-exclamation',
                'title' => 'Low inventory warning',

                'message' => $lowStockCount
                    . ' product'
                    . ($lowStockCount === 1 ? '' : 's')
                    . ' have '
                    . $lowStockThreshold
                    . ' or fewer units remaining.',

                'action_label' => 'Check inventory',

                'action_url' => route(
                    'admin.products.index'
                ),
            ];
        }

        if (($statistics['pending_orders'] ?? 0) > 0) {
            $pendingOrders = (int) $statistics['pending_orders'];

            $dashboardAlerts[] = [
                'type' => 'info',
                'icon' => 'fa-regular fa-clock',
                'title' => 'Orders require attention',

                'message' => $pendingOrders
                    . ' pending order'
                    . ($pendingOrders === 1 ? '' : 's')
                    . ' are waiting to be processed.',

                'action_label' => 'View pending orders',

                'action_url' => route(
                    'admin.orders.index',
                    [
                        'order_status' => 'pending',
                    ]
                ),
            ];
        }

        if (($statistics['pending_reviews'] ?? 0) > 0) {
            $pendingReviews = (int) $statistics['pending_reviews'];

            $dashboardAlerts[] = [
                'type' => 'notice',
                'icon' => 'fa-solid fa-star',
                'title' => 'Reviews awaiting moderation',

                'message' => $pendingReviews
                    . ' customer review'
                    . ($pendingReviews === 1 ? '' : 's')
                    . ' require approval or rejection.',

                'action_label' => 'Moderate reviews',

                'action_url' => route(
                    'admin.reviews.index',
                    [
                        'status' => 'pending',
                    ]
                ),
            ];
        }

        if ($failedPaymentsCount > 0) {
            $dashboardAlerts[] = [
                'type' => 'danger',
                'icon' => 'fa-solid fa-credit-card',
                'title' => 'Failed payment attempts',

                'message' => $failedPaymentsCount
                    . ' order'
                    . ($failedPaymentsCount === 1 ? '' : 's')
                    . ' have a failed or declined payment.',

                'action_label' => 'Review payments',

                'action_url' => route(
                    'admin.orders.index',
                    [
                        'payment_status' => 'failed',
                    ]
                ),
            ];
        }

        $dashboardAlertSummary = [
            'total' => count($dashboardAlerts),

            'critical' => collect($dashboardAlerts)
                ->where('type', 'danger')
                ->count(),
        ];

        return [
            'alerts' => $dashboardAlerts,
            'summary' => $dashboardAlertSummary,
        ];
    }

    private function getLatestReviews(
        ?\Carbon\CarbonInterface $startDate = null,
        ?\Carbon\CarbonInterface $endDate = null
    ) {
        $query = Review::query()->latest();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);
        }

        $reviewModel = new Review();

        $relationships = [];

        if (method_exists($reviewModel, 'product')) {
            $relationships[] = 'product';
        }

        if (method_exists($reviewModel, 'user')) {
            $relationships[] = 'user';
        }

        if (!empty($relationships)) {
            $query->with($relationships);
        }

        return $query
            ->limit(5)
            ->get();
    }
    private function getOrderStatusOverview(
        ?\Carbon\CarbonInterface $startDate = null,
        ?\Carbon\CarbonInterface $endDate = null
    ): array {

        $query = Order::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);
        }

        return [

            'pending' => (clone $query)
                ->where('order_status', 'pending')
                ->count(),

            'processing' => (clone $query)
                ->where('order_status', 'processing')
                ->count(),

            'completed' => (clone $query)
                ->whereIn('order_status', [
                    'completed',
                    'delivered',
                ])
                ->count(),

            'cancelled' => (clone $query)
                ->whereIn('order_status', [
                    'cancelled',
                    'refunded',
                ])
                ->count(),

        ];
    }

    private function getBestSellingProducts(
        ?\Carbon\CarbonInterface $startDate = null,
        ?\Carbon\CarbonInterface $endDate = null
    ) {
        $query = Product::query()
            ->where('purchase_count', '>', 0);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);
        }

        return $query
            ->orderByDesc('purchase_count')
            ->orderByDesc('views_count')
            ->limit(5)
            ->get();
    }

    private function getLowStockProducts(
        int $threshold = 5
    ) {
        return Product::query()
            ->where('status', '!=', 'draft')
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->orderBy('title')
            ->limit(5)
            ->get();
    }
    private function getCustomerAnalytics(
        ?\Carbon\CarbonInterface $startDate = null,
        ?\Carbon\CarbonInterface $endDate = null
    ): array {
        /*
    |--------------------------------------------------------------------------
    | New customers
    |--------------------------------------------------------------------------
    */

        $newCustomersQuery = User::query()
            ->where('is_admin', false);

        if ($startDate && $endDate) {
            $newCustomersQuery->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);
        }

        $newCustomersCount = $newCustomersQuery->count();

        /*
    |--------------------------------------------------------------------------
    | Paying customers
    |--------------------------------------------------------------------------
    */

        $payingCustomersQuery = DB::table('orders')
            ->whereNotNull('user_id')
            ->whereIn('payment_status', [
                'paid',
                'completed',
                'succeeded',
            ])
            ->whereNotIn('order_status', [
                'cancelled',
                'refunded',
            ]);

        if ($startDate && $endDate) {
            $payingCustomersQuery->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);
        }

        $payingCustomersCount = $payingCustomersQuery
            ->distinct()
            ->count('user_id');

        /*
    |--------------------------------------------------------------------------
    | Returning customers
    |--------------------------------------------------------------------------
    */

        $returningOrdersQuery = DB::table('orders')
            ->select('user_id')
            ->whereNotNull('user_id')
            ->whereIn('payment_status', [
                'paid',
                'completed',
                'succeeded',
            ])
            ->whereNotIn('order_status', [
                'cancelled',
                'refunded',
            ]);

        if ($startDate && $endDate) {
            $returningOrdersQuery->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);
        }

        $returningCustomersCount = DB::query()
            ->fromSub(
                $returningOrdersQuery
                    ->groupBy('user_id')
                    ->havingRaw('COUNT(*) >= 2'),
                'returning_customers'
            )
            ->count();

        /*
    |--------------------------------------------------------------------------
    | Revenue
    |--------------------------------------------------------------------------
    */

        $revenueQuery = $this->paidRevenueQuery();

        if ($startDate && $endDate) {
            $revenueQuery->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);
        }

        $revenue = (float) $revenueQuery->sum('total');

        $repeatPurchaseRate = $this->calculatePercentage(
            $returningCustomersCount,
            $payingCustomersCount
        );

        $averageCustomerSpend = $payingCustomersCount > 0
            ? $revenue / $payingCustomersCount
            : 0;

        return [
            'new_this_month' => $newCustomersCount,
            'returning_customers' => $returningCustomersCount,
            'paying_customers' => $payingCustomersCount,
            'repeat_purchase_rate' => $repeatPurchaseRate,
            'average_customer_spend' => round(
                $averageCustomerSpend,
                2
            ),
        ];
    }

    /**
     * Get top customers for the selected period.
     */
    private function getTopCustomers(
        ?\Carbon\CarbonInterface $startDate = null,
        ?\Carbon\CarbonInterface $endDate = null
    ) {
        $query = User::query()
            ->join(
                'orders',
                'users.id',
                '=',
                'orders.user_id'
            )
            ->where('users.is_admin', false)
            ->whereIn('orders.payment_status', [
                'paid',
                'completed',
                'succeeded',
            ])
            ->whereNotIn('orders.order_status', [
                'cancelled',
                'refunded',
            ]);

        if ($startDate && $endDate) {
            $query->whereBetween('orders.created_at', [
                $startDate,
                $endDate,
            ]);
        }

        return $query
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.created_at',
            ])
            ->selectRaw(
                'COUNT(orders.id) AS orders_count'
            )
            ->selectRaw(
                'COALESCE(SUM(orders.total), 0) AS total_spent'
            )
            ->selectRaw(
                'MAX(orders.created_at) AS last_order_at'
            )
            ->groupBy([
                'users.id',
                'users.name',
                'users.email',
                'users.created_at',
            ])
            ->orderByDesc('total_spent')
            ->orderByDesc('orders_count')
            ->limit(8)
            ->get()
            ->map(function ($customer) {
                $customer->orders_count =
                    (int) $customer->orders_count;

                $customer->total_spent =
                    (float) $customer->total_spent;

                $customer->average_order_value =
                    $customer->orders_count > 0
                    ? $customer->total_spent
                    / $customer->orders_count
                    : 0;

                return $customer;
            });
    }

    
}
