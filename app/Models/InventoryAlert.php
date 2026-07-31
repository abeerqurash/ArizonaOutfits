<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAlert extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'alert_type',
        'stock_level',
        'threshold',
        'status',
        'notified_at',
        'resolved_at',
    ];

    protected $casts = [
        'stock_level' => 'integer',
        'threshold' => 'integer',
        'notified_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Product relationship
    |--------------------------------------------------------------------------
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Product variant relationship
    |--------------------------------------------------------------------------
    */

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Alert state helpers
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isLowStock(): bool
    {
        return $this->alert_type === self::TYPE_LOW_STOCK;
    }

    public function isOutOfStock(): bool
    {
        return $this->alert_type === self::TYPE_OUT_OF_STOCK;
    }

    /*
    |--------------------------------------------------------------------------
    | Readable alert title
    |--------------------------------------------------------------------------
    */

    public const TYPE_LOW_STOCK = 'low_stock';
    public const TYPE_OUT_OF_STOCK = 'out_of_stock';

    public function getAlertTitleAttribute(): string
    {
        return match ($this->alert_type) {
            self::TYPE_OUT_OF_STOCK => 'Product out of stock',
            self::TYPE_LOW_STOCK => 'Low stock warning',
            default => 'Inventory alert',
        };
    }
    /*
    |--------------------------------------------------------------------------
    | Readable item name
    |--------------------------------------------------------------------------
    */

    public function getItemNameAttribute(): string
    {
        $productTitle = $this->product?->title
            ?? $this->variant?->product?->title
            ?? 'Unknown product';

        if (!$this->variant) {
            return $productTitle;
        }

        $variantDescription =
            $this->variantDescription();

        if ($variantDescription === '') {
            return $productTitle;
        }

        return $productTitle
            . ' — '
            . $variantDescription;
    }

    /*
    |--------------------------------------------------------------------------
    | Variant description
    |--------------------------------------------------------------------------
    */

    private function variantDescription(): string
    {
        if (!$this->variant) {
            return '';
        }

        $options = $this->variant?->options ?? [];

        if (!is_array($options) || empty($options)) {
            return $this->variant->sku
                ?: 'Variant #'
                . $this->variant->id;
        }

        return collect($options)
            ->map(
                function (
                    mixed $value,
                    mixed $key
                ): string {
                    if (is_string($key)) {
                        return $key
                            . ': '
                            . $value;
                    }

                    return (string) $value;
                }
            )
            ->implode(', ');
    }
}
