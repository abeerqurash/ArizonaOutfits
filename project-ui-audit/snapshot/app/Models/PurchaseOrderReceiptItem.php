<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderReceiptItem extends Model
{
    protected $fillable = [
        'purchase_order_receipt_id',
        'purchase_order_item_id',
        'product_id',
        'product_variant_id',
        'quantity_received',
        'quantity_corrected',
        'stock_before',
        'stock_after',
    ];

    protected $casts = [
        'quantity_received' => 'integer',
        'quantity_corrected' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(
            PurchaseOrderReceipt::class,
            'purchase_order_receipt_id'
        );
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

    public function getCorrectableQuantityAttribute(): int
    {
        return max(
            0,
            (int) $this->quantity_received
                - (int) $this->quantity_corrected
        );
    }
}
