<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repair existing product variant options.
     */
    public function up(): void
    {
        if (
            !Schema::hasTable('product_variants')
            || !Schema::hasTable('product_options')
            || !Schema::hasTable('product_option_values')
        ) {
            return;
        }

        $optionValueLabelColumn =
            $this->findFirstExistingColumn(
                'product_option_values',
                [
                    'value',
                    'label',
                    'name',
                    'title',
                ]
            );

        if (!$optionValueLabelColumn) {
            throw new RuntimeException(
                'No value, label, name or title column exists on product_option_values.'
            );
        }

        DB::table('product_variants')
            ->select([
                'id',
                'options',
            ])
            ->orderBy('id')
            ->chunkById(
                100,
                function ($variants) use (
                    $optionValueLabelColumn
                ) {
                    foreach ($variants as $variant) {
                        $decodedOptions =
                            $this->decodeOptions(
                                $variant->options
                            );

                        if (empty($decodedOptions)) {
                            DB::table(
                                'product_variants'
                            )
                                ->where(
                                    'id',
                                    $variant->id
                                )
                                ->update([
                                    'options' =>
                                        json_encode(
                                            [],
                                            JSON_UNESCAPED_UNICODE
                                            | JSON_UNESCAPED_SLASHES
                                        ),
                                ]);

                            continue;
                        }

                        $normalizedOptions =
                            $this->normalizeOptions(
                                $decodedOptions,
                                $optionValueLabelColumn
                            );

                        DB::table(
                            'product_variants'
                        )
                            ->where(
                                'id',
                                $variant->id
                            )
                            ->update([
                                'options' =>
                                    json_encode(
                                        $normalizedOptions,
                                        JSON_UNESCAPED_UNICODE
                                        | JSON_UNESCAPED_SLASHES
                                    ),
                            ]);
                    }
                },
                'id'
            );
    }

    /**
     * This data migration should not restore invalid old JSON.
     */
    public function down(): void
    {
        //
    }

    /**
     * Normalize old and new option structures.
     */
    private function normalizeOptions(
        array $options,
        string $optionValueLabelColumn
    ): array {
        /*
         * Already stored in the correct list structure:
         *
         * [
         *     [
         *         "option_id" => 1,
         *         "option_name" => "Color",
         *         "value_id" => 2,
         *         "value_label" => "Black"
         *     ]
         * ]
         */
        if (array_is_list($options)) {
            return $this->normalizeOptionList(
                $options,
                $optionValueLabelColumn
            );
        }

        /*
         * Old structure:
         *
         * [
         *     "Color" => "Black",
         *     "Size" => "S"
         * ]
         */
        $normalized = [];

        foreach (
            $options
            as $optionName => $valueLabel
        ) {
            if (
                !is_scalar($optionName)
                || !is_scalar($valueLabel)
            ) {
                continue;
            }

            $optionName = trim(
                (string) $optionName
            );

            $valueLabel = trim(
                (string) $valueLabel
            );

            if (
                $optionName === ''
                || $valueLabel === ''
            ) {
                continue;
            }

            $option = DB::table(
                'product_options'
            )
                ->whereRaw(
                    'LOWER(name) = ?',
                    [
                        mb_strtolower(
                            $optionName
                        ),
                    ]
                )
                ->first();

            if (!$option) {
                /*
                 * Keep the label information even when the old
                 * option cannot be matched to an existing record.
                 */
                $normalized[] = [
                    'option_id' => null,
                    'option_name' => $optionName,
                    'value_id' => null,
                    'value_label' => $valueLabel,
                ];

                continue;
            }

            $optionValue = DB::table(
                'product_option_values'
            )
                ->where(
                    'product_option_id',
                    $option->id
                )
                ->whereRaw(
                    'LOWER('
                    . $optionValueLabelColumn
                    . ') = ?',
                    [
                        mb_strtolower(
                            $valueLabel
                        ),
                    ]
                )
                ->first();

            $normalized[] = [
                'option_id' =>
                    (int) $option->id,

                'option_name' =>
                    (string) $option->name,

                'value_id' =>
                    $optionValue
                        ? (int) $optionValue->id
                        : null,

                'value_label' =>
                    $optionValue
                        ? (string) $optionValue
                            ->{$optionValueLabelColumn}
                        : $valueLabel,
            ];
        }

        return $normalized;
    }

    /**
     * Normalize variants that already use a list of option objects.
     */
    private function normalizeOptionList(
        array $options,
        string $optionValueLabelColumn
    ): array {
        $normalized = [];

        foreach ($options as $optionData) {
            if (!is_array($optionData)) {
                continue;
            }

            $optionId = isset(
                $optionData['option_id']
            )
                && is_numeric(
                    $optionData['option_id']
                )
                    ? (int) $optionData[
                        'option_id'
                    ]
                    : null;

            $valueId = isset(
                $optionData['value_id']
            )
                && is_numeric(
                    $optionData['value_id']
                )
                    ? (int) $optionData[
                        'value_id'
                    ]
                    : null;

            $optionName = trim(
                (string) (
                    $optionData[
                        'option_name'
                    ]
                    ?? $optionData['name']
                    ?? ''
                )
            );

            $valueLabel = trim(
                (string) (
                    $optionData[
                        'value_label'
                    ]
                    ?? $optionData['label']
                    ?? $optionData['value']
                    ?? ''
                )
            );

            if (
                !$optionId
                && $optionName !== ''
            ) {
                $option = DB::table(
                    'product_options'
                )
                    ->whereRaw(
                        'LOWER(name) = ?',
                        [
                            mb_strtolower(
                                $optionName
                            ),
                        ]
                    )
                    ->first();

                if ($option) {
                    $optionId =
                        (int) $option->id;

                    $optionName =
                        (string) $option->name;
                }
            }

            if (
                !$valueId
                && $optionId
                && $valueLabel !== ''
            ) {
                $optionValue = DB::table(
                    'product_option_values'
                )
                    ->where(
                        'product_option_id',
                        $optionId
                    )
                    ->whereRaw(
                        'LOWER('
                        . $optionValueLabelColumn
                        . ') = ?',
                        [
                            mb_strtolower(
                                $valueLabel
                            ),
                        ]
                    )
                    ->first();

                if ($optionValue) {
                    $valueId =
                        (int) $optionValue->id;

                    $valueLabel =
                        (string) $optionValue
                            ->{$optionValueLabelColumn};
                }
            }

            $normalized[] = [
                'option_id' => $optionId,
                'option_name' => $optionName,
                'value_id' => $valueId,
                'value_label' => $valueLabel,
            ];
        }

        return $normalized;
    }

    /**
     * Decode normal or double-encoded JSON.
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

    /**
     * Find the column used for option value labels.
     */
    private function findFirstExistingColumn(
        string $table,
        array $columns
    ): ?string {
        foreach ($columns as $column) {
            if (
                Schema::hasColumn(
                    $table,
                    $column
                )
            ) {
                return $column;
            }
        }

        return null;
    }
};