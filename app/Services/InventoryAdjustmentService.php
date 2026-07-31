<?php

namespace App\Services;

use App\Models\InventoryHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryAdjustmentService
{
    public function __construct(
        private readonly InventoryHistoryService $historyService,
        private readonly InventoryAlertService $alertService,
    ) {}

    /**
     * Adjust stock for a simple product.
     */
    public function adjustProduct(
        Product $product,
        string $type,
        int $quantity,
        string $reason,
        ?string $notes,
        User $user,
    ): Product {

        return DB::transaction(function () use (
            $product,
            $type,
            $quantity,
            $reason,
            $notes,
            $user
        ) {

            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            $before = (int) $product->stock;

            $after = match ($type) {
                'add' => $before + $quantity,
                'remove' => $before - $quantity,
                'set' => $quantity,

                default => throw new RuntimeException(
                    'Invalid adjustment type.'
                ),
            };

            $product->update([
                'stock' => $after,
            ]);

            $movement = match ($type) {

                'add' => InventoryHistory::TYPE_RESTOCK,

                'remove' => InventoryHistory::TYPE_MANUAL_ADJUSTMENT,

                'set' => InventoryHistory::TYPE_INVENTORY_CORRECTION,
            };

            $this->historyService->record(

                product: $product,

                variant: null,

                stockBefore: $before,

                stockAfter: $after,

                movementType: $movement,

                user: $user,

                reason: $reason,

                notes: $notes,

                metadata: [
                    'source' => 'admin',
                ],

            );

            DB::afterCommit(function () use ($product) {

                $this->alertService->checkProduct($product);
            });

            return $product->fresh();
        });
    }

    /**
     * Adjust stock for a product variant.
     */
    public function adjustVariant(
        ProductVariant $variant,
        string $type,
        int $quantity,
        string $reason,
        ?string $notes,
        User $user,
    ): ProductVariant {

        return DB::transaction(function () use (
            $variant,
            $type,
            $quantity,
            $reason,
            $notes,
            $user
        ) {

            $variant = ProductVariant::query()
                ->lockForUpdate()
                ->findOrFail($variant->id);

            $before = (int) $variant->stock;

            $after = match ($type) {
                'add' => $before + $quantity,
                'remove' => $before - $quantity,
                'set' => $quantity,

                default => throw new RuntimeException(
                    'Invalid adjustment type.'
                ),
            };

            $variant->update([
                'stock' => $after,
            ]);

            $movement = match ($type) {

                'add' => InventoryHistory::TYPE_RESTOCK,

                'remove' => InventoryHistory::TYPE_MANUAL_ADJUSTMENT,

                'set' => InventoryHistory::TYPE_INVENTORY_CORRECTION,
            };

            $this->historyService->record(

                product: $variant->product,

                variant: $variant,

                stockBefore: $before,

                stockAfter: $after,

                movementType: $movement,

                user: $user,

                reason: $reason,

                notes: $notes,

                metadata: [
                    'source' => 'admin',
                ],

            );

            DB::afterCommit(function () use ($variant) {

                $this->alertService
                    ->checkVariant($variant);
            });

            return $variant->fresh();
        });
    }
}
