<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAnalyticsController extends AdminController
{
    private const PAID_STATUSES = ['paid', 'completed', 'succeeded'];
    private const EXCLUDED_ORDER_STATUSES = ['cancelled', 'refunded'];

    public function index(Request $request): View
    {
        [$start, $end] = $this->dateRange($request);
        $days = $start->diffInDays($end) + 1;
        $previousEnd = $start->copy()->subSecond();
        $previousStart = $previousEnd->copy()->subDays($days - 1)->startOfDay();

        $current = $this->summary($start, $end);
        $previous = $this->summary($previousStart, $previousEnd);

        $metrics = [
            'revenue' => $this->metric($current['revenue'], $previous['revenue']),
            'orders' => $this->metric($current['orders'], $previous['orders']),
            'average_order' => $this->metric($current['average_order'], $previous['average_order']),
            'customers' => $this->metric($current['customers'], $previous['customers']),
            'items' => $this->metric($current['items'], $previous['items']),
            'discounts' => $this->metric($current['discounts'], $previous['discounts']),
        ];

        $daily = $this->dailySeries($start, $end);
        $orderStatuses = $this->breakdown('order_status', $start, $end);
        $paymentMethods = $this->breakdown('payment_method', $start, $end);
        $topProducts = $this->topProducts($start, $end);
        $topCountries = $this->topCountries($start, $end);
        $customerSeries = $this->customerSeries($start, $end);

        $chartData = [
            'daily' => $daily,
            'statuses' => $orderStatuses,
            'payments' => $paymentMethods,
            'customers' => $customerSeries,
        ];

        return view('admin.analytics.index', compact(
            'start', 'end', 'metrics', 'chartData', 'topProducts', 'topCountries'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        [$start, $end] = $this->dateRange($request);
        $summary = $this->summary($start, $end);
        $daily = $this->dailySeries($start, $end);
        $products = $this->topProducts($start, $end, 100);

        return response()->streamDownload(function () use ($start, $end, $summary, $daily, $products): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Arizona Outfits Analytics Export']);
            fputcsv($output, ['Period', $start->toDateString() . ' to ' . $end->toDateString()]);
            fputcsv($output, []);
            fputcsv($output, ['Metric', 'Value']);
            foreach ($summary as $key => $value) fputcsv($output, [str($key)->headline(), $value]);
            fputcsv($output, []);
            fputcsv($output, ['Daily performance']);
            fputcsv($output, ['Date', 'Paid revenue', 'Orders']);
            foreach ($daily['labels'] as $index => $label) fputcsv($output, [$label, $daily['revenue'][$index], $daily['orders'][$index]]);
            fputcsv($output, []);
            fputcsv($output, ['Top products']);
            fputcsv($output, ['Product', 'SKU', 'Units sold', 'Sales']);
            foreach ($products as $product) fputcsv($output, [$product->product_title, $product->sku, $product->units, $product->sales]);
            fclose($output);
        }, 'store-analytics-' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function dateRange(Request $request): array
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $end = $request->filled('end_date') ? Carbon::parse($request->string('end_date')->value())->endOfDay() : now()->endOfDay();
        $start = $request->filled('start_date') ? Carbon::parse($request->string('start_date')->value())->startOfDay() : $end->copy()->subDays(29)->startOfDay();

        if ($start->greaterThan($end)) abort(422, 'The start date must be before the end date.');
        if ($start->diffInDays($end) > 366) abort(422, 'Analytics can cover a maximum of 366 days at once.');

        return [$start, $end];
    }

    private function paidOrders(Carbon $start, Carbon $end): Builder
    {
        return Order::query()->whereIn('payment_status', self::PAID_STATUSES)
            ->whereNotIn('order_status', self::EXCLUDED_ORDER_STATUSES)
            ->whereBetween('created_at', [$start, $end]);
    }

    private function summary(Carbon $start, Carbon $end): array
    {
        $paid = $this->paidOrders($start, $end);
        $revenue = (float) (clone $paid)->sum('total');
        $orders = (int) (clone $paid)->count();
        $orderIds = (clone $paid)->select('id');

        return [
            'revenue' => round($revenue, 2),
            'orders' => $orders,
            'average_order' => $orders > 0 ? round($revenue / $orders, 2) : 0,
            'customers' => User::query()->where('is_admin', false)->whereBetween('created_at', [$start, $end])->count(),
            'items' => (int) OrderItem::query()->whereIn('order_id', $orderIds)->sum('quantity'),
            'discounts' => round((float) (clone $paid)->sum('discount'), 2),
        ];
    }

    private function metric(float|int $current, float|int $previous): array
    {
        $change = $previous > 0 ? (($current - $previous) / $previous) * 100 : ($current > 0 ? 100 : 0);
        return ['value' => $current, 'previous' => $previous, 'change' => round($change, 1), 'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'same')];
    }

    private function dailySeries(Carbon $start, Carbon $end): array
    {
        $records = $this->paidOrders($start, $end)
            ->selectRaw('DATE(created_at) as sale_date, SUM(total) as revenue, COUNT(*) as orders')
            ->groupByRaw('DATE(created_at)')
            ->get()
            ->keyBy('sale_date');
        $labels = $revenue = $orders = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->toDateString(); $row = $records->get($key);
            $labels[] = $key; $revenue[] = round((float) ($row->revenue ?? 0), 2); $orders[] = (int) ($row->orders ?? 0);
        }
        return compact('labels', 'revenue', 'orders');
    }

    private function breakdown(string $column, Carbon $start, Carbon $end): array
    {
        $rows = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("{$column} as raw_label, COUNT(*) as total")
            ->groupBy($column)
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $rows->map(
                fn ($row) => filled($row->raw_label)
                    ? str($row->raw_label)->headline()->toString()
                    : 'Unknown'
            )->values(),
            'values' => $rows->pluck('total')
                ->map(fn ($value) => (int) $value)
                ->values(),
        ];
    }

    private function topProducts(Carbon $start, Carbon $end, int $limit = 10): Collection
    {
        return OrderItem::query()->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.payment_status', self::PAID_STATUSES)->whereNotIn('orders.order_status', self::EXCLUDED_ORDER_STATUSES)
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw('order_items.product_title, order_items.sku, SUM(order_items.quantity) as units, SUM(order_items.total) as sales')
            ->groupBy('order_items.product_title', 'order_items.sku')
            ->orderByDesc('sales')->limit($limit)->get()
            ->each(function ($product): void {
                $product->product_title = filled($product->product_title)
                    ? $product->product_title
                    : 'Deleted product';
                $product->sku = filled($product->sku) ? $product->sku : '—';
            });
    }

    private function topCountries(Carbon $start, Carbon $end): Collection
    {
        return $this->paidOrders($start, $end)
            ->selectRaw('shipping_country, billing_country, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('shipping_country', 'billing_country')
            ->get()
            ->groupBy(function ($row): string {
                return filled($row->shipping_country)
                    ? trim($row->shipping_country)
                    : (filled($row->billing_country)
                        ? trim($row->billing_country)
                        : 'Unknown');
            })
            ->map(function (Collection $rows, string $country) {
                return (object) [
                    'country' => $country,
                    'orders' => $rows->sum(fn ($row) => (int) $row->orders),
                    'revenue' => $rows->sum(fn ($row) => (float) $row->revenue),
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();
    }

    private function customerSeries(Carbon $start, Carbon $end): array
    {
        $rows = User::query()->where('is_admin', false)->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as joined_date, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->get()->keyBy('joined_date');
        $labels = $values = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) { $key = $date->toDateString(); $labels[] = $key; $values[] = (int) ($rows->get($key)->total ?? 0); }
        return compact('labels', 'values');
    }
}
