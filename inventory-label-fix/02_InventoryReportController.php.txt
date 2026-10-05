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

        $inventory = app(\App\Services\InventoryCatalogService::class)->rows();
        $totalProductStock = (int) $inventory->whereNull('inventory_variant_id')->sum('stock');
        $totalVariantStock = (int) $inventory->whereNotNull('inventory_variant_id')->sum('stock');
        $totalStockUnits = $totalProductStock + $totalVariantStock;
        $outOfStockProducts = $inventory->filter(fn ($item) => $item->stock <= 0)->count();
        $lowStockProducts = $inventory->filter(fn ($item) => $item->stock > 0 && $item->stock <= $item->reorder_point)->count();

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

        $recentMovements = (clone $movementQuery)
            ->with([
                'product',
                'variant',
                'user',
                'admin',
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

        $lowStockItems = $inventory->filter(fn ($item) => $item->stock > 0 && $item->stock <= $item->reorder_point)->sortBy('stock')->take(15)->values();
        $outOfStockItems = $inventory->filter(fn ($item) => $item->stock <= 0)->sortByDesc('updated_at')->take(15)->values();

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
        $request->validate([
            'date_range' => ['nullable', 'in:today,7_days,30_days,90_days,this_month,last_month,custom'],
            'start_date' => ['required_if:date_range,custom', 'nullable', 'date'],
            'end_date' => ['required_if:date_range,custom', 'nullable', 'date', 'after_or_equal:start_date'],
        ]);
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
                'admin',
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

                    $row->variant ? \App\Services\InventoryCatalogService::variantLabel($row->variant->options) : null,

                    $row->variant?->sku ?: $row->product?->sku,

                    $row->movement_type,

                    $row->stock_before,

                    $row->quantity_change,

                    $row->stock_after,

                    $row->performed_by,

                    $row->reason,

                    $row->order?->order_number,

                ]);
            }

            fclose($handle);
        }, $filename);
    }
}
