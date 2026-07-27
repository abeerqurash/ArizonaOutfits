<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductOptionController extends AdminController
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('product_options', 'name'),
            ],

            'type' => [
                'required',
                Rule::in([
                    'select',
                    'color',
                    'text',
                ]),
            ],
        ]);

        $option = ProductOption::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
        ]);

        return response()->json([
            'success' => true,

            'message' =>
                'Attribute created successfully.',

            'option' => [
                'id' => $option->id,
                'name' => $option->name,
                'type' => $option->type,
                'values' => [],
            ],
        ]);
    }

    public function storeValue(
        Request $request,
        ProductOption $productOption
    ): JsonResponse {
        $validated = $request->validate([
            'label' => [
                'required',
                'string',
                'max:100',
            ],

            'value' => [
                'nullable',
                'string',
                'max:100',
            ],

            'color_code' => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
        ]);

        $valueText = filled(
            $validated['value'] ?? null
        )
            ? trim((string) $validated['value'])
            : str((string) $validated['label'])
                ->slug()
                ->toString();

        $duplicateExists = $productOption
            ->values()
            ->where(function ($query) use (
                $validated,
                $valueText
            ) {
                $query
                    ->where(
                        'label',
                        $validated['label']
                    )
                    ->orWhere(
                        'value',
                        $valueText
                    );
            })
            ->exists();

        if ($duplicateExists) {
            return response()->json([
                'success' => false,

                'message' =>
                    'This value already exists for this attribute.',
            ], 422);
        }

        $value = ProductOptionValue::create([
            'product_option_id' =>
                $productOption->id,

            'label' =>
                $validated['label'],

            'value' =>
                $valueText,

            'color_code' =>
                $validated['color_code'] ?? null,
        ]);

        return response()->json([
            'success' => true,

            'message' =>
                'Attribute value created successfully.',

            'value' => [
                'id' => $value->id,
                'label' => $value->label,
                'value' => $value->value,
                'color_code' => $value->color_code,
            ],
        ]);
    }
}