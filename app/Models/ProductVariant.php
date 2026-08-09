<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'regular_price',
        'sale_price',
        'stock',
        'reorder_point',
        'reorder_quantity',
        'image',
        'options',
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
        'reorder_point' => 'integer',
        'reorder_quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplierProducts(): HasMany
    {
        return $this->hasMany(
            SupplierProduct::class,
            'product_variant_id'
        );
    }

    protected function options(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): array => $this->decodeOptions($value),
            set: function (mixed $value): string {
                return json_encode(
                    $this->decodeOptions($value),
                    JSON_UNESCAPED_UNICODE
                        | JSON_UNESCAPED_SLASHES
                        | JSON_THROW_ON_ERROR
                );
            }
        );
    }

    private function decodeOptions(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return [];
        }

        for ($attempt = 0; $attempt < 5; $attempt++) {
            if (!is_string($value)) {
                break;
            }

            $decoded = json_decode($value, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [];
            }

            $value = $decoded;

            if (is_array($value)) {
                return $value;
            }
        }

        return is_array($value) ? $value : [];
    }

    public function inventoryHistories(): HasMany
    {
        return $this->hasMany(
            InventoryHistory::class,
            'product_variant_id'
        );
    }
}
