<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupplierReturn extends Model
{
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'reference',
        'purchase_order_id',
        'supplier_id',
        'status',
        'reason_category',
        'reason',
        'returned_at',
        'expected_credit',
        'actual_credit',
        'notes',
        'created_by',
        'completed_by',
        'completed_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
        'expected_credit' => 'decimal:2',
        'actual_credit' => 'decimal:2',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(
            function (SupplierReturn $supplierReturn): void {
                if (blank($supplierReturn->reference)) {
                    $supplierReturn->reference = 'SRT-'
                        . now()->format('Ymd')
                        . '-'
                        . Str::upper(Str::random(6));
                }

                if (blank($supplierReturn->status)) {
                    $supplierReturn->status = self::STATUS_SUBMITTED;
                }

                if (blank($supplierReturn->returned_at)) {
                    $supplierReturn->returned_at = now();
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

    public function items(): HasMany
    {
        return $this->hasMany(SupplierReturnItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getStatusLabelAttribute(): string
    {
        return Str::headline((string) $this->status);
    }
}
