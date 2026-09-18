<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAlert extends Model
{
    public const TYPE_LOW_STOCK = 'low_stock';
    public const TYPE_OUT_OF_STOCK = 'out_of_stock';

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
        $productTitle = $this->stringValue(
            $this->product?->title
                ?? $this->variant?->product?->title
                ?? 'Unknown product'
        );

        if (!$this->variant) {
            return $productTitle;
        }

        $variantDescription = $this->variantDescription();

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
    |
    | ProductVariant::options may contain simple scalar values or structured
    | arrays such as:
    |
    | [
    |     [
    |         'option_name' => 'Color',
    |         'value_label' => 'Black',
    |     ],
    | ]
    |
    | Never concatenate an array directly into a string.
    |--------------------------------------------------------------------------
    */

    private function variantDescription(): string
    {
        if (!$this->variant) {
            return '';
        }

        $options = $this->variant->options ?? [];

        if (is_string($options)) {
            $decoded = json_decode(
                $options,
                true
            );

            if (
                json_last_error() === JSON_ERROR_NONE
                && is_array($decoded)
            ) {
                $options = $decoded;
            }
        }

        if (!is_array($options) || empty($options)) {
            return $this->variantFallbackName();
        }

        $descriptions = [];

        foreach ($options as $key => $value) {
            $description = $this->optionDescription(
                $key,
                $value
            );

            if ($description !== '') {
                $descriptions[] = $description;
            }
        }

        if (empty($descriptions)) {
            return $this->variantFallbackName();
        }

        return implode(', ', $descriptions);
    }

    /*
    |--------------------------------------------------------------------------
    | Convert one variant option to readable text
    |--------------------------------------------------------------------------
    */

    private function optionDescription(
        mixed $key,
        mixed $value
    ): string {
        if (is_array($value)) {
            $optionName = $this->firstStringValue(
                $value,
                [
                    'option_name',
                    'name',
                    'option',
                    'attribute_name',
                    'attribute',
                ]
            );

            $optionValue = $this->firstStringValue(
                $value,
                [
                    'value_label',
                    'label',
                    'value',
                    'option_value',
                    'attribute_value',
                ]
            );

            if (
                $optionName !== ''
                && $optionValue !== ''
            ) {
                return $optionName
                    . ': '
                    . $optionValue;
            }

            if ($optionValue !== '') {
                return $optionValue;
            }

            if ($optionName !== '') {
                return $optionName;
            }

            /*
             * Last safe fallback for an unknown array structure.
             * Keep scalar values only and never cast nested arrays directly.
             */
            $parts = [];

            foreach ($value as $nestedValue) {
                $text = $this->stringValue(
                    $nestedValue
                );

                if ($text !== '') {
                    $parts[] = $text;
                }
            }

            return implode(
                ': ',
                array_values(
                    array_unique($parts)
                )
            );
        }

        $text = $this->stringValue(
            $value
        );

        if ($text === '') {
            return '';
        }

        if (
            is_string($key)
            && $key !== ''
            && !is_numeric($key)
        ) {
            return $key
                . ': '
                . $text;
        }

        return $text;
    }

    /*
    |--------------------------------------------------------------------------
    | Find first readable value in a structured option
    |--------------------------------------------------------------------------
    */

    private function firstStringValue(
        array $data,
        array $keys
    ): string {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $value = $this->stringValue(
                $data[$key]
            );

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /*
    |--------------------------------------------------------------------------
    | Safely convert a value to text
    |--------------------------------------------------------------------------
    */

    private function stringValue(
        mixed $value
    ): string {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return trim($value);
        }

        if (
            is_int($value)
            || is_float($value)
        ) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value
                ? 'Yes'
                : 'No';
        }

        /*
         * Arrays/objects are deliberately not cast to string.
         * That prevents PHP's "Array to string conversion" warning.
         */
        return '';
    }

    /*
    |--------------------------------------------------------------------------
    | Variant fallback
    |--------------------------------------------------------------------------
    */

    private function variantFallbackName(): string
    {
        $sku = $this->stringValue(
            $this->variant?->sku
        );

        if ($sku !== '') {
            return $sku;
        }

        return 'Variant #'
            . (string) $this->variant->id;
    }
}
