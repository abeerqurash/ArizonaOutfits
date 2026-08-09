<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PurchaseOrderReceipt extends Model
{
    protected $fillable = [
        'reference',
        'purchase_order_id',
        'supplier_id',
        'received_at',
        'notes',
        'received_by',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(
            function (PurchaseOrderReceipt $receipt): void {
                if (blank($receipt->reference)) {
                    $receipt->reference = 'RCV-'
                        . now()->format('Ymd')
                        . '-'
                        . Str::upper(Str::random(6));
                }

                if (blank($receipt->received_at)) {
                    $receipt->received_at = now();
                }
            }
        );
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderReceiptItem::class);
    }

    public function getTotalReceivedAttribute(): int
    {
        return (int) ($this->relationLoaded('items')
            ? $this->items->sum('quantity_received')
            : $this->items()->sum('quantity_received'));
    }

    public function getTotalCorrectedAttribute(): int
    {
        return (int) ($this->relationLoaded('items')
            ? $this->items->sum('quantity_corrected')
            : $this->items()->sum('quantity_corrected'));
    }
}
