<?php

namespace App\Http\Controllers\Admin;

use App\Models\InventoryHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderReceipt;
use App\Models\PurchaseOrderReceiptItem;
use App\Models\SupplierReturn;
use App\Models\SupplierReturnItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseOrderReceivingController extends AdminController
{
    private const MOVEMENT_SUPPLIER_RETURN = 'supplier_return';
    private const MOVEMENT_SUPPLIER_RETURN_CANCELLED =
        'supplier_return_cancelled';

    public function index(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['items.product', 'items.variant']);

        $receipts = PurchaseOrderReceipt::query()
            ->where('purchase_order_id', $purchaseOrder->id)
            ->with([
                'items.purchaseOrderItem',
                'receiver:id,name,email',
            ])
            ->latest('received_at')
            ->get();

        $supplierReturns = SupplierReturn::query()
            ->where('purchase_order_id', $purchaseOrder->id)
            ->with([
                'items.purchaseOrderItem',
                'creator:id,name,email',
                'completer:id,name,email',
                'canceller:id,name,email',
            ])
            ->latest('returned_at')
            ->get();

        $activeReturnedByItem = SupplierReturnItem::query()
            ->whereHas(
                'supplierReturn',
                fn ($query) => $query
                    ->where('purchase_order_id', $purchaseOrder->id)
                    ->where('status', '!=', SupplierReturn::STATUS_CANCELLED)
            )
            ->selectRaw('purchase_order_item_id, SUM(quantity) as returned_quantity')
            ->groupBy('purchase_order_item_id')
            ->pluck('returned_quantity', 'purchase_order_item_id');

        $returnableItems = $purchaseOrder->items
            ->map(
                function (PurchaseOrderItem $item) use (
                    $activeReturnedByItem
                ): array {
                    $returned = (int) (
                        $activeReturnedByItem[$item->id] ?? 0
                    );

                    return [
                        'item' => $item,
                        'returned' => $returned,
                        'returnable' => max(
                            0,
                            (int) $item->quantity_received - $returned
                        ),
                    ];
                }
            )
            ->filter(fn (array $row): bool => $row['returnable'] > 0)
            ->values();

        $stats = [
            'net_received' => (int) $purchaseOrder->items
                ->sum('quantity_received'),
            'corrected' => (int) $receipts
                ->sum(fn ($receipt) => $receipt->total_corrected),
            'returned' => (int) $activeReturnedByItem->sum(),
            'open_returns' => $supplierReturns
                ->where('status', SupplierReturn::STATUS_SUBMITTED)
                ->count(),
        ];

        return view(
            'admin.purchase-orders.receiving',
            compact(
                'purchaseOrder',
                'receipts',
                'supplierReturns',
                'returnableItems',
                'activeReturnedByItem',
                'stats'
            )
        );
    }

    /**
     * Replace the original receive endpoint while preserving its route name.
     */
    public function store(
        Request $request,
        PurchaseOrder $purchaseOrder
    ): RedirectResponse {
        $this->ensureReceivable($purchaseOrder);

        $validated = $request->validate(
            [
                'items' => ['required', 'array', 'min:1', 'max:500'],
                'items.*.purchase_order_item_id' => [
                    'required',
                    'integer',
                    'distinct',
                    'exists:purchase_order_items,id',
                ],
                'items.*.quantity_received' => [
                    'nullable',
                    'integer',
                    'min:0',
                    'max:1000000',
                ],
                'receiving_notes' => ['nullable', 'string', 'max:5000'],
            ]
        );

        $totalToReceive = collect($validated['items'])
            ->sum(
                fn (array $item): int => max(
                    0,
                    (int) ($item['quantity_received'] ?? 0)
                )
            );

        if ($totalToReceive <= 0) {
            throw ValidationException::withMessages([
                'items' =>
                    'Enter a received quantity for at least one item.',
            ]);
        }

        $receivedTotal = 0;

        DB::transaction(
            function () use (
                $validated,
                $purchaseOrder,
                &$receivedTotal
            ): void {
                $lockedOrder = PurchaseOrder::query()
                    ->whereKey($purchaseOrder->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->ensureReceivable($lockedOrder);

                $receipt = PurchaseOrderReceipt::create([
                    'purchase_order_id' => $lockedOrder->id,
                    'supplier_id' => $lockedOrder->supplier_id,
                    'received_at' => now(),
                    'notes' => $validated['receiving_notes'] ?? null,
                    'received_by' => Auth::id(),
                ]);

                foreach ($validated['items'] as $submittedItem) {
                    $quantity = max(
                        0,
                        (int) ($submittedItem['quantity_received'] ?? 0)
                    );

                    if ($quantity === 0) {
                        continue;
                    }

                    $item = PurchaseOrderItem::query()
                        ->whereKey(
                            (int) $submittedItem['purchase_order_item_id']
                        )
                        ->where('purchase_order_id', $lockedOrder->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$item) {
                        throw ValidationException::withMessages([
                            'items' =>
                                'One receiving item does not belong to this purchase order.',
                        ]);
                    }

                    $remaining = max(
                        0,
                        (int) $item->quantity_ordered
                            - (int) $item->quantity_received
                    );

                    if ($quantity > $remaining) {
                        throw ValidationException::withMessages([
                            'items' =>
                                'The received quantity for ' . $item->item_name
                                . ' exceeds the remaining quantity of '
                                . number_format($remaining) . '.',
                        ]);
                    }

                    [$stockBefore, $stockAfter] = $this->adjustStock(
                        $item,
                        $quantity
                    );

                    $receiptItem = $receipt->items()->create([
                        'purchase_order_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity_received' => $quantity,
                        'quantity_corrected' => 0,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                    ]);

                    $item->forceFill([
                        'quantity_received' =>
                            (int) $item->quantity_received + $quantity,
                    ])->save();

                    $this->recordMovement(
                        $item,
                        $quantity,
                        $stockBefore,
                        $stockAfter,
                        InventoryHistory::TYPE_PURCHASE_ORDER_RECEIPT,
                        'Purchase order inventory received',
                        $validated['receiving_notes'] ?? null,
                        $receiptItem,
                        $lockedOrder
                    );

                    $receivedTotal += $quantity;
                }

                if (!$receipt->items()->exists()) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'No inventory quantity could be received.',
                    ]);
                }

                $lockedOrder->refresh();
                $lockedOrder->refreshReceivingStatus();
            }
        );

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with(
                'success',
                number_format($receivedTotal)
                    . ' inventory units were received and recorded.'
            );
    }

    public function correct(
        Request $request,
        PurchaseOrder $purchaseOrder,
        PurchaseOrderReceipt $receipt,
        PurchaseOrderReceiptItem $receiptItem
    ): RedirectResponse {
        $validated = $request->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:1000000',
            ],
            'reason' => ['required', 'string', 'min:5', 'max:5000'],
        ]);

        $this->ensureReceiptOwnership(
            $purchaseOrder,
            $receipt,
            $receiptItem
        );

        DB::transaction(
            function () use (
                $validated,
                $purchaseOrder,
                $receipt,
                $receiptItem
            ): void {
                $lockedReceiptItem = PurchaseOrderReceiptItem::query()
                    ->whereKey($receiptItem->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $item = PurchaseOrderItem::query()
                    ->whereKey($lockedReceiptItem->purchase_order_item_id)
                    ->where('purchase_order_id', $purchaseOrder->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $returnedQuantity = $this->activeReturnedQuantity($item);
                $correctableFromReceipt = max(
                    0,
                    (int) $lockedReceiptItem->quantity_received
                        - (int) $lockedReceiptItem->quantity_corrected
                );
                $correctableFromItem = max(
                    0,
                    (int) $item->quantity_received - $returnedQuantity
                );
                $correctable = min(
                    $correctableFromReceipt,
                    $correctableFromItem
                );
                $quantity = (int) $validated['quantity'];

                if ($quantity > $correctable) {
                    throw ValidationException::withMessages([
                        'quantity' =>
                            'Only ' . number_format($correctable)
                            . ' unit(s) can be corrected for this receipt line.',
                    ]);
                }

                [$stockBefore, $stockAfter] = $this->adjustStock(
                    $item,
                    -$quantity
                );

                $lockedReceiptItem->forceFill([
                    'quantity_corrected' =>
                        (int) $lockedReceiptItem->quantity_corrected
                            + $quantity,
                ])->save();

                $item->forceFill([
                    'quantity_received' => max(
                        0,
                        (int) $item->quantity_received - $quantity
                    ),
                ])->save();

                $this->recordMovement(
                    $item,
                    -$quantity,
                    $stockBefore,
                    $stockAfter,
                    InventoryHistory::TYPE_INVENTORY_CORRECTION,
                    'Purchase order receiving correction',
                    $validated['reason'],
                    $lockedReceiptItem,
                    $purchaseOrder
                );

                $purchaseOrder->refresh();
                $purchaseOrder->refreshReceivingStatus();
            }
        );

        return $this->redirectToManagement(
            $purchaseOrder,
            'The receiving correction was applied successfully.'
        );
    }

    public function storeReturn(
        Request $request,
        PurchaseOrder $purchaseOrder
    ): RedirectResponse {
        if ($purchaseOrder->isDraft() || $purchaseOrder->isCancelled()) {
            return $this->redirectToManagement(
                $purchaseOrder,
                'Returns can only be created for received inventory.',
                true
            );
        }

        $validated = $request->validate([
            'reason_category' => [
                'required',
                Rule::in([
                    'damaged',
                    'incorrect_item',
                    'quality_issue',
                    'over_delivery',
                    'other',
                ]),
            ],
            'reason' => ['required', 'string', 'min:5', 'max:5000'],
            'returned_at' => ['nullable', 'date', 'before_or_equal:now'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1', 'max:500'],
            'items.*.purchase_order_item_id' => [
                'required',
                'integer',
                'distinct',
                'exists:purchase_order_items,id',
            ],
            'items.*.quantity' => [
                'nullable',
                'integer',
                'min:0',
                'max:1000000',
            ],
        ]);

        $requestedTotal = collect($validated['items'])
            ->sum(fn (array $row): int => (int) ($row['quantity'] ?? 0));

        if ($requestedTotal <= 0) {
            throw ValidationException::withMessages([
                'items' =>
                    'Enter a return quantity for at least one item.',
            ]);
        }

        $supplierReturn = DB::transaction(
            function () use ($validated, $purchaseOrder): SupplierReturn {
                $lockedOrder = PurchaseOrder::query()
                    ->whereKey($purchaseOrder->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedOrder->isDraft() || $lockedOrder->isCancelled()) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'This purchase order cannot accept a supplier return.',
                    ]);
                }

                $return = SupplierReturn::create([
                    'purchase_order_id' => $lockedOrder->id,
                    'supplier_id' => $lockedOrder->supplier_id,
                    'status' => SupplierReturn::STATUS_SUBMITTED,
                    'reason_category' => $validated['reason_category'],
                    'reason' => $validated['reason'],
                    'returned_at' => $validated['returned_at'] ?? now(),
                    'expected_credit' => 0,
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                $expectedCredit = 0;

                foreach ($validated['items'] as $submittedItem) {
                    $quantity = max(
                        0,
                        (int) ($submittedItem['quantity'] ?? 0)
                    );

                    if ($quantity === 0) {
                        continue;
                    }

                    $item = PurchaseOrderItem::query()
                        ->whereKey(
                            (int) $submittedItem['purchase_order_item_id']
                        )
                        ->where('purchase_order_id', $lockedOrder->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$item) {
                        throw ValidationException::withMessages([
                            'items' =>
                                'One return item does not belong to this purchase order.',
                        ]);
                    }

                    $alreadyReturned = $this->activeReturnedQuantity($item);
                    $returnable = max(
                        0,
                        (int) $item->quantity_received - $alreadyReturned
                    );

                    if ($quantity > $returnable) {
                        throw ValidationException::withMessages([
                            'items' =>
                                'Only ' . number_format($returnable)
                                . ' unit(s) of ' . $item->item_name
                                . ' can be returned.',
                        ]);
                    }

                    [$stockBefore, $stockAfter] = $this->adjustStock(
                        $item,
                        -$quantity
                    );

                    $returnItem = $return->items()->create([
                        'purchase_order_item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => $quantity,
                        'unit_cost' => $item->unit_cost,
                        'line_total' => round(
                            $quantity * (float) $item->unit_cost,
                            2
                        ),
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                    ]);

                    $expectedCredit += (float) $returnItem->line_total;

                    $this->recordMovement(
                        $item,
                        -$quantity,
                        $stockBefore,
                        $stockAfter,
                        self::MOVEMENT_SUPPLIER_RETURN,
                        'Inventory returned to supplier',
                        $validated['reason'],
                        $returnItem,
                        $lockedOrder
                    );
                }

                if (!$return->items()->exists()) {
                    throw ValidationException::withMessages([
                        'items' => 'No inventory was selected for return.',
                    ]);
                }

                $return->forceFill([
                    'expected_credit' => round($expectedCredit, 2),
                ])->save();

                return $return;
            }
        );

        return $this->redirectToManagement(
            $purchaseOrder,
            'Supplier return ' . $supplierReturn->reference
                . ' was submitted and stock was deducted.'
        );
    }

    public function completeReturn(
        Request $request,
        PurchaseOrder $purchaseOrder,
        SupplierReturn $supplierReturn
    ): RedirectResponse {
        $this->ensureReturnOwnership($purchaseOrder, $supplierReturn);

        $validated = $request->validate([
            'actual_credit' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999999.99',
            ],
        ]);

        DB::transaction(
            function () use ($validated, $supplierReturn): void {
                $lockedReturn = SupplierReturn::query()
                    ->whereKey($supplierReturn->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$lockedReturn->isSubmitted()) {
                    throw ValidationException::withMessages([
                        'actual_credit' =>
                            'Only a submitted supplier return can be completed.',
                    ]);
                }

                $lockedReturn->forceFill([
                    'status' => SupplierReturn::STATUS_COMPLETED,
                    'actual_credit' => round(
                        (float) $validated['actual_credit'],
                        2
                    ),
                    'completed_by' => Auth::id(),
                    'completed_at' => now(),
                ])->save();
            }
        );

        return $this->redirectToManagement(
            $purchaseOrder,
            'The supplier return was completed.'
        );
    }

    public function cancelReturn(
        Request $request,
        PurchaseOrder $purchaseOrder,
        SupplierReturn $supplierReturn
    ): RedirectResponse {
        $this->ensureReturnOwnership($purchaseOrder, $supplierReturn);

        $validated = $request->validate([
            'cancellation_reason' => [
                'required',
                'string',
                'min:5',
                'max:5000',
            ],
        ]);

        DB::transaction(
            function () use (
                $validated,
                $purchaseOrder,
                $supplierReturn
            ): void {
                $lockedReturn = SupplierReturn::query()
                    ->whereKey($supplierReturn->id)
                    ->with('items')
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$lockedReturn->isSubmitted()) {
                    throw ValidationException::withMessages([
                        'cancellation_reason' =>
                            'Only a submitted supplier return can be cancelled.',
                    ]);
                }

                foreach ($lockedReturn->items as $returnItem) {
                    $item = PurchaseOrderItem::query()
                        ->whereKey($returnItem->purchase_order_item_id)
                        ->where('purchase_order_id', $purchaseOrder->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    [$stockBefore, $stockAfter] = $this->adjustStock(
                        $item,
                        (int) $returnItem->quantity
                    );

                    $this->recordMovement(
                        $item,
                        (int) $returnItem->quantity,
                        $stockBefore,
                        $stockAfter,
                        self::MOVEMENT_SUPPLIER_RETURN_CANCELLED,
                        'Supplier return cancelled; stock restored',
                        $validated['cancellation_reason'],
                        $returnItem,
                        $purchaseOrder
                    );
                }

                $lockedReturn->forceFill([
                    'status' => SupplierReturn::STATUS_CANCELLED,
                    'cancelled_by' => Auth::id(),
                    'cancelled_at' => now(),
                    'cancellation_reason' =>
                        $validated['cancellation_reason'],
                ])->save();
            }
        );

        return $this->redirectToManagement(
            $purchaseOrder,
            'The supplier return was cancelled and its stock was restored.'
        );
    }

    private function ensureReceivable(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->isCancelled()) {
            throw ValidationException::withMessages([
                'items' =>
                    'Inventory cannot be received against a cancelled purchase order.',
            ]);
        }

        if ($purchaseOrder->isDraft()) {
            throw ValidationException::withMessages([
                'items' =>
                    'Mark this purchase order as ordered before receiving inventory.',
            ]);
        }

        if ($purchaseOrder->isReceived()) {
            throw ValidationException::withMessages([
                'items' =>
                    'This purchase order has already been fully received.',
            ]);
        }
    }

    private function adjustStock(
        PurchaseOrderItem $item,
        int $quantityChange
    ): array {
        if ($item->product_variant_id) {
            $stockModel = ProductVariant::query()
                ->whereKey($item->product_variant_id)
                ->lockForUpdate()
                ->first();

            if (!$stockModel) {
                throw ValidationException::withMessages([
                    'items' =>
                        'The variant for ' . $item->item_name
                        . ' no longer exists.',
                ]);
            }
        } else {
            $stockModel = Product::query()
                ->whereKey($item->product_id)
                ->lockForUpdate()
                ->first();

            if (!$stockModel) {
                throw ValidationException::withMessages([
                    'items' =>
                        'The product for ' . $item->item_name
                        . ' no longer exists.',
                ]);
            }
        }

        $stockBefore = max(0, (int) ($stockModel->stock ?? 0));
        $stockAfter = $stockBefore + $quantityChange;

        if ($stockAfter < 0) {
            throw ValidationException::withMessages([
                'items' =>
                    'There is not enough current stock for '
                    . $item->item_name . '. Available stock: '
                    . number_format($stockBefore) . '.',
            ]);
        }

        $stockModel->forceFill(['stock' => $stockAfter])->save();

        return [$stockBefore, $stockAfter];
    }

    private function activeReturnedQuantity(
        PurchaseOrderItem $item
    ): int {
        return (int) SupplierReturnItem::query()
            ->where('purchase_order_item_id', $item->id)
            ->whereHas(
                'supplierReturn',
                fn ($query) => $query->where(
                    'status',
                    '!=',
                    SupplierReturn::STATUS_CANCELLED
                )
            )
            ->sum('quantity');
    }

    private function recordMovement(
        PurchaseOrderItem $item,
        int $quantityChange,
        int $stockBefore,
        int $stockAfter,
        string $movementType,
        string $reason,
        ?string $notes,
        object $reference,
        PurchaseOrder $purchaseOrder
    ): void {
        InventoryHistory::create([
            'product_id' => $item->product_id,
            'product_variant_id' => $item->product_variant_id,
            'order_id' => null,
            'user_id' => Auth::id(),
            'quantity_change' => $quantityChange,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'movement_type' => $movementType,
            'reason' => $reason,
            'notes' => $notes,
            'reference_type' => $reference::class,
            'reference_id' => (string) $reference->id,
            'metadata' => [
                'purchase_order_id' => $purchaseOrder->id,
                'purchase_order_reference' => $purchaseOrder->reference,
                'purchase_order_item_id' => $item->id,
                'item_name' => $item->item_name,
                'sku' => $item->sku,
                'supplier_name' => $purchaseOrder->supplier_name,
            ],
        ]);
    }

    private function ensureReceiptOwnership(
        PurchaseOrder $purchaseOrder,
        PurchaseOrderReceipt $receipt,
        PurchaseOrderReceiptItem $receiptItem
    ): void {
        abort_unless(
            (int) $receipt->purchase_order_id === (int) $purchaseOrder->id
                && (int) $receiptItem->purchase_order_receipt_id
                    === (int) $receipt->id,
            404
        );
    }

    private function ensureReturnOwnership(
        PurchaseOrder $purchaseOrder,
        SupplierReturn $supplierReturn
    ): void {
        abort_unless(
            (int) $supplierReturn->purchase_order_id
                === (int) $purchaseOrder->id,
            404
        );
    }

    private function redirectToManagement(
        PurchaseOrder $purchaseOrder,
        string $message,
        bool $error = false
    ): RedirectResponse {
        return redirect()
            ->route('admin.purchase-orders.receiving.index', $purchaseOrder)
            ->with($error ? 'error' : 'success', $message);
    }
}
