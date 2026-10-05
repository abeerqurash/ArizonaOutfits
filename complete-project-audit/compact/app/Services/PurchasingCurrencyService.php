<?php
namespace App\Services;
use Illuminate\Support\Collection;
class PurchasingCurrencyService
{
    public static function totals(Collection $orders): array
    {
        return $orders->where('status', '!=', 'cancelled')->groupBy(fn ($order) => strtoupper($order->currency ?: 'GBP'))
            ->map(fn ($group) => round((float) $group->sum('total_amount'), 2))->sortKeys()->all();
    }
    public static function format(array $totals): string
    {
        return collect($totals)->map(fn ($total, $currency) => $currency . ' ' . number_format($total, 2))->implode(' · ') ?: 'No purchases';
    }
}