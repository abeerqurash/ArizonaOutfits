<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierReturnItem extends Model
{
    protected $fillable = [
        'supplier_return_id',
        'purchase_order_item_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_cost',
        'line_total',
        'stock_before',
        'stock_after',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'line_total' => 'decimal:2',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(
            function (SupplierReturnItem $item): void {
                $item->line_total = round(
                    max(0, (int) $item->quantity)
                        * max(0, (float) $item->unit_cost),
                    2
                );
            }
        );
    }

    public function supplierReturn(): BelongsTo
    {
        return $this->belongsTo(SupplierReturn::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
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
}
