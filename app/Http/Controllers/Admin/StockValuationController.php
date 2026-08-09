<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StockValuationExport;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockValuationController extends AdminController
{
    /**
     * Display the Stock Valuation dashboard.
     */
    public function index(Request $request): View
    {
        /*
    |--------------------------------------------------------------------------
    | Load all products for the interactive dashboard
    |--------------------------------------------------------------------------
    |
    | The table filtering is currently handled by JavaScript, so the initial
    | page still receives every product.
    |
    */

        $products = $this->getValuationProducts();

        $analytics = $this->buildAnalytics(
            $products
        );

        return view(
            'admin.stock-valuation.index',
            [
                'products' => $products,

                'totalCost' =>
                $analytics['totalCost'],

                'totalRetail' =>
                $analytics['totalRetail'],

                'totalProfit' =>
                $analytics['totalProfit'],

                'totalUnits' =>
                $analytics['totalUnits'],

                'healthyProducts' =>
                $analytics['healthyProducts'],

                'lowStockProducts' =>
                $analytics['lowStockProducts'],

                'outOfStockProducts' =>
                $analytics['outOfStockProducts'],

                'negativeMarginProducts' =>
                $analytics['negativeMarginProducts'],

                'missingCostProducts' =>
                $analytics['missingCostProducts'],

                'healthScore' =>
                $analytics['healthScore'],

                'highestInventoryValue' =>
                $analytics['highestInventoryValue'],

                'highestProfitProducts' =>
                $analytics['highestProfitProducts'],

                'highestInvestmentProducts' =>
                $analytics['highestInvestmentProducts'],

                'lowestMarginProducts' =>
                $analytics['lowestMarginProducts'],

                'inventoryValueLabels' =>
                $analytics['inventoryValueLabels'],

                'inventoryValueData' =>
                $analytics['inventoryValueData'],

                'profitLabels' =>
                $analytics['profitLabels'],

                'profitData' =>
                $analytics['profitData'],

                'stockDistribution' =>
                $analytics['stockDistribution'],

                'marginDistribution' =>
                $analytics['marginDistribution'],

                'categoryInventory' =>
                $analytics['categoryInventory'],
            ]
        );
    }

    /**
     * Download the Stock Valuation report as CSV.
     */
    public function exportCsv(
        Request $request
    ): StreamedResponse {
        $products = $this->getFilteredValuationProducts(
            $request
        );

        $analytics = $this->buildAnalytics(
            $products
        );

        $fileName =
            'stock-valuation-'
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
                $products,
                $analytics,
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
             * UTF-8 BOM helps Excel correctly display the
             * pound symbol and special characters.
             */
                fwrite(
                    $output,
                    "\xEF\xBB\xBF"
                );

                /*
            |--------------------------------------------------------------------------
            | Report heading
            |--------------------------------------------------------------------------
            */

                fputcsv(
                    $output,
                    [
                        'Arizona Outfits',
                        'Stock Valuation Report',
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
                        'Applied Search',
                        $request->string('search')->trim()->value()
                            ?: 'None',
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Stock Filter',
                        $this->stockFilterLabel(
                            $request->string('stock')->value()
                        ),
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Margin Filter',
                        $this->marginFilterLabel(
                            $request->string('margin')->value()
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
                        'Exported Products',
                        $products->count(),
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Total Stock Units',
                        $analytics['totalUnits'],
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Total Inventory Cost',
                        number_format(
                            $analytics['totalCost'],
                            2,
                            '.',
                            ''
                        ),
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Total Retail Value',
                        number_format(
                            $analytics['totalRetail'],
                            2,
                            '.',
                            ''
                        ),
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Expected Profit',
                        number_format(
                            $analytics['totalProfit'],
                            2,
                            '.',
                            ''
                        ),
                    ]
                );

                fputcsv(
                    $output,
                    [
                        'Inventory Health Score',
                        $analytics['healthScore'] . '%',
                    ]
                );

                fputcsv(
                    $output,
                    []
                );

                /*
            |--------------------------------------------------------------------------
            | Product headings
            |--------------------------------------------------------------------------
            */

                fputcsv(
                    $output,
                    [
                        'Product',
                        'SKU',
                        'Categories',
                        'Stock Status',
                        'Stock Units',
                        'Cost Price',
                        'Selling Price',
                        'Inventory Cost',
                        'Retail Value',
                        'Expected Profit',
                        'Margin Percentage',
                    ]
                );

                /*
            |--------------------------------------------------------------------------
            | Product rows
            |--------------------------------------------------------------------------
            */

                foreach ($products as $product) {
                    $stock = (int) (
                        $product->stock ?? 0
                    );

                    $costPrice = (float) (
                        $product->cost_price ?? 0
                    );

                    $sellingPrice = (float) (
                        $product->selling_price ?? 0
                    );

                    $inventoryCost = (float) (
                        $product->inventory_cost ?? 0
                    );

                    $inventoryRetail = (float) (
                        $product->inventory_retail ?? 0
                    );

                    $inventoryProfit = (float) (
                        $product->inventory_profit ?? 0
                    );

                    $margin = (float) (
                        $product->margin ?? 0
                    );

                    if ($stock <= 0) {
                        $stockStatus = 'Out of stock';
                    } elseif ($stock <= 5) {
                        $stockStatus = 'Low stock';
                    } else {
                        $stockStatus = 'In stock';
                    }

                    $categoryNames = $product
                        ->categories
                        ->pluck('title')
                        ->filter()
                        ->implode(', ');

                    fputcsv(
                        $output,
                        [
                            $product->title,

                            $product->sku
                                ?: 'Not assigned',

                            $categoryNames
                                ?: 'Uncategorised',

                            $stockStatus,

                            $stock,

                            number_format(
                                $costPrice,
                                2,
                                '.',
                                ''
                            ),

                            number_format(
                                $sellingPrice,
                                2,
                                '.',
                                ''
                            ),

                            number_format(
                                $inventoryCost,
                                2,
                                '.',
                                ''
                            ),

                            number_format(
                                $inventoryRetail,
                                2,
                                '.',
                                ''
                            ),

                            number_format(
                                $inventoryProfit,
                                2,
                                '.',
                                ''
                            ),

                            number_format(
                                $margin,
                                2,
                                '.',
                                ''
                            ) . '%',
                        ]
                    );
                }

                fclose($output);
            },
            $fileName,
            $headers
        );
    }

    /**
     * Load products and calculate their valuation fields.
     */
    private function getValuationProducts()
    {
        $products = Product::query()
            ->with('categories')
            ->orderBy('title')
            ->get();

        foreach ($products as $product) {
            $stock = max(
                0,
                (int) ($product->stock ?? 0)
            );

            $costPrice = max(
                0,
                (float) ($product->cost_price ?? 0)
            );

            $regularPrice = max(
                0,
                (float) ($product->regular_price ?? 0)
            );

            $salePrice = !is_null(
                $product->sale_price
            )
                ? (float) $product->sale_price
                : null;

            $sellingPrice =
                !is_null($salePrice)
                && $salePrice > 0
                && $salePrice < $regularPrice
                ? $salePrice
                : $regularPrice;

            $inventoryCost =
                $costPrice * $stock;

            $inventoryRetail =
                $sellingPrice * $stock;

            $inventoryProfit =
                ($sellingPrice - $costPrice)
                * $stock;

            $margin = $sellingPrice > 0
                ? (
                    (
                        $sellingPrice - $costPrice
                    ) / $sellingPrice
                ) * 100
                : 0;

            $product->inventory_cost =
                round($inventoryCost, 2);

            $product->inventory_retail =
                round($inventoryRetail, 2);

            $product->inventory_profit =
                round($inventoryProfit, 2);

            $product->selling_price =
                round($sellingPrice, 2);

            $product->margin =
                round($margin, 2);
        }

        return $products;
    }

    /**
     * Build totals, health calculations and chart data.
     */
    private function buildAnalytics(
        $products
    ): array {
        $totalCost = (float) $products->sum(
            'inventory_cost'
        );

        $totalRetail = (float) $products->sum(
            'inventory_retail'
        );

        $totalProfit = (float) $products->sum(
            'inventory_profit'
        );

        $totalUnits = (int) $products->sum(
            'stock'
        );

        $healthyProducts = 0;
        $lowStockProducts = 0;
        $outOfStockProducts = 0;
        $negativeMarginProducts = 0;
        $missingCostProducts = 0;

        foreach ($products as $product) {
            $stock = (int) (
                $product->stock ?? 0
            );

            $costPrice = (float) (
                $product->cost_price ?? 0
            );

            $sellingPrice = (float) (
                $product->selling_price ?? 0
            );

            if ($stock <= 0) {
                $outOfStockProducts++;
            } elseif ($stock <= 5) {
                $lowStockProducts++;
            } else {
                $healthyProducts++;
            }

            if ($costPrice <= 0) {
                $missingCostProducts++;
            }

            if ($sellingPrice < $costPrice) {
                $negativeMarginProducts++;
            }
        }

        $productCount = $products->count();

        $penalty =
            ($outOfStockProducts * 4)
            + ($negativeMarginProducts * 5)
            + ($missingCostProducts * 3)
            + ($lowStockProducts * 2);

        $maximumPenalty =
            max($productCount, 1) * 5;

        $healthScore = $productCount === 0
            ? 0
            : max(
                0,
                min(
                    100,
                    (int) round(
                        100 - (
                            (
                                $penalty
                                / $maximumPenalty
                            ) * 100
                        )
                    )
                )
            );

        $highestInventoryValue = $products
            ->sortByDesc('inventory_retail')
            ->take(5)
            ->values();

        $highestProfitProducts = $products
            ->sortByDesc('inventory_profit')
            ->take(5)
            ->values();

        $highestInvestmentProducts = $products
            ->sortByDesc('inventory_cost')
            ->take(5)
            ->values();

        $lowestMarginProducts = $products
            ->sortBy('margin')
            ->take(5)
            ->values();

        $inventoryValueChart = $products
            ->sortByDesc('inventory_retail')
            ->take(10)
            ->values();

        $inventoryValueLabels =
            $inventoryValueChart
            ->pluck('title')
            ->map(
                fn($title): string =>
                (string) $title
            )
            ->values();

        $inventoryValueData =
            $inventoryValueChart
            ->pluck('inventory_retail')
            ->map(
                fn($value): float =>
                round(
                    (float) $value,
                    2
                )
            )
            ->values();

        $profitChart = $products
            ->sortByDesc('inventory_profit')
            ->take(10)
            ->values();

        $profitLabels =
            $profitChart
            ->pluck('title')
            ->map(
                fn($title): string =>
                (string) $title
            )
            ->values();

        $profitData =
            $profitChart
            ->pluck('inventory_profit')
            ->map(
                fn($value): float =>
                round(
                    (float) $value,
                    2
                )
            )
            ->values();

        $stockDistribution = [
            'In Stock' => $healthyProducts,
            'Low Stock' => $lowStockProducts,
            'Out Of Stock' => $outOfStockProducts,
        ];

        $marginDistribution = [
            'Negative' => 0,
            '0-20%' => 0,
            '20-40%' => 0,
            '40%+' => 0,
        ];

        foreach ($products as $product) {
            $margin = (float) (
                $product->margin ?? 0
            );

            if ($margin < 0) {
                $marginDistribution['Negative']++;
            } elseif ($margin < 20) {
                $marginDistribution['0-20%']++;
            } elseif ($margin < 40) {
                $marginDistribution['20-40%']++;
            } else {
                $marginDistribution['40%+']++;
            }
        }

        $categoryInventory = [];

        foreach ($products as $product) {
            foreach (
                $product->categories
                as $productCategory
            ) {
                $categoryTitle = trim(
                    (string) (
                        $productCategory->title ?? ''
                    )
                );

                if ($categoryTitle === '') {
                    continue;
                }

                if (!array_key_exists(
                    $categoryTitle,
                    $categoryInventory
                )) {
                    $categoryInventory[$categoryTitle] = 0;
                }

                $categoryInventory[$categoryTitle] += (float) (
                    $product->inventory_retail ?? 0
                );
            }
        }

        $categoryInventory = collect(
            $categoryInventory
        )
            ->map(
                fn($value): float =>
                round(
                    (float) $value,
                    2
                )
            )
            ->sortDesc()
            ->take(10)
            ->all();

        return [
            'totalCost' =>
            round($totalCost, 2),

            'totalRetail' =>
            round($totalRetail, 2),

            'totalProfit' =>
            round($totalProfit, 2),

            'totalUnits' =>
            $totalUnits,

            'healthyProducts' =>
            $healthyProducts,

            'lowStockProducts' =>
            $lowStockProducts,

            'outOfStockProducts' =>
            $outOfStockProducts,

            'negativeMarginProducts' =>
            $negativeMarginProducts,

            'missingCostProducts' =>
            $missingCostProducts,

            'healthScore' =>
            $healthScore,

            'highestInventoryValue' =>
            $highestInventoryValue,

            'highestProfitProducts' =>
            $highestProfitProducts,

            'highestInvestmentProducts' =>
            $highestInvestmentProducts,

            'lowestMarginProducts' =>
            $lowestMarginProducts,

            'inventoryValueLabels' =>
            $inventoryValueLabels,

            'inventoryValueData' =>
            $inventoryValueData,

            'profitLabels' =>
            $profitLabels,

            'profitData' =>
            $profitData,

            'stockDistribution' =>
            $stockDistribution,

            'marginDistribution' =>
            $marginDistribution,

            'categoryInventory' =>
            $categoryInventory,
        ];
    }
    /**
     * Download the filtered Stock Valuation report as Excel.
     */
    public function exportExcel(
        Request $request
    ): BinaryFileResponse {
        $products = $this->getFilteredValuationProducts(
            $request
        );

        $fileName =
            'stock-valuation-'
            . now()->format('Y-m-d-His')
            . '.xlsx';

        return Excel::download(
            new StockValuationExport(
                $products
            ),
            $fileName
        );
    }

    /**
     * Download the filtered Stock Valuation report as PDF.
     */
    public function exportPdf(
        Request $request
    ): Response {
        $products = $this->getFilteredValuationProducts(
            $request
        );

        $analytics = $this->buildAnalytics(
            $products
        );

        $fileName =
            'stock-valuation-'
            . now()->format('Y-m-d-His')
            . '.pdf';

        $pdf = Pdf::loadView(
            'admin.stock-valuation.pdf',
            [
                'products' =>
                $products,

                'totalCost' =>
                $analytics['totalCost'],

                'totalRetail' =>
                $analytics['totalRetail'],

                'totalProfit' =>
                $analytics['totalProfit'],

                'totalUnits' =>
                $analytics['totalUnits'],

                'healthyProducts' =>
                $analytics['healthyProducts'],

                'lowStockProducts' =>
                $analytics['lowStockProducts'],

                'outOfStockProducts' =>
                $analytics['outOfStockProducts'],

                'negativeMarginProducts' =>
                $analytics['negativeMarginProducts'],

                'missingCostProducts' =>
                $analytics['missingCostProducts'],

                'healthScore' =>
                $analytics['healthScore'],

                'appliedSearch' =>
                $request->string('search')
                    ->trim()
                    ->value(),

                'appliedStockFilter' =>
                $this->stockFilterLabel(
                    $request->string('stock')->value()
                ),

                'appliedMarginFilter' =>
                $this->marginFilterLabel(
                    $request->string('margin')->value()
                ),

                'appliedSortFilter' =>
                $this->sortFilterLabel(
                    $request->string('sort')->value()
                ),
            ]
        );

        $pdf->setPaper(
            'a4',
            'landscape'
        );

        return $pdf->download(
            $fileName
        );
    }

    /**
     * Load products matching export filters and sorting.
     */
    private function getFilteredValuationProducts(
        Request $request
    ): Collection {
        $search = strtolower(
            trim(
                $request->string('search')->value()
            )
        );

        $stockFilter = trim(
            $request->string('stock')->value()
        );

        $marginFilter = trim(
            $request->string('margin')->value()
        );

        $sortFilter = trim(
            $request->string('sort')->value()
        );

        /*
     * First calculate all valuation fields so filtering and sorting
     * use exactly the same values as the dashboard.
     */
        $products = $this->getValuationProducts();

        $products = $products
            ->filter(
                function (
                    Product $product
                ) use (
                    $search,
                    $stockFilter,
                    $marginFilter
                ): bool {
                    $stock = (int) (
                        $product->stock ?? 0
                    );

                    $margin = (float) (
                        $product->margin ?? 0
                    );

                    /*
                |--------------------------------------------------------------------------
                | Search filter
                |--------------------------------------------------------------------------
                */

                    if ($search !== '') {
                        $searchableText = strtolower(
                            trim(
                                (string) (
                                    $product->title ?? ''
                                )
                                    . ' '
                                    . (string) (
                                        $product->sku ?? ''
                                    )
                            )
                        );

                        if (
                            !str_contains(
                                $searchableText,
                                $search
                            )
                        ) {
                            return false;
                        }
                    }

                    /*
                |--------------------------------------------------------------------------
                | Stock filter
                |--------------------------------------------------------------------------
                */

                    if (
                        $stockFilter === 'available'
                        && $stock <= 5
                    ) {
                        return false;
                    }

                    if (
                        $stockFilter === 'low'
                        && !(
                            $stock > 0
                            && $stock <= 5
                        )
                    ) {
                        return false;
                    }

                    if (
                        $stockFilter === 'out'
                        && $stock > 0
                    ) {
                        return false;
                    }

                    /*
                |--------------------------------------------------------------------------
                | Margin filter
                |--------------------------------------------------------------------------
                */

                    if (
                        $marginFilter === 'positive'
                        && $margin <= 0
                    ) {
                        return false;
                    }

                    if (
                        $marginFilter === 'negative'
                        && $margin >= 0
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

        $products = match ($sortFilter) {
            'profit' => $products->sortByDesc(
                'inventory_profit'
            ),

            'value' => $products->sortByDesc(
                'inventory_retail'
            ),

            'stock' => $products->sortByDesc(
                function (Product $product): int {
                    return (int) (
                        $product->stock ?? 0
                    );
                }
            ),

            default => $products->sortBy(
                function (Product $product): string {
                    return strtolower(
                        (string) (
                            $product->title ?? ''
                        )
                    );
                }
            ),
        };

        return $products->values();
    }

    /**
     * Convert the stock filter into readable report text.
     */
    private function stockFilterLabel(
        string $stockFilter
    ): string {
        return match ($stockFilter) {
            'available' => 'In Stock',
            'low' => 'Low Stock',
            'out' => 'Out of Stock',
            default => 'All Stock',
        };
    }

    /**
     * Convert the margin filter into readable report text.
     */
    private function marginFilterLabel(
        string $marginFilter
    ): string {
        return match ($marginFilter) {
            'positive' => 'Positive Margin',
            'negative' => 'Negative Margin',
            default => 'All Margins',
        };
    }
    /**
     * Convert the sort option into readable report text.
     */
    private function sortFilterLabel(
        string $sortFilter
    ): string {
        return match ($sortFilter) {
            'profit' => 'Highest Expected Profit',
            'value' => 'Highest Retail Value',
            'stock' => 'Highest Stock Quantity',
            default => 'Product Name A–Z',
        };
    }
}
