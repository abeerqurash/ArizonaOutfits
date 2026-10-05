<?php
namespace App\Services;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
class CatalogOrderService
{
    public function apply(Builder $query): Builder
    {
        $generation = DB::table('catalog_order_state')->where('id', 1)->value('generation');
        $ids = Product::query()->pluck('id')->all();
        usort($ids, fn ($a, $b) => strcmp(hash('sha256', $generation.':'.$a), hash('sha256', $generation.':'.$b)) ?: $a <=> $b);
        if (!$ids) return $query->orderBy('products.id');
        $cases = [];
        foreach ($ids as $position => $id) $cases[] = 'WHEN '.(int)$id.' THEN '.(int)$position;
        return $query->orderByRaw('CASE products.id '.implode(' ', $cases).' ELSE '.count($ids).' END')->orderBy('products.id');
    }
}
