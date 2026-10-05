<?php
namespace App\Services;
use Illuminate\Support\Collection;

class CustomerSpendService
{
    public static function totals(Collection $orders): array
    {
        return $orders->where('payment_status', 'paid')->whereNotIn('order_status', ['cancelled', 'refunded'])
            ->groupBy(fn ($order) => strtoupper($order->currency ?: config('shipping.currency', 'USD')))
            ->map(fn ($group) => round((float) $group->sum('total'), 2))->sortKeys()->all();
    }
    public static function format(array $totals): string
    {
        return collect($totals)->map(fn ($value, $currency) => $currency . ' ' . number_format($value, 2))->implode(' · ') ?: 'No paid orders';
    }
}
