<?php

namespace App\Services;

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

    /**
     * Deduct inventory for an order.
     *
     * This operation is idempotent. Calling it multiple
     * times for the same order cannot deduct stock twice.
     */
    public function deductForOrder(
        Order $order
    ): Order {
        $processedOrder = DB::transaction(
            function () use ($order): Order {
                /*
                 * Lock the order so multiple payment callbacks
                 * cannot deduct its inventory simultaneously.
                 */
                $lockedOrder = Order::query()
                    ->with('items')
                    ->lockForUpdate()
                    ->findOrFail($order->id);

                /*
                 * Stock was already deducted for this order.
                 */
                if (
                    $lockedOrder->inventory_deducted_at
                    !== null
                ) {
                    return $lockedOrder;
                }

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

                    /*
                     * Product with a selected variant.
                     */
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

                    /*
                     * Normal product without a variant.
                     */
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

                /*
                 * Only mark inventory as deducted after every
                 * order item has been processed successfully.
                 */
                $lockedOrder->update([
                    'inventory_deducted_at' => now(),
                ]);

                return $lockedOrder->fresh([
                    'items',
                ]);
            },
            3
        );

        /*
         * Schedule alerts after the surrounding database
         * transaction commits.
         *
         * This is important because this service may be called
         * from CheckoutController or StripeWebhookController,
         * which may already have an outer transaction.
         */
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

    /**
     * Deduct stock from one product variant.
     */
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

        /*
         * The conditional decrement prevents stock from
         * becoming negative during concurrent orders.
         */
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

        /*
         * Variant inventory is separate from parent product
         * inventory. Only the parent's purchase count changes.
         */
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

    /**
     * Deduct stock from a normal product.
     */
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

    /**
     * Build a readable insufficient-stock error message.
     */
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
