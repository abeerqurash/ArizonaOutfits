<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryAdjustmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [

            'variant_id' => [
                'nullable',
                'integer',
                'exists:product_variants,id',
            ],

            'adjustment_type' => [
                'required',

                Rule::in([
                    'add',
                    'remove',
                    'set',
                ]),
            ],

            'quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            'reason' => [
                'required',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],

        ];
    }

    /**
     * Friendly field names.
     */
    public function attributes(): array
    {
        return [

            'variant_id' => 'variant',

            'adjustment_type' => 'adjustment type',

            'quantity' => 'quantity',

            'reason' => 'reason',

            'notes' => 'notes',

        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [

            'adjustment_type.in' =>
                'Please choose a valid adjustment type.',

            'quantity.min' =>
                'Quantity cannot be negative.',

            'variant_id.exists' =>
                'The selected variant could not be found.',

        ];
    }
}