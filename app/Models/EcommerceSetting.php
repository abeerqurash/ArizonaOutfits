<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcommerceSetting extends Model
{
    protected $fillable = [
        'store_name',
        'store_email',
        'store_phone',
        'store_address',
        'currency',
        'currency_symbol',
        'tax_percentage',
        'shipping_fee',
        'free_shipping_threshold',
        'order_prefix',
        'guest_checkout_enabled',
        'cash_on_delivery_enabled',
        'stock_management_enabled',
        'maintenance_mode',
        'low_stock_threshold',
        'checkout_notice',
        'order_email_message',
    ];

    protected $casts = [
        'tax_percentage' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'guest_checkout_enabled' => 'boolean',
        'cash_on_delivery_enabled' => 'boolean',
        'stock_management_enabled' => 'boolean',
        'maintenance_mode' => 'boolean',
        'low_stock_threshold' => 'integer',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'store_name' => config('app.name', 'IdeoStream'),
                'currency' => 'USD',
                'currency_symbol' => '$',
                'order_prefix' => 'ORD',
            ]
        );
    }
}