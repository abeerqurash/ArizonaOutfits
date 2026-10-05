<?php
namespace App\Services;
use App\Models\InventoryHistory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InventoryHistoryService;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;
class InventoryService
{
    public function __construct(
        private readonly InventoryAlertService $inventoryAlertService,
        private readonly InventoryHistoryService $inventoryHistoryService
    ) {}
    public function deductForOrder(
        Order $order
    ): Order {
        $processedOrder = DB::transaction(
            function () use ($order): Order {
                $lockedOrder = Order::withTrashed()
                    ->with('items')
                    ->lockForUpdate()
                    ->findOrFail($order->id);
                if (
                    $lockedOrder->inventory_deducted_at !== null
                    || $lockedOrder->inventory_restored_at !== null
                ) {
                    return $lockedOrder;
                }
                if (data_get($lockedOrder->payment_metadata, 'inventory_managed', true) === false) return $lockedOrder;
                if ($lockedOrder->items->isEmpty()) {
                    throw new RuntimeException(
                        'The order contains no items.'
                    );
                }
                foreach ($lockedOrder->items as $orderItem) {
                    $quantity = max(
                        1,
                        (int) $orderItem->quantity
                    );
                    if ($orderItem->variant_id) {
                        $this->deductVariantStock(
                            order: $lockedOrder,
                            variantId: (int) $orderItem->variant_id,
                            productId: $orderItem->product_id
                                ? (int) $orderItem->product_id
                                : null,
                            quantity: $quantity,
                            productTitle: (string) $orderItem->product_title
                        );
                        continue;
                    }
                    if ($orderItem->product_id) {
                        $this->deductProductStock(
                            order: $lockedOrder,
                            productId: (int) $orderItem->product_id,
                            quantity: $quantity,
                            productTitle: (string) $orderItem->product_title
                        );
                        continue;
                    }
                    throw new RuntimeException(
                        'Inventory information is missing for '
                            . (
                                $orderItem->product_title
                                ?: 'an order item'
                            )
                            . '.'
                    );
                }
                $lockedOrder->update([
                    'inventory_deducted_at' => now(),
                ]);
                return $lockedOrder->fresh([
                    'items',
                ]);
            },
            3
        );
        DB::afterCommit(
            function () use ($processedOrder): void {
                try {
                    $this->inventoryAlertService
                        ->checkOrder(
                            $processedOrder->fresh([
                                'items',
                            ])
                        );
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        );
        return $processedOrder;
    }
    private function deductVariantStock(
        Order $order,
        int $variantId,
        ?int $productId,
        int $quantity,
        string $productTitle
    ): void {
        $variant = ProductVariant::query()
            ->lockForUpdate()
            ->find($variantId);
        if (!$variant) {
            throw new RuntimeException(
                'The selected variant for '
                    . ($productTitle ?: 'this product')
                    . ' no longer exists.'
            );
        }
        if (
            $productId !== null
            && (int) $variant->product_id
            !== $productId
        ) {
            throw new RuntimeException(
                'The selected product variant is invalid.'
            );
        }
        $availableStock = max(
            0,
            (int) $variant->stock
        );
        $stockBefore = $availableStock;
        if ($availableStock < $quantity) {
            throw new RuntimeException(
                $this->stockErrorMessage(
                    productTitle: $productTitle,
                    availableStock: $availableStock,
                    requestedQuantity: $quantity
                )
            );
        }
        $updated = ProductVariant::query()
            ->whereKey($variant->id)
            ->where(
                'stock',
                '>=',
                $quantity
            )
            ->decrement(
                'stock',
                $quantity
            );
        if ($updated !== 1) {
            throw new RuntimeException(
                'Stock changed while the order was being processed. Please try again.'
            );
        }
        $variant->refresh();
        $product = Product::findOrFail($variant->product_id);
        $this->inventoryHistoryService->recordOrderDeduction(
            product: $product,
            variant: $variant,
            stockBefore: $stockBefore,
            stockAfter: (int) $variant->stock,
            order: $order
        );
        $updatedProduct = Product::query()
            ->whereKey($variant->product_id)
            ->update([
                'purchase_count' => DB::raw(
                    'COALESCE(purchase_count, 0) + '
                        . $quantity
                ),
            ]);
        if ($updatedProduct !== 1) {
            throw new RuntimeException(
                'The parent product for the selected variant no longer exists.'
            );
        }
    }
    private function deductProductStock(
        Order $order,
        int $productId,
        int $quantity,
        string $productTitle
    ): void {
        $product = Product::query()
            ->lockForUpdate()
            ->find($productId);
        if (!$product) {
            throw new RuntimeException(
                ($productTitle ?: 'The product')
                    . ' no longer exists.'
            );
        }
        $availableStock = max(
            0,
            (int) $product->stock
        );
        $stockBefore = $availableStock;
        if ($availableStock < $quantity) {
            throw new RuntimeException(
                $this->stockErrorMessage(
                    productTitle: $productTitle,
                    availableStock: $availableStock,
                    requestedQuantity: $quantity
                )
            );
        }
        $updated = Product::query()
            ->whereKey($product->id)
            ->where(
                'stock',
                '>=',
                $quantity
            )
            ->update([
                'stock' => DB::raw(
                    'stock - ' . $quantity
                ),
                'purchase_count' => DB::raw(
                    'COALESCE(purchase_count, 0) + '
                        . $quantity
                ),
            ]);
        if ($updated !== 1) {
            throw new RuntimeException(
                'Stock changed while the order was being processed. Please try again.'
            );
        }
        $product->refresh();
        $this->inventoryHistoryService->recordOrderDeduction(
            product: $product,
            variant: null,
            stockBefore: $stockBefore,
            stockAfter: (int) $product->stock,
            order: $order
        );
    }
    public function restoreForOrder(Order $order, string $reason = 'refunded'): Order
    {
        if (!in_array($reason, ['cancelled', 'refunded'], true)) {
            throw new RuntimeException('Invalid inventory restoration reason.');
        }
        $processedOrder = DB::transaction(function () use ($order, $reason): Order {
            $lockedOrder = Order::withTrashed()->with('items')->lockForUpdate()->findOrFail($order->id);
            if (
                $lockedOrder->inventory_deducted_at === null
                || $lockedOrder->inventory_restored_at !== null
            ) {
                return $lockedOrder;
            }
            $alreadyRestored = InventoryHistory::query()
                ->where('order_id', $lockedOrder->id)
                ->whereIn('movement_type', [
                    InventoryHistory::TYPE_ORDER_CANCELLED,
                    InventoryHistory::TYPE_ORDER_REFUND,
                ])
                ->where('quantity_change', '>', 0)
                ->exists();
            if ($alreadyRestored) {
                $lockedOrder->forceFill([
                    'inventory_restored_at' => now(),
                ])->save();
                return $lockedOrder;
            }
            if ($lockedOrder->items->isEmpty()) {
                throw new RuntimeException('The order contains no items.');
            }
            foreach ($lockedOrder->items as $orderItem) {
                $quantity = max(1, (int) $orderItem->quantity);
                if ($orderItem->variant_id) {
                    $this->restoreVariantStock(
                        $lockedOrder,
                        (int) $orderItem->variant_id,
                        $orderItem->product_id ? (int) $orderItem->product_id : null,
                        $quantity,
                        $reason
                    );
                    continue;
                }
                if ($orderItem->product_id) {
                    $this->restoreProductStock(
                        $lockedOrder,
                        (int) $orderItem->product_id,
                        $quantity,
                        $reason
                    );
                    continue;
                }
                throw new RuntimeException(
                    'Inventory information is missing for ' .
                    ($orderItem->product_title ?: 'an order item') . '.'
                );
            }
            $lockedOrder->forceFill([
                'inventory_restored_at' => now(),
            ])->save();
            return $lockedOrder->fresh(['items']);
        }, 3);
        DB::afterCommit(function () use ($processedOrder): void {
            try {
                $this->inventoryAlertService->checkOrder($processedOrder->fresh(['items']));
            } catch (Throwable $exception) {
                report($exception);
            }
        });
        return $processedOrder;
    }
    private function restoreVariantStock(
        Order $order,
        int $variantId,
        ?int $productId,
        int $quantity,
        string $reason
    ): void {
        $variant = ProductVariant::query()->lockForUpdate()->find($variantId);
        if (!$variant) {
            throw new RuntimeException('The product variant required for inventory restoration no longer exists.');
        }
        if ($productId !== null && (int) $variant->product_id !== $productId) {
            throw new RuntimeException('The selected product variant is invalid.');
        }
        $product = Product::query()->lockForUpdate()->find($variant->product_id);
        if (!$product) {
            throw new RuntimeException('The parent product required for inventory restoration no longer exists.');
        }
        $stockBefore = max(0, (int) $variant->stock);
        $variant->increment('stock', $quantity);
        $variant->refresh();
        Product::query()->whereKey($product->id)->update([
            'purchase_count' => DB::raw(
                'GREATEST(COALESCE(purchase_count, 0) - ' . $quantity . ', 0)'
            ),
        ]);
        $this->recordOrderRestoration(
            $order, $product, $variant, $quantity, $stockBefore,
            (int) $variant->stock, $reason
        );
    }
    private function restoreProductStock(
        Order $order,
        int $productId,
        int $quantity,
        string $reason
    ): void {
        $product = Product::query()->lockForUpdate()->find($productId);
        if (!$product) {
            throw new RuntimeException('The product required for inventory restoration no longer exists.');
        }
        $stockBefore = max(0, (int) $product->stock);
        Product::query()->whereKey($product->id)->update([
            'stock' => DB::raw('stock + ' . $quantity),
            'purchase_count' => DB::raw(
                'GREATEST(COALESCE(purchase_count, 0) - ' . $quantity . ', 0)'
            ),
        ]);
        $product->refresh();
        $this->recordOrderRestoration(
            $order, $product, null, $quantity, $stockBefore,
            (int) $product->stock, $reason
        );
    }
    private function recordOrderRestoration(
        Order $order,
        Product $product,
        ?ProductVariant $variant,
        int $quantity,
        int $stockBefore,
        int $stockAfter,
        string $reason
    ): void {
        InventoryHistory::query()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'order_id' => $order->id,
            'user_id' => null,
            'admin_id' => auth('admin')->id(),
            'quantity_change' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'movement_type' => $reason === 'cancelled'
                ? InventoryHistory::TYPE_ORDER_CANCELLED
                : InventoryHistory::TYPE_ORDER_REFUND,
            'reason' => 'Inventory restored for ' . $reason . ' order #' . $order->order_number,
            'notes' => null,
            'reference_type' => Order::class,
            'reference_id' => (string) $order->id,
            'metadata' => [
                'source' => 'order_lifecycle',
                'order_number' => $order->order_number,
                'restoration_reason' => $reason,
            ],
        ]);
    }
    private function stockErrorMessage(
        string $productTitle,
        int $availableStock,
        int $requestedQuantity
    ): string {
        $title = trim($productTitle);
        if ($title === '') {
            $title = 'This product';
        }
        if ($availableStock === 0) {
            return $title
                . ' is currently out of stock.';
        }
        return $title
            . ' has only '
            . $availableStock
            . ' item'
            . ($availableStock === 1 ? '' : 's')
            . ' available, but '
            . $requestedQuantity
            . ' were requested.';
    }
}