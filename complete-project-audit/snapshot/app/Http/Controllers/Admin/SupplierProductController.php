<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SupplierProductController extends AdminController
{
    public function index(
        Request $request,
        Supplier $supplier
    ): View {
        $search = trim($request->string('search')->value());

        $assignmentQuery = $supplier
            ->supplierProducts()
            ->with([
                'product:id,title,sku,status,cost_price',
                'variant:id,product_id,sku,options',
            ]);

        if ($search !== '') {
            $assignmentQuery->where(
                function ($query) use ($search): void {
                    $query
                        ->where('supplier_sku', 'like', '%' . $search . '%')
                        ->orWhereHas(
                            'product',
                            fn ($productQuery) => $productQuery
                                ->where('title', 'like', '%' . $search . '%')
                                ->orWhere('sku', 'like', '%' . $search . '%')
                        )
                        ->orWhereHas(
                            'variant',
                            fn ($variantQuery) => $variantQuery
                                ->where('sku', 'like', '%' . $search . '%')
                        );
                }
            );
        }

        $assignments = $assignmentQuery
            ->orderByDesc('is_preferred')
            ->orderByDesc('is_active')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $products = Product::query()
            ->with([
                'variants' => fn ($query) => $query
                    ->orderBy('sku')
                    ->select([
                        'id',
                        'product_id',
                        'sku',
                        'options',
                    ]),
            ])
            ->orderBy('title')
            ->get([
                'id',
                'title',
                'sku',
                'status',
                'cost_price',
            ]);

        $stats = [
            'total' => $supplier->supplierProducts()->count(),
            'active' => $supplier->supplierProducts()->active()->count(),
            'preferred' => $supplier->supplierProducts()
                ->where('is_preferred', true)
                ->count(),
            'average_cost' => round(
                (float) $supplier->supplierProducts()
                    ->active()
                    ->avg('unit_cost'),
                2
            ),
        ];

        return view(
            'admin.suppliers.products',
            compact(
                'supplier',
                'assignments',
                'products',
                'stats',
                'search'
            )
        );
    }

    public function store(
        Request $request,
        Supplier $supplier
    ): RedirectResponse {
        $validated = $this->validatedData(
            $request,
            $supplier
        );

        $variant = $this->validatedVariant(
            $validated['product_id'],
            $validated['product_variant_id'] ?? null
        );

        $variantKey = (int) ($variant?->id ?? 0);

        $alreadyAssigned = $supplier
            ->supplierProducts()
            ->where('product_id', $validated['product_id'])
            ->where('variant_key', $variantKey)
            ->exists();

        if ($alreadyAssigned) {
            throw ValidationException::withMessages([
                'product_id' =>
                    'This product or exact variant is already assigned to this supplier.',
            ]);
        }

        $validated['product_variant_id'] = $variant?->id;
        $validated['is_preferred'] = $request->boolean('is_preferred');
        $validated['is_active'] = $request->boolean('is_active');

        DB::transaction(
            fn () => $supplier->supplierProducts()->create($validated)
        );

        return redirect()
            ->route('admin.suppliers.products.index', $supplier)
            ->with('success', 'The supplier product was assigned successfully.');
    }

    public function update(
        Request $request,
        Supplier $supplier,
        SupplierProduct $supplierProduct
    ): RedirectResponse {
        $this->ensureOwnership($supplier, $supplierProduct);

        $validated = $request->validate([
            'supplier_sku' => [
                'nullable',
                'string',
                'max:120',
                Rule::unique('supplier_products', 'supplier_sku')
                    ->where(
                        fn ($query) => $query->where(
                            'supplier_id',
                            $supplier->id
                        )
                    )
                    ->ignore($supplierProduct->id),
            ],
            'unit_cost' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999999.99',
            ],
            'minimum_order_quantity' => [
                'required',
                'integer',
                'min:1',
                'max:1000000',
            ],
            'lead_time_days' => [
                'nullable',
                'integer',
                'min:0',
                'max:3650',
            ],
            'is_preferred' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $validated['supplier_sku'] = $this->nullableString(
            $validated['supplier_sku'] ?? null
        );
        $validated['is_preferred'] = $request->boolean('is_preferred');
        $validated['is_active'] = $request->boolean('is_active');

        $supplierProduct->update($validated);

        return redirect()
            ->route('admin.suppliers.products.index', $supplier)
            ->with('success', 'The supplier product was updated successfully.');
    }

    public function destroy(
        Supplier $supplier,
        SupplierProduct $supplierProduct
    ): RedirectResponse {
        $this->ensureOwnership($supplier, $supplierProduct);

        $supplierProduct->delete();

        return redirect()
            ->route('admin.suppliers.products.index', $supplier)
            ->with('success', 'The supplier product assignment was removed.');
    }

    /**
     * Return the supplier prices used by the purchase-order create page.
     */
    public function pricing(Supplier $supplier): JsonResponse
    {
        $pricing = $supplier
            ->supplierProducts()
            ->active()
            ->get()
            ->mapWithKeys(
                function (SupplierProduct $assignment): array {
                    $key = $assignment->product_variant_id
                        ? 'variant:' . $assignment->product_variant_id
                        : 'product:' . $assignment->product_id;

                    return [
                        $key => [
                            'product_id' => (int) $assignment->product_id,
                            'product_variant_id' =>
                                $assignment->product_variant_id
                                    ? (int) $assignment->product_variant_id
                                    : null,
                            'supplier_sku' => $assignment->supplier_sku,
                            'unit_cost' => (float) $assignment->unit_cost,
                            'minimum_order_quantity' =>
                                (int) $assignment->minimum_order_quantity,
                            'lead_time_days' =>
                                $assignment->lead_time_days !== null
                                    ? (int) $assignment->lead_time_days
                                    : null,
                            'is_preferred' => (bool) $assignment->is_preferred,
                        ],
                    ];
                }
            );

        return response()->json([
            'supplier_id' => (int) $supplier->id,
            'pricing' => $pricing,
        ]);
    }

    private function validatedData(
        Request $request,
        Supplier $supplier
    ): array {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id'),
            ],
            'product_variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id'),
            ],
            'supplier_sku' => [
                'nullable',
                'string',
                'max:120',
                Rule::unique('supplier_products', 'supplier_sku')
                    ->where(
                        fn ($query) => $query->where(
                            'supplier_id',
                            $supplier->id
                        )
                    ),
            ],
            'unit_cost' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999999.99',
            ],
            'minimum_order_quantity' => [
                'required',
                'integer',
                'min:1',
                'max:1000000',
            ],
            'lead_time_days' => [
                'nullable',
                'integer',
                'min:0',
                'max:3650',
            ],
            'is_preferred' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $validated['supplier_sku'] = $this->nullableString(
            $validated['supplier_sku'] ?? null
        );

        return $validated;
    }

    private function validatedVariant(
        int $productId,
        mixed $variantId
    ): ?ProductVariant {
        if (blank($variantId)) {
            return null;
        }

        $variant = ProductVariant::query()
            ->whereKey((int) $variantId)
            ->first();

        if (!$variant || (int) $variant->product_id !== $productId) {
            throw ValidationException::withMessages([
                'product_variant_id' =>
                    'The selected variant does not belong to the selected product.',
            ]);
        }

        return $variant;
    }

    private function ensureOwnership(
        Supplier $supplier,
        SupplierProduct $supplierProduct
    ): void {
        abort_unless(
            (int) $supplierProduct->supplier_id === (int) $supplier->id,
            404
        );
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
