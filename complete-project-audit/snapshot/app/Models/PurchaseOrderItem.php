<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'product_variant_id',
        'item_name',
        'sku',
        'variant_name',
        'quantity_ordered',
        'quantity_received',
        'unit_cost',
        'line_total',
        'stock_before',
        'reorder_point',
        'suggested_quantity',
        'notes',
    ];

    protected $casts = [
        'quantity_ordered' => 'integer',
        'quantity_received' => 'integer',
        'unit_cost' => 'decimal:2',
        'line_total' => 'decimal:2',
        'stock_before' => 'integer',
        'reorder_point' => 'integer',
        'suggested_quantity' => 'integer',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id'
        );
    }

    public function getRemainingQuantityAttribute(): int
    {
        return max(
            0,
            (int) $this->quantity_ordered
                - (int) $this->quantity_received
        );
    }

    public function getReceivingPercentageAttribute(): int
    {
        $ordered = (int) $this->quantity_ordered;

        if ($ordered <= 0) {
            return 0;
        }

        return min(
            100,
            (int) round(
                ((int) $this->quantity_received / $ordered) * 100
            )
        );
    }

    protected static function booted(): void
    {
        /*
         * Apply the chosen supplier's exact variant price first, then fall
         * back to its product-level price. This is a server-side safeguard:
         * the correct cost is still used even when JavaScript is unavailable.
         */
        static::creating(
            function (PurchaseOrderItem $item): void {
                if (!$item->purchase_order_id || !$item->product_id) {
                    return;
                }

                $supplierId = PurchaseOrder::query()
                    ->whereKey($item->purchase_order_id)
                    ->value('supplier_id');

                if (!$supplierId) {
                    return;
                }

                $query = SupplierProduct::query()
                    ->active()
                    ->where('supplier_id', $supplierId)
                    ->where('product_id', $item->product_id);

                $assignment = null;

                if ($item->product_variant_id) {
                    $assignment = (clone $query)
                        ->where(
                            'product_variant_id',
                            $item->product_variant_id
                        )
                        ->first();
                }

                $assignment ??= (clone $query)
                    ->whereNull('product_variant_id')
                    ->first();

                if (!$assignment) {
                    return;
                }

                $item->unit_cost = (float) $assignment->unit_cost;
                $item->quantity_ordered = max(
                    (int) $item->quantity_ordered,
                    (int) $assignment->minimum_order_quantity
                );

                if (filled($assignment->supplier_sku)) {
                    $item->sku = $assignment->supplier_sku;
                }

                /*
                 * The general saving hook runs before Laravel's creating
                 * event, so recalculate once more after applying the
                 * supplier cost and minimum quantity.
                 */
                $item->line_total = round(
                    (int) $item->quantity_ordered
                        * (float) $item->unit_cost,
                    2
                );
            }
        );

        static::saving(
            function (PurchaseOrderItem $item): void {
                $quantity = max(0, (int) $item->quantity_ordered);
                $unitCost = max(0, (float) $item->unit_cost);

                $item->line_total = round($quantity * $unitCost, 2);
            }
        );
    }
}
