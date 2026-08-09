<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use App\Exports\ReorderDashboardExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReorderDashboardController extends AdminController
{
    /**
     * Display inventory reorder recommendations.
     */
    public function index(): View
    {
        $reorderItems = $this->getReorderItems();

        $productsNeedReordering = $reorderItems
            ->where('needs_reorder', true)
            ->count();

        $outOfStockCount = $reorderItems
            ->where('stock_status', 'out')
            ->count();

        $lowStockCount = $reorderItems
            ->where('stock_status', 'low')
            ->count();

        $healthyCount = $reorderItems
            ->where('stock_status', 'healthy')
            ->count();

        $missingReorderPointCount = $reorderItems
            ->where('missing_reorder_point', true)
            ->count();

        $missingReorderQuantityCount = $reorderItems
            ->where('missing_reorder_quantity', true)
            ->count();

        $missingCostCount = $reorderItems
            ->where('missing_cost', true)
            ->count();

        $totalSuggestedUnits = (int) $reorderItems
            ->where('needs_reorder', true)
            ->sum('suggested_quantity');

        $estimatedPurchaseCost = (float) $reorderItems
            ->where('needs_reorder', true)
            ->sum('estimated_cost');

        /*
        |--------------------------------------------------------------------------
        | Highest priority reorder items
        |--------------------------------------------------------------------------
        */

        $urgentItems = $reorderItems
            ->where('needs_reorder', true)
            ->sortByDesc(function (array $item): int {
                return match ($item['urgency']) {
                    'critical' => 4,
                    'high' => 3,
                    'medium' => 2,
                    default => 1,
                };
            })
            ->take(10)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Reorder chart data
        |--------------------------------------------------------------------------
        */

        $reorderChartItems = $reorderItems
            ->where('needs_reorder', true)
            ->sortByDesc('suggested_quantity')
            ->take(10)
            ->values();

        $reorderChartLabels = $reorderChartItems
            ->pluck('item_name')
            ->values();

        $reorderChartData = $reorderChartItems
            ->pluck('suggested_quantity')
            ->map(
                fn($value): int => (int) $value
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Stock health chart
        |--------------------------------------------------------------------------
        */

        $stockHealthDistribution = [
            'Healthy' => $healthyCount,
            'Low Stock' => $lowStockCount,
            'Out Of Stock' => $outOfStockCount,
        ];

        /*
        |--------------------------------------------------------------------------
        | Purchase cost by ecommerce category
        |--------------------------------------------------------------------------
        */

        $categoryPurchaseCosts = [];

        foreach ($reorderItems as $item) {
            if (!$item['needs_reorder']) {
                continue;
            }

            $categories = $item['categories'];

            if ($categories->isEmpty()) {
                $categoryPurchaseCosts['Uncategorised'] =
                    (
                        $categoryPurchaseCosts['Uncategorised']
                        ?? 0
                    )
                    + $item['estimated_cost'];

                continue;
            }

            foreach ($categories as $category) {
                $categoryTitle = trim(
                    (string) ($category->title ?? '')
                );

                if ($categoryTitle === '') {
                    continue;
                }

                $categoryPurchaseCosts[$categoryTitle] =
                    (
                        $categoryPurchaseCosts[$categoryTitle]
                        ?? 0
                    )
                    + $item['estimated_cost'];
            }
        }

        $categoryPurchaseCosts = collect(
            $categoryPurchaseCosts
        )
            ->map(
                fn($value): float =>
                round((float) $value, 2)
            )
            ->sortDesc()
            ->take(10)
            ->all();

        return view(
            'admin.reorder-dashboard.index',
            compact(
                'reorderItems',
                'productsNeedReordering',
                'outOfStockCount',
                'lowStockCount',
                'healthyCount',
                'missingReorderPointCount',
                'missingReorderQuantityCount',
                'missingCostCount',
                'totalSuggestedUnits',
                'estimatedPurchaseCost',
                'urgentItems',
                'reorderChartLabels',
                'reorderChartData',
                'stockHealthDistribution',
                'categoryPurchaseCosts'
            )
        );
    }
    /**
     * Download reorder recommendations as a CSV purchase list.
     */
    public function exportCsv(
        Request $request
    ): StreamedResponse {
        $items = $this->getFilteredReorderItems(
            $request
        );

        $fileName =
            'reorder-purchase-list-'
            . now()->format('Y-m-d-His')
            . '.csv';

        $headers = [
            'Content-Type' =>
            'text/csv; charset=UTF-8',

            'Content-Disposition' =>
            'attachment; filename="' . $fileName . '"',

            'Cache-Control' =>
            'no-store, no-cache, must-revalidate',

            'Pragma' =>
            'no-cache',

            'Expires' =>
            '0',
        ];

        return response()->streamDownload(
            function () use (
                $items,
                $request
            ): void {
                $output = fopen(
                    'php://output',
                    'w'
                );

                if ($output === false) {
                    return;
                }

                /*
             * UTF-8 BOM allows Excel to display
             * pound symbols and special characters correctly.
             */
                fwrite(
                    $output,
                    "\xEF\xBB\xBF"
                );

                /*
            |--------------------------------------------------------------------------
            | Report information
            |--------------------------------------------------------------------------
            */

                fputcsv(
                    $output,
                    [
                        'Arizona Outfits',
                        'Inventory Reorder Purchase List',
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Generated',
                        now()->format('d M Y h:i A'),
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Search',
                        $request->string('search')
                            ->trim()
                            ->value()
                            ?: 'None',
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Stock Status',
                        $this->statusFilterLabel(
                            $request->string('status')->value()
                        ),
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Urgency',
                        $this->urgencyFilterLabel(
                            $request->string('urgency')->value()
                        ),
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Configuration',
                        $this->configurationFilterLabel(
                            $request->string('configuration')->value()
                        ),
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Sort Order',
                        $this->sortFilterLabel(
                            $request->string('sort')->value()
                        ),
                    ]
                );

                fputcsv(
                    $output,
                    []
                );

                /*
            |--------------------------------------------------------------------------
            | Summary
            |--------------------------------------------------------------------------
            */

                $totalSuggestedUnits = (int) $items
                    ->sum('suggested_quantity');

                $totalEstimatedCost = (float) $items
                    ->sum('estimated_cost');

                $outOfStockCount = $items
                    ->where('stock_status', 'out')
                    ->count();

                $lowStockCount = $items
                    ->where('stock_status', 'low')
                    ->count();

                fputcsv(
                    $output,
                    [
                        'Summary',
                        'Value',
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Items Exported',
                        $items->count(),
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Out Of Stock Items',
                        $outOfStockCount,
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Low Stock Items',
                        $lowStockCount,
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Total Suggested Units',
                        $totalSuggestedUnits,
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Estimated Purchase Cost',
                        number_format(
                            $totalEstimatedCost,
                            2,
                            '.',
                            ''
                        ),
                    ]
                );

                fputcsv(
                    $output,
                    []
                );

                /*
            |--------------------------------------------------------------------------
            | Purchase-list headings
            |--------------------------------------------------------------------------
            */

                fputcsv(
                    $output,
                    [
                        'Product / Variant',
                        'SKU',
                        'Type',
                        'Categories',
                        'Stock Status',
                        'Current Stock',
                        'Reorder Point',
                        'Configured Reorder Quantity',
                        'Suggested Purchase Quantity',
                        'Cost Price',
                        'Estimated Purchase Cost',
                        'Urgency',
                        'Missing Reorder Point',
                        'Missing Reorder Quantity',
                        'Missing Cost Price',
                    ]
                );

                /*
            |--------------------------------------------------------------------------
            | Purchase-list rows
            |--------------------------------------------------------------------------
            */

                foreach ($items as $item) {
                    $categoryNames = $item['categories']
                        ->pluck('title')
                        ->filter()
                        ->implode(', ');

                    fputcsv(
                        $output,
                        [
                            $item['item_name'],

                            $item['sku']
                                ?: 'Not assigned',

                            $item['is_variant']
                                ? 'Variant'
                                : 'Simple Product',

                            $categoryNames
                                ?: 'Uncategorised',

                            $item['stock_status_label'],

                            $item['stock'],

                            $item['missing_reorder_point']
                                ? 'Not set'
                                : $item['reorder_point'],

                            $item['missing_reorder_quantity']
                                ? 'Not set'
                                : $item['reorder_quantity'],

                            $item['suggested_quantity'],

                            $item['missing_cost']
                                ? 'Not set'
                                : number_format(
                                    $item['cost_price'],
                                    2,
                                    '.',
                                    ''
                                ),

                            number_format(
                                $item['estimated_cost'],
                                2,
                                '.',
                                ''
                            ),

                            $item['urgency_label'],

                            $item['missing_reorder_point']
                                ? 'Yes'
                                : 'No',

                            $item['missing_reorder_quantity']
                                ? 'Yes'
                                : 'No',

                            $item['missing_cost']
                                ? 'Yes'
                                : 'No',
                        ]
                    );
                }

                /*
            |--------------------------------------------------------------------------
            | Final totals
            |--------------------------------------------------------------------------
            */

                fputcsv(
                    $output,
                    []
                );

                fputcsv(
                    $output,
                    [
                        'Purchase Totals',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        $totalSuggestedUnits,
                        '',
                        number_format(
                            $totalEstimatedCost,
                            2,
                            '.',
                            ''
                        ),
                    ]
                );

                fclose($output);
            },
            $fileName,
            $headers
        );
    }

    /**
     * Download reorder recommendations as an Excel purchase list.
     */
    public function exportExcel(
        Request $request
    ): BinaryFileResponse {
        $items = $this->getFilteredReorderItems(
            $request
        );

        $fileName =
            'reorder-purchase-list-'
            . now()->format('Y-m-d-His')
            . '.xlsx';

        return Excel::download(
            new ReorderDashboardExport(
                $items
            ),
            $fileName
        );
    }

    /**
     * Return reorder items matching export filters.
     */
    private function getFilteredReorderItems(
        Request $request
    ): Collection {
        $search = strtolower(
            trim(
                $request->string('search')->value()
            )
        );

        $statusFilter = trim(
            $request->string('status')->value()
        );

        $urgencyFilter = trim(
            $request->string('urgency')->value()
        );

        $configurationFilter = trim(
            $request->string('configuration')->value()
        );

        $sortFilter = trim(
            $request->string('sort')->value()
        );

        $items = $this->getReorderItems()
            ->filter(
                function (
                    array $item
                ) use (
                    $search,
                    $statusFilter,
                    $urgencyFilter,
                    $configurationFilter
                ): bool {
                    /*
                |--------------------------------------------------------------------------
                | Search
                |--------------------------------------------------------------------------
                */

                    if ($search !== '') {
                        $searchText = strtolower(
                            trim(
                                $item['item_name']
                                    . ' '
                                    . ($item['sku'] ?? '')
                            )
                        );

                        if (
                            !str_contains(
                                $searchText,
                                $search
                            )
                        ) {
                            return false;
                        }
                    }

                    /*
                |--------------------------------------------------------------------------
                | Stock status
                |--------------------------------------------------------------------------
                */

                    if (
                        $statusFilter === 'reorder'
                        && !$item['needs_reorder']
                    ) {
                        return false;
                    }

                    if (
                        in_array(
                            $statusFilter,
                            [
                                'healthy',
                                'low',
                                'out',
                            ],
                            true
                        )
                        && $item['stock_status']
                        !== $statusFilter
                    ) {
                        return false;
                    }

                    /*
                |--------------------------------------------------------------------------
                | Urgency
                |--------------------------------------------------------------------------
                */

                    if (
                        $urgencyFilter !== ''
                        && $item['urgency']
                        !== $urgencyFilter
                    ) {
                        return false;
                    }

                    /*
                |--------------------------------------------------------------------------
                | Configuration status
                |--------------------------------------------------------------------------
                */

                    if (
                        $configurationFilter
                        === 'missing-point'
                        && !$item['missing_reorder_point']
                    ) {
                        return false;
                    }

                    if (
                        $configurationFilter
                        === 'missing-quantity'
                        && !$item['missing_reorder_quantity']
                    ) {
                        return false;
                    }

                    if (
                        $configurationFilter
                        === 'missing-cost'
                        && !$item['missing_cost']
                    ) {
                        return false;
                    }

                    return true;
                }
            );

        /*
    |--------------------------------------------------------------------------
    | Export sorting
    |--------------------------------------------------------------------------
    */

        $urgencyOrder = [
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'normal' => 1,
        ];

        $items = match ($sortFilter) {
            'urgency' => $items->sortByDesc(
                function (
                    array $item
                ) use (
                    $urgencyOrder
                ): int {
                    return $urgencyOrder[$item['urgency']] ?? 0;
                }
            ),

            'cost' => $items->sortByDesc(
                'estimated_cost'
            ),

            'quantity' => $items->sortByDesc(
                'suggested_quantity'
            ),

            'stock' => $items->sortBy(
                'stock'
            ),

            default => $items->sortBy(
                function (array $item): string {
                    return strtolower(
                        $item['item_name']
                    );
                }
            ),
        };

        return $items->values();
    }

    /**
     * Convert status filter value into readable text.
     */
    private function statusFilterLabel(
        string $status
    ): string {
        return match ($status) {
            'reorder' => 'Needs Reorder',
            'healthy' => 'Healthy',
            'low' => 'Low Stock',
            'out' => 'Out Of Stock',
            default => 'All Stock Statuses',
        };
    }

    /**
     * Convert urgency filter value into readable text.
     */
    private function urgencyFilterLabel(
        string $urgency
    ): string {
        return match ($urgency) {
            'critical' => 'Critical',
            'high' => 'High',
            'medium' => 'Medium',
            'normal' => 'Normal',
            default => 'All Urgency Levels',
        };
    }

    /**
     * Convert configuration filter value into readable text.
     */
    private function configurationFilterLabel(
        string $configuration
    ): string {
        return match ($configuration) {
            'missing-point' =>
            'Missing Reorder Point',

            'missing-quantity' =>
            'Missing Reorder Quantity',

            'missing-cost' =>
            'Missing Cost Price',

            default =>
            'All Configuration Statuses',
        };
    }

    /**
     * Convert sort filter value into readable text.
     */
    private function sortFilterLabel(
        string $sort
    ): string {
        return match ($sort) {
            'urgency' => 'Highest Urgency',
            'cost' => 'Highest Purchase Cost',
            'quantity' =>
            'Highest Suggested Quantity',

            'stock' => 'Lowest Stock',
            default => 'Product Name A–Z',
        };
    }
    /**
     * Build reorder information for simple products and variants.
     */
    private function getReorderItems(): Collection
    {
        $products = Product::query()
            ->with([
                'categories',
                'variants',
            ])
            ->orderBy('title')
            ->get();

        $items = collect();

        foreach ($products as $product) {
            /*
             * Products with variants use variant-level inventory.
             * This prevents the parent product from being listed twice.
             */
            if ($product->variants->isNotEmpty()) {
                foreach ($product->variants as $variant) {
                    $items->push(
                        $this->buildReorderItem(
                            product: $product,
                            variant: $variant
                        )
                    );
                }

                continue;
            }

            /*
             * Simple product without variants.
             */
            $items->push(
                $this->buildReorderItem(
                    product: $product,
                    variant: null
                )
            );
        }

        return $items
            ->sortBy(function (array $item): string {
                return strtolower(
                    $item['item_name']
                );
            })
            ->values();
    }

    /**
     * Build one reorder row.
     */
    private function buildReorderItem(
        Product $product,
        mixed $variant
    ): array {
        $isVariant = $variant !== null;

        $stock = max(
            0,
            (int) (
                $isVariant
                ? $variant->stock
                : $product->stock
            )
        );

        $reorderPointValue = $isVariant
            ? $variant->reorder_point
            : $product->reorder_point;

        $reorderQuantityValue = $isVariant
            ? $variant->reorder_quantity
            : $product->reorder_quantity;

        $missingReorderPoint =
            $reorderPointValue === null;

        $missingReorderQuantity =
            $reorderQuantityValue === null;

        /*
         * Use defaults only for calculations when values
         * have not been configured.
         */
        $reorderPoint = max(
            0,
            (int) (
                $reorderPointValue ?? 5
            )
        );

        $reorderQuantity = max(
            0,
            (int) (
                $reorderQuantityValue ?? 0
            )
        );

        $costPrice = max(
            0,
            (float) (
                $product->cost_price ?? 0
            )
        );

        $missingCost = $costPrice <= 0;

        $needsReorder =
            $stock <= $reorderPoint;

        /*
         * If no reorder quantity has been configured,
         * suggest enough units to reach the reorder point.
         */
        $suggestedQuantity = $needsReorder
            ? (
                $reorderQuantity > 0
                ? $reorderQuantity
                : max(
                    0,
                    $reorderPoint - $stock
                )
            )
            : 0;

        $estimatedCost =
            $suggestedQuantity * $costPrice;

        if ($stock <= 0) {
            $stockStatus = 'out';
            $stockStatusLabel = 'Out of Stock';
        } elseif ($stock <= $reorderPoint) {
            $stockStatus = 'low';
            $stockStatusLabel = 'Low Stock';
        } else {
            $stockStatus = 'healthy';
            $stockStatusLabel = 'Healthy';
        }

        /*
        |--------------------------------------------------------------------------
        | Urgency
        |--------------------------------------------------------------------------
        */

        if ($stock <= 0) {
            $urgency = 'critical';
            $urgencyLabel = 'Critical';
        } elseif (
            $reorderPoint > 0
            && $stock <= max(
                1,
                (int) floor(
                    $reorderPoint / 2
                )
            )
        ) {
            $urgency = 'high';
            $urgencyLabel = 'High';
        } elseif ($needsReorder) {
            $urgency = 'medium';
            $urgencyLabel = 'Medium';
        } else {
            $urgency = 'normal';
            $urgencyLabel = 'Normal';
        }

        $variantName = $isVariant
            ? $this->variantLabel(
                $variant->options ?? []
            )
            : null;

        $itemName = $product->title;

        if (
            $variantName !== null
            && $variantName !== ''
        ) {
            $itemName .= ' — ' . $variantName;
        }

        return [
            'product_id' =>
            (int) $product->id,

            'variant_id' =>
            $isVariant
                ? (int) $variant->id
                : null,

            'is_variant' =>
            $isVariant,

            'product' =>
            $product,

            'variant' =>
            $variant,

            'item_name' =>
            $itemName,

            'product_title' =>
            (string) $product->title,

            'variant_name' =>
            $variantName,

            'sku' =>
            $isVariant
                ? (
                    $variant->sku
                    ?: $product->sku
                )
                : $product->sku,

            'categories' =>
            $product->categories,

            'stock' =>
            $stock,

            'reorder_point' =>
            $reorderPoint,

            'reorder_quantity' =>
            $reorderQuantity,

            'suggested_quantity' =>
            $suggestedQuantity,

            'cost_price' =>
            round($costPrice, 2),

            'estimated_cost' =>
            round($estimatedCost, 2),

            'needs_reorder' =>
            $needsReorder,

            'stock_status' =>
            $stockStatus,

            'stock_status_label' =>
            $stockStatusLabel,

            'urgency' =>
            $urgency,

            'urgency_label' =>
            $urgencyLabel,

            'missing_reorder_point' =>
            $missingReorderPoint,

            'missing_reorder_quantity' =>
            $missingReorderQuantity,

            'missing_cost' =>
            $missingCost,

            'edit_url' =>
            route(
                'admin.products.edit',
                $product
            ),
        ];
    }

    /**
     * Create a readable variant name.
     */
    private function variantLabel(
        mixed $options
    ): ?string {
        if (!is_array($options)) {
            return null;
        }

        $labels = collect($options)
            ->map(function (mixed $option): ?string {
                if (!is_array($option)) {
                    return null;
                }

                $optionName = trim(
                    (string) (
                        $option['option_name']
                        ?? ''
                    )
                );

                $valueLabel = trim(
                    (string) (
                        $option['value_label']
                        ?? ''
                    )
                );

                if (
                    $optionName === ''
                    && $valueLabel === ''
                ) {
                    return null;
                }

                if ($optionName === '') {
                    return $valueLabel;
                }

                if ($valueLabel === '') {
                    return $optionName;
                }

                return $optionName
                    . ': '
                    . $valueLabel;
            })
            ->filter()
            ->values();

        return $labels->isEmpty()
            ? null
            : $labels->implode(' / ');
    }
}
