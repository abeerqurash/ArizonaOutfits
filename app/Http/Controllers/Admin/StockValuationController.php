<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\View\View;

class StockValuationController extends AdminController
{
    public function index(): View
    {
        /*
        |--------------------------------------------------------------------------
        | Load products and their ecommerce categories
        |--------------------------------------------------------------------------
        |
        | Product categories use the ProductCategory model through the
        | Product::categories() relationship.
        |
        | App\Models\Category belongs to the blog system and must not be used
        | for ecommerce product category calculations.
        |
        */

        $products = Product::query()
            ->with('categories')
            ->orderBy('title')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Main inventory totals
        |--------------------------------------------------------------------------
        */

        $totalCost = 0;
        $totalRetail = 0;
        $totalProfit = 0;
        $totalUnits = 0;

        foreach ($products as $product) {
            $stock = (int) ($product->stock ?? 0);

            $costPrice = (float) ($product->cost_price ?? 0);

            $regularPrice = (float) ($product->regular_price ?? 0);

            $salePrice = !is_null($product->sale_price)
                ? (float) $product->sale_price
                : null;

            $sellingPrice = !is_null($salePrice)
                && $salePrice > 0
                ? $salePrice
                : $regularPrice;

            $inventoryCost = $costPrice * $stock;

            $inventoryRetail = $sellingPrice * $stock;

            $inventoryProfit = (
                $sellingPrice - $costPrice
            ) * $stock;

            $unitProfit = $sellingPrice - $costPrice;

            $margin = $sellingPrice > 0
                ? ($unitProfit / $sellingPrice) * 100
                : 0;

            /*
             * Add calculated values to each Product model instance.
             * These values are not database columns.
             */
            $product->inventory_cost = $inventoryCost;
            $product->inventory_retail = $inventoryRetail;
            $product->inventory_profit = $inventoryProfit;
            $product->margin = $margin;
            $product->selling_price = $sellingPrice;

            $totalCost += $inventoryCost;
            $totalRetail += $inventoryRetail;
            $totalProfit += $inventoryProfit;
            $totalUnits += $stock;
        }

        /*
        |--------------------------------------------------------------------------
        | Inventory health counters
        |--------------------------------------------------------------------------
        */

        $healthyProducts = 0;
        $lowStockProducts = 0;
        $outOfStockProducts = 0;
        $negativeMarginProducts = 0;
        $missingCostProducts = 0;

        foreach ($products as $product) {
            $stock = (int) ($product->stock ?? 0);

            $costPrice = (float) ($product->cost_price ?? 0);

            $sellingPrice = (float) ($product->selling_price ?? 0);

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

        /*
        |--------------------------------------------------------------------------
        | Inventory health score
        |--------------------------------------------------------------------------
        */

        $actualProductCount = $products->count();

        $penalty =
            ($outOfStockProducts * 4)
            + ($negativeMarginProducts * 5)
            + ($missingCostProducts * 3)
            + ($lowStockProducts * 2);

        $maxPenalty = max($actualProductCount, 1) * 5;

        $healthScore = $actualProductCount === 0
            ? 0
            : max(
                0,
                min(
                    100,
                    (int) round(
                        100 - (
                            ($penalty / $maxPenalty) * 100
                        )
                    )
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Analytics cards
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Inventory Value chart
        |--------------------------------------------------------------------------
        */

        $inventoryValueChart = $products
            ->sortByDesc('inventory_retail')
            ->take(10)
            ->values();

        $inventoryValueLabels = $inventoryValueChart
            ->pluck('title')
            ->map(function ($title) {
                return (string) $title;
            })
            ->values();

        $inventoryValueData = $inventoryValueChart
            ->pluck('inventory_retail')
            ->map(function ($value) {
                return round((float) $value, 2);
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Expected Profit chart
        |--------------------------------------------------------------------------
        */

        $profitChart = $products
            ->sortByDesc('inventory_profit')
            ->take(10)
            ->values();

        $profitLabels = $profitChart
            ->pluck('title')
            ->map(function ($title) {
                return (string) $title;
            })
            ->values();

        $profitData = $profitChart
            ->pluck('inventory_profit')
            ->map(function ($value) {
                return round((float) $value, 2);
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Stock distribution chart
        |--------------------------------------------------------------------------
        */

        $stockDistribution = [
            'In Stock' => $healthyProducts,
            'Low Stock' => $lowStockProducts,
            'Out Of Stock' => $outOfStockProducts,
        ];

        /*
        |--------------------------------------------------------------------------
        | Margin distribution chart
        |--------------------------------------------------------------------------
        */

        $marginDistribution = [
            'Negative' => 0,
            '0-20%' => 0,
            '20-40%' => 0,
            '40%+' => 0,
        ];

        foreach ($products as $product) {
            $margin = (float) ($product->margin ?? 0);

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

        /*
        |--------------------------------------------------------------------------
        | Inventory value by ecommerce category
        |--------------------------------------------------------------------------
        |
        | Each Product already has its ProductCategory models loaded through:
        |
        | $product->categories
        |
        | We add each product's inventory retail value to every category attached
        | to that product.
        |
        */

        $categoryInventory = [];

        foreach ($products as $product) {
            foreach ($product->categories as $productCategory) {
                $categoryTitle = trim(
                    (string) ($productCategory->title ?? '')
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

                $categoryInventory[$categoryTitle] +=
                    (float) $product->inventory_retail;
            }
        }

        /*
         * Sort highest category value first and keep the top 10.
         */
        $categoryInventory = collect($categoryInventory)
            ->map(function ($value) {
                return round((float) $value, 2);
            })
            ->sortDesc()
            ->take(10)
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Return Stock Valuation page
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.stock-valuation.index',
            compact(
                'products',
                'totalCost',
                'totalRetail',
                'totalProfit',
                'totalUnits',

                'healthyProducts',
                'lowStockProducts',
                'outOfStockProducts',
                'negativeMarginProducts',
                'missingCostProducts',

                'healthScore',

                'highestInventoryValue',
                'highestProfitProducts',
                'highestInvestmentProducts',
                'lowestMarginProducts',

                'inventoryValueLabels',
                'inventoryValueData',

                'profitLabels',
                'profitData',

                'stockDistribution',
                'marginDistribution',
                'categoryInventory'
            )
        );
    }
}