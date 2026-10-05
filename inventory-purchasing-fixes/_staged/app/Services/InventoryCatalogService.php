<?php
namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class InventoryCatalogService
{
    public static function threshold(Product|ProductVariant $item): int
    {
        return max(0, (int) ($item->reorder_point ?? config('inventory.low_stock_threshold', 5)));
    }

    /** Read-only projections: never save these copies of catalog products. */
    public function rows(): Collection
    {
        return Product::with(['categories', 'variants'])->orderBy('title')->get()->flatMap(function (Product $product) {
            $entities = $product->variants->isEmpty() ? collect([$product]) : $product->variants;
            return $entities->map(function ($entity) use ($product) {
                $row = clone $product;
                $variant = $entity instanceof ProductVariant;
                $label = $variant ? collect($entity->options ?? [])->map(fn ($value, $key) => is_scalar($value) ? $key . ': ' . $value : json_encode($value))->implode(', ') : '';
                $row->title = $product->title . ($label !== '' ? ' — ' . $label : '');
                $row->sku = $entity->sku ?: $product->sku;
                $row->stock = max(0, (int) $entity->stock);
                $row->inventory_variant_id = $variant ? $entity->id : null;
                $row->reorder_point = self::threshold($entity);
                $row->regular_price = $entity->regular_price !== null ? $entity->regular_price : $product->regular_price;
                $row->sale_price = $entity->sale_price;
                $row->cost_price = max(0, (float) $product->cost_price);
                $regular = max(0, (float) $row->regular_price);
                $sale = $row->sale_price !== null ? (float) $row->sale_price : null;
                $row->selling_price = $sale !== null && $sale >= 0 && $sale < $regular ? $sale : $regular;
                $row->inventory_cost = round($row->stock * $row->cost_price, 2);
                $row->inventory_retail = round($row->stock * $row->selling_price, 2);
                $row->inventory_profit = round($row->inventory_retail - $row->inventory_cost, 2);
                $row->margin = $row->selling_price > 0 ? round(($row->selling_price - $row->cost_price) / $row->selling_price * 100, 2) : ($row->cost_price > 0 ? -100 : 0);
                return $row;
            });
        })->values();
    }
}
