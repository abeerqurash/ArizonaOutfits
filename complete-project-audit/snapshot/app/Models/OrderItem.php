<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',

        'product_title',
        'sku',

        'price',
        'quantity',

        'options',

        'total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariant::class,
            'variant_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Raw options
    |--------------------------------------------------------------------------
    */

    protected function options(): Attribute
    {
        return Attribute::make(
            get: function ($value): array {
                if ($value === null || $value === '') {
                    return [];
                }

                if (is_array($value)) {
                    return $value;
                }

                $decoded = json_decode(
                    $value,
                    true
                );

                /*
                 * Handle options that were JSON-encoded twice.
                 */
                if (is_string($decoded)) {
                    $decodedAgain = json_decode(
                        $decoded,
                        true
                    );

                    if (
                        json_last_error()
                        === JSON_ERROR_NONE
                    ) {
                        $decoded = $decodedAgain;
                    }
                }

                return is_array($decoded)
                    ? $decoded
                    : [];
            },

            set: function ($value): string {
                if ($value === null || $value === '') {
                    return json_encode(
                        [],
                        JSON_UNESCAPED_UNICODE
                    );
                }

                if (is_string($value)) {
                    $decoded = json_decode(
                        $value,
                        true
                    );

                    if (
                        json_last_error()
                        === JSON_ERROR_NONE
                    ) {
                        $value = $decoded;
                    } else {
                        $value = [$value];
                    }
                }

                return json_encode(
                    is_array($value)
                        ? $value
                        : [],
                    JSON_UNESCAPED_UNICODE
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Display-ready product options
    |--------------------------------------------------------------------------
    |
    | Converts:
    |
    | [1, "color", 1, "Red"]
    |
    | Into:
    |
    | [
    |     [
    |         "name" => "Color",
    |         "value" => "Red",
    |     ]
    | ]
    |
    */

    protected function displayOptions(): Attribute
    {
        return Attribute::make(
            get: function (): array {
                $options = $this->options;

                if (empty($options)) {
                    return [];
                }

                $displayOptions = [];

                foreach ($options as $key => $option) {
                    $normalizedOption =
                        $this->normalizeDisplayOption(
                            $key,
                            $option
                        );

                    if ($normalizedOption !== null) {
                        $displayOptions[] =
                            $normalizedOption;
                    }
                }

                return $displayOptions;
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Compatibility accessors
    |--------------------------------------------------------------------------
    */

    protected function productName(): Attribute
    {
        return Attribute::make(
            get: fn (): string =>
                $this->product_title
                ?: $this->product?->title
                ?: 'Product'
        );
    }

    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                $storedTotal = (float) (
                    $this->getRawOriginal('total')
                    ?? 0
                );

                if ($storedTotal > 0) {
                    return $storedTotal;
                }

                return round(
                    (float) $this->price
                    * (int) $this->quantity,
                    2
                );
            }
        );
    }

    protected function variantLabel(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $displayOptions =
                    $this->display_options;

                if (empty($displayOptions)) {
                    return null;
                }

                $labels = [];

                foreach (
                    $displayOptions as $option
                ) {
                    $labels[] = sprintf(
                        '%s: %s',
                        $option['name'],
                        $option['value']
                    );
                }

                return implode(
                    ', ',
                    $labels
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Normalize one option
    |--------------------------------------------------------------------------
    */

    private function normalizeDisplayOption(
        mixed $key,
        mixed $option
    ): ?array {
        /*
         * Format:
         *
         * [option_id, option_name, value_id, value_label]
         *
         * Example:
         *
         * [1, "color", 1, "Red"]
         */
        if (
            is_array($option)
            && array_is_list($option)
        ) {
            $optionName =
                $option[1]
                ?? null;

            $optionValue =
                $option[3]
                ?? $option[2]
                ?? null;

            if (
                filled($optionName)
                && filled($optionValue)
            ) {
                return [
                    'name' => $this->formatOptionName(
                        $optionName
                    ),

                    'value' => $this->formatOptionValue(
                        $optionValue
                    ),
                ];
            }

            /*
             * A shorter array such as:
             *
             * ["Color", "Red"]
             */
            if (
                isset($option[0], $option[1])
                && !is_numeric($option[0])
            ) {
                return [
                    'name' => $this->formatOptionName(
                        $option[0]
                    ),

                    'value' => $this->formatOptionValue(
                        $option[1]
                    ),
                ];
            }
        }

        /*
         * Associative option formats.
         */
        if (is_array($option)) {
            $optionName =
                $option['option_name']
                ?? $option['option']
                ?? $option['name']
                ?? $option['attribute_name']
                ?? $option['attribute']
                ?? (
                    is_string($key)
                    && !is_numeric($key)
                        ? $key
                        : null
                );

            $optionValue =
                $option['value_label']
                ?? $option['option_value']
                ?? $option['value_name']
                ?? $option['label']
                ?? $option['value']
                ?? $option['attribute_value']
                ?? null;

            if (
                filled($optionName)
                && filled($optionValue)
            ) {
                return [
                    'name' => $this->formatOptionName(
                        $optionName
                    ),

                    'value' => $this->formatOptionValue(
                        $optionValue
                    ),
                ];
            }

            return null;
        }

        /*
         * Format:
         *
         * "Color" => "Red"
         */
        if (
            is_string($key)
            && !is_numeric($key)
            && filled($option)
        ) {
            return [
                'name' => $this->formatOptionName(
                    $key
                ),

                'value' => $this->formatOptionValue(
                    $option
                ),
            ];
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Format option name
    |--------------------------------------------------------------------------
    */

    private function formatOptionName(
        mixed $name
    ): string {
        return ucwords(
            str_replace(
                ['_', '-'],
                ' ',
                trim((string) $name)
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Format option value
    |--------------------------------------------------------------------------
    */

    private function formatOptionValue(
        mixed $value
    ): string {
        if (is_array($value)) {
            return implode(
                ', ',
                array_map(
                    fn ($item): string =>
                        trim((string) $item),
                    $value
                )
            );
        }

        return trim((string) $value);
    }
}