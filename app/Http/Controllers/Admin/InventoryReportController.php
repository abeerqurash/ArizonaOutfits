<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryReportController extends Controller
{
    /**
     * Display the inventory reports dashboard.
     */
    public function index(Request $request): View
    {
        $dateRange = $request->input('date_range', '30_days');

        [$startDate, $endDate] = $this->resolveDateRange(
            $dateRange,
            $request
        );

        /*
        |--------------------------------------------------------------------------
        | Inventory summary
        |--------------------------------------------------------------------------
        */

        $totalProducts = Product::query()->count();

        $totalVariants = ProductVariant::query()->count();

        $totalProductStock = (int) Product::query()
            ->sum('stock');

        $totalVariantStock = (int) ProductVariant::query()
            ->sum('stock');

        $totalStockUnits = $totalProductStock + $totalVariantStock;

        $outOfStockProducts = Product::query()
            ->where('stock', '<=', 0)
            ->count();

        $lowStockProducts = Product::query()
            ->where('stock', '>', 0)
            ->where('stock', '<=', 5)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Inventory movement query
        |--------------------------------------------------------------------------
        */

        $movementQuery = InventoryHistory::query()
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);

        $totalMovements = (clone $movementQuery)->count();

        $totalStockAdded = (int) (clone $movementQuery)
            ->where('quantity_change', '>', 0)
            ->sum('quantity_change');

        $totalStockRemoved = abs(
            (int) (clone $movementQuery)
                ->where('quantity_change', '<', 0)
                ->sum('quantity_change')
        );

        /*
        |--------------------------------------------------------------------------
        | Movement type totals
        |--------------------------------------------------------------------------
        */

        $movementTypeTotals = (clone $movementQuery)
            ->selectRaw(
                'movement_type, COUNT(*) as total_movements'
            )
            ->selectRaw(
                'SUM(quantity_change) as total_quantity_change'
            )
            ->groupBy('movement_type')
            ->orderByDesc('total_movements')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Most adjusted products
        |--------------------------------------------------------------------------
        */

        $mostAdjustedProducts = (clone $movementQuery)
            ->selectRaw(
                'product_id, COUNT(*) as movement_count'
            )
            ->selectRaw(
                'SUM(ABS(quantity_change)) as total_quantity_moved'
            )
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity_moved')
            ->with('product')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent inventory movements
        |--------------------------------------------------------------------------
        */

        $recentMovements = InventoryHistory::query()
            ->with([
                'product',
                'variant',
                'user',
                'order',
            ])
            ->latest()
            ->limit(15)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Low-stock product list
        |--------------------------------------------------------------------------
        */

        $lowStockItems = Product::query()
            ->where('stock', '>', 0)
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->limit(15)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Out-of-stock product list
        |--------------------------------------------------------------------------
        */

        $outOfStockItems = Product::query()
            ->where('stock', '<=', 0)
            ->latest('updated_at')
            ->limit(15)
            ->get();

        return view(
            'admin.inventory-reports.index',
            compact(
                'dateRange',
                'startDate',
                'endDate',
                'totalProducts',
                'totalVariants',
                'totalProductStock',
                'totalVariantStock',
                'totalStockUnits',
                'outOfStockProducts',
                'lowStockProducts',
                'totalMovements',
                'totalStockAdded',
                'totalStockRemoved',
                'movementTypeTotals',
                'mostAdjustedProducts',
                'recentMovements',
                'lowStockItems',
                'outOfStockItems'
            )
        );
    }

    /**
     * Resolve the selected report date range.
     */
    private function resolveDateRange(
        string $dateRange,
        Request $request
    ): array {
        $endDate = now()->endOfDay();

        return match ($dateRange) {
            'today' => [
                now()->startOfDay(),
                now()->endOfDay(),
            ],

            '7_days' => [
                now()->subDays(6)->startOfDay(),
                $endDate,
            ],

            '30_days' => [
                now()->subDays(29)->startOfDay(),
                $endDate,
            ],

            '90_days' => [
                now()->subDays(89)->startOfDay(),
                $endDate,
            ],

            'this_month' => [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ],

            'last_month' => [
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
            ],

            'custom' => [
                $request->filled('start_date')
                    ? Carbon::parse(
                        $request->input('start_date')
                    )->startOfDay()
                    : now()->subDays(29)->startOfDay(),

                $request->filled('end_date')
                    ? Carbon::parse(
                        $request->input('end_date')
                    )->endOfDay()
                    : $endDate,
            ],

            default => [
                now()->subDays(29)->startOfDay(),
                $endDate,
            ],
        };
    }
    public function export(Request $request): StreamedResponse
    {
        $dateRange = $request->input('date_range', '30_days');

        [$startDate, $endDate] = $this->resolveDateRange(
            $dateRange,
            $request
        );

        $rows = InventoryHistory::query()
            ->with([
                'product',
                'variant',
                'user',
                'order',
            ])
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ])
            ->latest()
            ->get();

        $filename = 'inventory-report-' . now()->format('Y-m-d-H-i-s') . '.csv';

        return response()->streamDownload(function () use ($rows) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Date',
                'Product',
                'Variant',
                'SKU',
                'Movement',
                'Before',
                'Change',
                'After',
                'User',
                'Reason',
                'Order'
            ]);

            foreach ($rows as $row) {

                fputcsv($handle, [

                    optional($row->created_at)->format('Y-m-d H:i'),

                    $row->product?->name
                        ?? $row->product?->title,

                    $row->variant?->name
                        ?? $row->variant?->title,

                    $row->product?->sku,

                    $row->movement_type,

                    $row->stock_before,

                    $row->quantity_change,

                    $row->stock_after,

                    $row->user?->name,

                    $row->reason,

                    $row->order?->order_number,

                ]);
            }

            fclose($handle);
        }, $filename);
    }
}
