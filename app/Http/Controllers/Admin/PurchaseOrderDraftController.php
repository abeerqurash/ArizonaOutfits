<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseOrderDraftController extends AdminController
{
    public function edit(
        PurchaseOrder $purchaseOrder
    ): View|RedirectResponse {
        if (!$purchaseOrder->isDraft()) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with(
                    'error',
                    'Only draft purchase orders can be edited.'
                );
        }

        $purchaseOrder->load([
            'items.product',
            'items.variant',
            'supplier',
        ]);

        $suppliers = Supplier::query()
            ->where(
                function ($query) use ($purchaseOrder): void {
                    $query
                        ->where('status', Supplier::STATUS_ACTIVE)
                        ->orWhere(
                            'id',
                            $purchaseOrder->supplier_id
                        );
                }
            )
            ->orderByDesc('is_preferred')
            ->orderBy('company_name')
            ->get([
                'id',
                'company_name',
                'supplier_code',
                'email',
                'phone',
                'alternate_phone',
                'address_line_1',
                'address_line_2',
                'city',
                'state',
                'postal_code',
                'country',
                'currency',
                'lead_time_days',
                'status',
                'is_preferred',
            ]);

        $catalogItems = $this->catalogItems();

        return view(
            'admin.purchase-orders.edit',
            compact(
                'purchaseOrder',
                'suppliers',
                'catalogItems'
            )
        );
    }

    public function update(
        Request $request,
        PurchaseOrder $purchaseOrder
    ): RedirectResponse {
        if (!$purchaseOrder->isDraft()) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with(
                    'error',
                    'Only draft purchase orders can be edited.'
                );
        }

        $validated = $request->validate(
            [
                'version' => ['required', 'integer', 'min:0'],
                'supplier_id' => [
                    'required',
                    'integer',
                    Rule::exists('suppliers', 'id')->where(
                        fn ($query) => $query->whereNull('deleted_at')
                    ),
                ],
                'order_date' => ['required', 'date'],
                'expected_date' => [
                    'nullable',
                    'date',
                    'after_or_equal:order_date',
                ],
                'tax_amount' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:999999999999.99',
                ],
                'shipping_amount' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:999999999999.99',
                ],
                'discount_amount' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:999999999999.99',
                ],
                'notes' => ['nullable', 'string', 'max:5000'],
                'internal_notes' => ['nullable', 'string', 'max:5000'],
                'items' => ['required', 'array', 'min:1', 'max:500'],
                'items.*.id' => [
                    'nullable',
                    'integer',
                    'distinct',
                ],
                'items.*.identifier' => [
                    'required',
                    'string',
                    'max:100',
                    'distinct',
                    'regex:/^(product|variant):[1-9][0-9]*$/',
                ],
                'items.*.quantity' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:1000000',
                ],
                'items.*.unit_cost' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:999999999999.99',
                ],
            ],
            [
                'items.required' =>
                    'The draft must contain at least one item.',
                'items.min' =>
                    'The draft must contain at least one item.',
                'items.*.identifier.distinct' =>
                    'The same product or variant cannot appear twice.',
                'items.*.identifier.regex' =>
                    'One of the submitted products is invalid.',
            ]
        );

        $supplier = Supplier::query()
            ->whereKey((int) $validated['supplier_id'])
            ->first();

        $changingSupplier = $supplier
            && (int) $supplier->id !== (int) $purchaseOrder->supplier_id;

        if (
            !$supplier
            || $supplier->isBlocked()
            || ($supplier->isInactive() && $changingSupplier)
        ) {
            throw ValidationException::withMessages([
                'supplier_id' =>
                    'Please select an active, unblocked supplier.',
            ]);
        }

        DB::transaction(
            function () use (
                $validated,
                $supplier,
                $purchaseOrder
            ): void {
                $lockedOrder = PurchaseOrder::query()
                    ->whereKey($purchaseOrder->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$lockedOrder->isDraft()) {
                    throw ValidationException::withMessages([
                        'status' =>
                            'This purchase order is no longer a draft and cannot be edited.',
                    ]);
                }

                $currentVersion = (int) optional(
                    $lockedOrder->updated_at
                )->getTimestamp();

                if ($currentVersion !== (int) $validated['version']) {
                    throw ValidationException::withMessages([
                        'version' =>
                            'This draft changed after you opened it. Reload the page and review the newest values before saving.',
                    ]);
                }

                $supplierChanged =
                    (int) $lockedOrder->supplier_id
                    !== (int) $supplier->id;

                $existingItems = $lockedOrder
                    ->items()
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $keptItemIds = collect();

                $lockedOrder->fill([
                    'supplier_id' => $supplier->id,
                    'supplier_name' => $supplier->company_name,
                    'supplier_email' => $supplier->email,
                    'supplier_phone' =>
                        $supplier->phone ?: $supplier->alternate_phone,
                    'supplier_address' =>
                        $supplier->full_address !== ''
                            ? $supplier->full_address
                            : null,
                    'order_date' => $validated['order_date'],
                    'expected_date' => $validated['expected_date'] ?? null,
                    'tax_amount' => round(
                        (float) ($validated['tax_amount'] ?? 0),
                        2
                    ),
                    'shipping_amount' => round(
                        (float) ($validated['shipping_amount'] ?? 0),
                        2
                    ),
                    'discount_amount' => round(
                        (float) ($validated['discount_amount'] ?? 0),
                        2
                    ),
                    'currency' => $supplier->currency ?: 'GBP',
                    'notes' => $validated['notes'] ?? null,
                    'internal_notes' =>
                        $validated['internal_notes'] ?? null,
                ]);
                $lockedOrder->save();

                foreach ($validated['items'] as $submittedItem) {
                    $resolved = $this->resolveIdentifier(
                        $submittedItem['identifier']
                    );

                    $existingItem = null;

                    if (!empty($submittedItem['id'])) {
                        $existingItem = $existingItems->get(
                            (int) $submittedItem['id']
                        );

                        if (!$existingItem) {
                            throw ValidationException::withMessages([
                                'items' =>
                                    'One purchase-order item no longer belongs to this draft.',
                            ]);
                        }

                        $expectedIdentifier =
                            $existingItem->product_variant_id
                                ? 'variant:' . $existingItem->product_variant_id
                                : 'product:' . $existingItem->product_id;

                        if ($expectedIdentifier !== $submittedItem['identifier']) {
                            throw ValidationException::withMessages([
                                'items' =>
                                    'A purchase-order item identifier was changed unexpectedly.',
                            ]);
                        }

                        $keptItemIds->push($existingItem->id);
                    }

                    $quantity = max(1, (int) $submittedItem['quantity']);
                    $unitCost = max(
                        0,
                        round((float) $submittedItem['unit_cost'], 2)
                    );

                    $sku = $existingItem
                        ? $existingItem->sku
                        : $resolved['sku'];

                    if ($supplierChanged) {
                        $supplierPrice = $this->supplierPrice(
                            $supplier,
                            $resolved['product_id'],
                            $resolved['product_variant_id']
                        );

                        if ($supplierPrice) {
                            $unitCost = (float) $supplierPrice->unit_cost;
                            $quantity = max(
                                $quantity,
                                (int) $supplierPrice->minimum_order_quantity
                            );
                            $sku = $supplierPrice->supplier_sku
                                ?: $resolved['sku'];
                        } else {
                            $sku = $resolved['sku'];
                        }
                    }

                    $itemData = [
                        'product_id' => $resolved['product_id'],
                        'product_variant_id' =>
                            $resolved['product_variant_id'],
                        'item_name' => $resolved['item_name'],
                        'sku' => $sku,
                        'variant_name' => $resolved['variant_name'],
                        'quantity_ordered' => $quantity,
                        'unit_cost' => $unitCost,
                        'stock_before' => $existingItem
                            ? $existingItem->stock_before
                            : $resolved['stock'],
                        'reorder_point' => $existingItem
                            ? $existingItem->reorder_point
                            : $resolved['reorder_point'],
                        'suggested_quantity' => $existingItem
                            ? $existingItem->suggested_quantity
                            : $quantity,
                    ];

                    if ($existingItem) {
                        $existingItem->update($itemData);
                    } else {
                        $createdItem = $lockedOrder
                            ->items()
                            ->create([
                                ...$itemData,
                                'quantity_received' => 0,
                            ]);

                        $keptItemIds->push($createdItem->id);
                    }
                }

                $lockedOrder->items()
                    ->whereNotIn('id', $keptItemIds->all())
                    ->delete();

                if (!$lockedOrder->items()->exists()) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'The draft must contain at least one item.',
                    ]);
                }

                $lockedOrder->recalculateTotals();
            }
        );

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with(
                'success',
                'Purchase order ' . $purchaseOrder->reference
                    . ' was updated successfully.'
            );
    }

    private function catalogItems(): Collection
    {
        $items = collect();

        $products = Product::query()
            ->with([
                'variants' => fn ($query) => $query->orderBy('sku'),
            ])
            ->orderBy('title')
            ->get();

        foreach ($products as $product) {
            if ($product->variants->isEmpty()) {
                $items->push($this->productData($product));
                continue;
            }

            foreach ($product->variants as $variant) {
                $items->push($this->variantData($variant, $product));
            }
        }

        return $items->values();
    }

    private function resolveIdentifier(string $identifier): array
    {
        [$type, $id] = explode(':', $identifier, 2);
        $id = (int) $id;

        if ($type === 'product') {
            $product = Product::query()->find($id);

            if (!$product || $product->variants()->exists()) {
                throw ValidationException::withMessages([
                    'items' =>
                        'One selected product is missing or now uses variants.',
                ]);
            }

            return $this->productData($product);
        }

        $variant = ProductVariant::query()
            ->with('product')
            ->find($id);

        if (!$variant || !$variant->product) {
            throw ValidationException::withMessages([
                'items' =>
                    'One selected product variant could not be found.',
            ]);
        }

        return $this->variantData($variant, $variant->product);
    }

    private function productData(Product $product): array
    {
        return [
            'identifier' => 'product:' . $product->id,
            'product_id' => (int) $product->id,
            'product_variant_id' => null,
            'item_name' => (string) $product->title,
            'variant_name' => null,
            'sku' => $product->sku,
            'stock' => max(0, (int) ($product->stock ?? 0)),
            'reorder_point' => max(
                0,
                (int) ($product->reorder_point ?? 0)
            ),
            'unit_cost' => round(
                max(0, (float) ($product->cost_price ?? 0)),
                2
            ),
        ];
    }

    private function variantData(
        ProductVariant $variant,
        Product $product
    ): array {
        $variantName = $this->variantLabel($variant->options ?? []);

        return [
            'identifier' => 'variant:' . $variant->id,
            'product_id' => (int) $product->id,
            'product_variant_id' => (int) $variant->id,
            'item_name' => (string) $product->title,
            'variant_name' => $variantName,
            'sku' => $variant->sku ?: $product->sku,
            'stock' => max(0, (int) ($variant->stock ?? 0)),
            'reorder_point' => max(
                0,
                (int) ($variant->reorder_point ?? 0)
            ),
            'unit_cost' => round(
                max(0, (float) ($product->cost_price ?? 0)),
                2
            ),
        ];
    }

    private function variantLabel(mixed $options): ?string
    {
        if (!is_array($options)) {
            return null;
        }

        $label = collect($options)
            ->map(
                function (mixed $option): ?string {
                    if (!is_array($option)) {
                        return null;
                    }

                    $name = trim((string) ($option['option_name'] ?? ''));
                    $value = trim((string) ($option['value_label'] ?? ''));

                    if ($value === '') {
                        return null;
                    }

                    return $name !== '' ? $name . ': ' . $value : $value;
                }
            )
            ->filter()
            ->implode(', ');

        return $label !== '' ? $label : null;
    }

    private function supplierPrice(
        Supplier $supplier,
        int $productId,
        ?int $variantId
    ): ?SupplierProduct {
        $query = $supplier
            ->supplierProducts()
            ->active()
            ->where('product_id', $productId);

        if ($variantId) {
            $exact = (clone $query)
                ->where('product_variant_id', $variantId)
                ->first();

            if ($exact) {
                return $exact;
            }
        }

        return (clone $query)
            ->whereNull('product_variant_id')
            ->first();
    }
}
