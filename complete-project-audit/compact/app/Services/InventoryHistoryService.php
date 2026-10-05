<?php
namespace App\Services;
use App\Models\InventoryHistory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Contracts\Auth\Authenticatable;
class InventoryHistoryService
{
    public function record(
        Product $product,
        ?ProductVariant $variant,
        int $stockBefore,
        int $stockAfter,
        string $movementType,
        ?Order $order = null,
        ?Authenticatable $user = null,
        ?string $reason = null,
        ?string $notes = null,
        array $metadata = []
    ): InventoryHistory {
        return InventoryHistory::create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'order_id' => $order?->id,
            'user_id' => $user instanceof User ? $user->getAuthIdentifier() : null,
            'admin_id' => $user instanceof Admin ? $user->getAuthIdentifier() : null,
            'quantity_change' => $stockAfter - $stockBefore,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'movement_type' => $movementType,
            'reason' => $reason,
            'notes' => $notes,
            'reference_type' => $order
                ? Order::class
                : null,
            'reference_id' => $order
                ? (string) $order->id
                : null,
            'metadata' => $metadata,
        ]);
    }
    public function recordOrderDeduction(
        Product $product,
        ?ProductVariant $variant,
        int $stockBefore,
        int $stockAfter,
        Order $order
    ): InventoryHistory {
        return $this->record(
            product: $product,
            variant: $variant,
            stockBefore: $stockBefore,
            stockAfter: $stockAfter,
            movementType: InventoryHistory::TYPE_ORDER_DEDUCTION,
            order: $order,
            reason: 'Inventory deducted for order #' . ($order->order_number ?? $order->id),
            metadata: [
                'source' => 'checkout',
                'payment_status' => $order->payment_status,
                'order_number' => $order->order_number,
            ]
        );
    }
    public function recordRestock(
        Product $product,
        ?ProductVariant $variant,
        int $stockBefore,
        int $stockAfter,
        ?Authenticatable $user = null,
        ?string $notes = null
    ): InventoryHistory {
        return $this->record(
            product: $product,
            variant: $variant,
            stockBefore: $stockBefore,
            stockAfter: $stockAfter,
            movementType: InventoryHistory::TYPE_RESTOCK,
            user: $user,
            reason: 'Product restocked',
            notes: $notes,
            metadata: [
                'source' => 'admin',
            ]
        );
    }
    public function recordManualAdjustment(
        Product $product,
        ?ProductVariant $variant,
        int $stockBefore,
        int $stockAfter,
        Authenticatable $user,
        ?string $notes = null
    ): InventoryHistory {
        return $this->record(
            product: $product,
            variant: $variant,
            stockBefore: $stockBefore,
            stockAfter: $stockAfter,
            movementType: InventoryHistory::TYPE_MANUAL_ADJUSTMENT,
            user: $user,
            reason: 'Manual inventory adjustment',
            notes: $notes,
            metadata: [
                'source' => 'admin',
            ]
        );
    }
    public function recordRefund(
        Product $product,
        ?ProductVariant $variant,
        int $stockBefore,
        int $stockAfter,
        Order $order
    ): InventoryHistory {
        return $this->record(
            product: $product,
            variant: $variant,
            stockBefore: $stockBefore,
            stockAfter: $stockAfter,
            movementType: InventoryHistory::TYPE_ORDER_REFUND,
            order: $order,
            reason: 'Inventory restored after refund',
            metadata: [
                'source' => 'refund',
                'order_number' => $order->order_number,
            ]
        );
    }
    public function recordCancellation(
        Product $product,
        ?ProductVariant $variant,
        int $stockBefore,
        int $stockAfter,
        Order $order
    ): InventoryHistory {
        return $this->record(
            product: $product,
            variant: $variant,
            stockBefore: $stockBefore,
            stockAfter: $stockAfter,
            movementType: InventoryHistory::TYPE_ORDER_CANCELLED,
            order: $order,
            reason: 'Inventory restored after order cancellation',
            metadata: [
                'source' => 'cancellation',
                'order_number' => $order->order_number,
            ]
        );
    }
    public function recordInitialStock(
        Product $product,
        ?ProductVariant $variant,
        int $stock
    ): InventoryHistory {
        return $this->record(
            product: $product,
            variant: $variant,
            stockBefore: 0,
            stockAfter: $stock,
            movementType: InventoryHistory::TYPE_INITIAL_STOCK,
            reason: 'Initial stock created',
            metadata: [
                'source' => 'product_creation',
            ]
        );
    }
}