<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supplier;
use App\Models\SupplierRating;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SupplierRatingController extends AdminController
{
    public function store(
        Request $request,
        Supplier $supplier
    ): RedirectResponse {
        $validated = $this->validateRating(
            $request,
            $supplier
        );

        $validated['overall_rating'] =
            $this->calculateOverallRating(
                $validated
            );

        $validated['would_recommend'] =
            $request->boolean('would_recommend');

        $validated['rated_by'] = Auth::id();
        $validated['rated_at'] = now();

        $supplier->ratings()->create(
            $validated
        );

        return redirect()
            ->route(
                'admin.suppliers.show',
                $supplier
            )
            ->with(
                'success',
                'Supplier performance rating was added successfully.'
            );
    }

    public function update(
        Request $request,
        Supplier $supplier,
        SupplierRating $rating
    ): RedirectResponse {
        $this->ensureRatingBelongsToSupplier(
            $supplier,
            $rating
        );

        $validated = $this->validateRating(
            $request,
            $supplier,
            $rating
        );

        $validated['overall_rating'] =
            $this->calculateOverallRating(
                $validated
            );

        $validated['would_recommend'] =
            $request->boolean('would_recommend');

        $validated['rated_by'] = Auth::id();
        $validated['rated_at'] = now();

        $rating->update(
            $validated
        );

        return redirect()
            ->route(
                'admin.suppliers.show',
                $supplier
            )
            ->with(
                'success',
                'Supplier performance rating was updated successfully.'
            );
    }

    public function destroy(
        Supplier $supplier,
        SupplierRating $rating
    ): RedirectResponse {
        $this->ensureRatingBelongsToSupplier(
            $supplier,
            $rating
        );

        $rating->delete();

        return redirect()
            ->route(
                'admin.suppliers.show',
                $supplier
            )
            ->with(
                'success',
                'Supplier performance rating was deleted successfully.'
            );
    }

    private function validateRating(
        Request $request,
        Supplier $supplier,
        ?SupplierRating $rating = null
    ): array {
        $scoreRules = [
            'required',
            'integer',
            'between:1,5',
        ];

        return $request->validateWithBag(
            'supplierRating',
            [
                'purchase_order_id' => [
                    'nullable',

                    Rule::exists(
                        'purchase_orders',
                        'id'
                    )->where(
                        fn ($query) =>
                            $query->where(
                                'supplier_id',
                                $supplier->id
                            )
                    ),

                    Rule::unique(
                        'supplier_ratings',
                        'purchase_order_id'
                    )->ignore($rating?->id),
                ],

                'quality_rating' => $scoreRules,
                'delivery_rating' => $scoreRules,
                'communication_rating' => $scoreRules,
                'pricing_rating' => $scoreRules,

                'title' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'review' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],

                'would_recommend' => [
                    'nullable',
                    'boolean',
                ],
            ],
            [
                'purchase_order_id.exists' =>
                    'The selected purchase order does not belong to this supplier.',

                'purchase_order_id.unique' =>
                    'This purchase order already has a supplier rating.',

                '*.between' =>
                    'Every supplier score must be between 1 and 5.',
            ]
        );
    }

    private function calculateOverallRating(
        array $validated
    ): float {
        return round(
            collect([
                $validated['quality_rating'],
                $validated['delivery_rating'],
                $validated['communication_rating'],
                $validated['pricing_rating'],
            ])->average(),
            2
        );
    }

    private function ensureRatingBelongsToSupplier(
        Supplier $supplier,
        SupplierRating $rating
    ): void {
        abort_unless(
            (int) $rating->supplier_id
                === (int) $supplier->id,
            404
        );
    }
}
