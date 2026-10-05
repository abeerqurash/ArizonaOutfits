<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class InventoryAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
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