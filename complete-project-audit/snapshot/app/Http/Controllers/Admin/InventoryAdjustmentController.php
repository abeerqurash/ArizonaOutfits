<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryAdjustmentRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InventoryAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class InventoryAdjustmentController extends Controller
{
    public function __construct(
        private readonly InventoryAdjustmentService $inventoryAdjustmentService
    ) {
    }

    /**
     * Show inventory page.
     */
    public function edit(Product $product): View
    {
        $product->load([
            'variants',
            'inventoryHistories.user',
            'inventoryHistories.order',
        ]);

        return view(
            'admin.products.inventory',
            compact('product')
        );
    }

    /**
     * Save inventory adjustment.
     */
    public function update(
        InventoryAdjustmentRequest $request,
        Product $product
    ): RedirectResponse {

        try {

            /*
            |--------------------------------------------------------------------------
            | Variant adjustment
            |--------------------------------------------------------------------------
            */

            if ($request->filled('variant_id')) {

                $variant = ProductVariant::query()

                    ->where('product_id', $product->id)

                    ->findOrFail(
                        $request->integer('variant_id')
                    );

                $this->inventoryAdjustmentService
                    ->adjustVariant(

                        variant: $variant,

                        type: $request->input(
                            'adjustment_type'
                        ),

                        quantity: $request->integer(
                            'quantity'
                        ),

                        reason: $request->input(
                            'reason'
                        ),

                        notes: $request->input(
                            'notes'
                        ),

                        user: $request->user()

                    );

            } else {

                /*
                |--------------------------------------------------------------------------
                | Simple product adjustment
                |--------------------------------------------------------------------------
                */

                $this->inventoryAdjustmentService
                    ->adjustProduct(

                        product: $product,

                        type: $request->input(
                            'adjustment_type'
                        ),

                        quantity: $request->integer(
                            'quantity'
                        ),

                        reason: $request->input(
                            'reason'
                        ),

                        notes: $request->input(
                            'notes'
                        ),

                        user: $request->user()

                    );

            }

            return redirect()

                ->route(
                    'admin.products.inventory.edit',
                    $product
                )

                ->with(
                    'success',
                    'Inventory updated successfully.'
                );

        } catch (Throwable $exception) {

            report($exception);

            return back()

                ->withInput()

                ->with(
                    'error',
                    $exception->getMessage()
                );

        }

    }
}