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
        'image',
        'options',
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
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
    | Variant options
    |--------------------------------------------------------------------------
    |
    | This accessor and mutator prevent options from being JSON encoded
    | more than once.
    |
    | It also safely reads older variant records that were stored as
    | double-encoded JSON strings.
    |
    */

    protected function options(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): array {
                return $this->decodeOptions(
                    $value
                );
            },

            set: function (mixed $value): string {
                $options = $this->decodeOptions(
                    $value
                );

                return json_encode(
                    $options,
                    JSON_UNESCAPED_UNICODE
                        | JSON_UNESCAPED_SLASHES
                        | JSON_THROW_ON_ERROR
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Decode options safely
    |--------------------------------------------------------------------------
    */

    private function decodeOptions(
        mixed $value
    ): array {
        if (is_array($value)) {
            return $value;
        }

        if (
            $value === null
            || $value === ''
        ) {
            return [];
        }

        /*
         * Older records may be encoded more than once:
         *
         * "\"{\\\"Color\\\":\\\"Black\\\"}\""
         *
         * Decode repeatedly until an array is obtained.
         */
        for ($attempt = 0; $attempt < 5; $attempt++) {
            if (!is_string($value)) {
                break;
            }

            $decoded = json_decode(
                $value,
                true
            );

            if (
                json_last_error()
                !== JSON_ERROR_NONE
            ) {
                return [];
            }

            $value = $decoded;

            if (is_array($value)) {
                return $value;
            }
        }

        return is_array($value)
            ? $value
            : [];
    }

    public function inventoryHistories(): HasMany
    {
        return $this->hasMany(
            InventoryHistory::class,
            'product_variant_id'
        );
    }
}
