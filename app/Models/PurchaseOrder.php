<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PurchaseOrder extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_ORDERED = 'ordered';

    public const STATUS_PARTIALLY_RECEIVED =
    'partially_received';

    public const STATUS_RECEIVED = 'received';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'reference',
        'supplier_id',
        'supplier_name',
        'supplier_email',
        'supplier_phone',
        'supplier_address',

        'status',

        'order_date',
        'expected_date',

        'ordered_at',
        'received_at',
        'cancelled_at',

        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',

        'currency',

        'notes',
        'internal_notes',

        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',

        'ordered_at' => 'datetime',
        'received_at' => 'datetime',
        'cancelled_at' => 'datetime',

        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model booting
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(
            function (PurchaseOrder $purchaseOrder): void {
                if (blank($purchaseOrder->reference)) {
                    $purchaseOrder->reference =
                        self::generateReference();
                }

                if (blank($purchaseOrder->status)) {
                    $purchaseOrder->status =
                        self::STATUS_DRAFT;
                }

                if (blank($purchaseOrder->currency)) {
                    $purchaseOrder->currency = 'GBP';
                }

                if (blank($purchaseOrder->order_date)) {
                    $purchaseOrder->order_date =
                        now()->toDateString();
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Purchase order items
    |--------------------------------------------------------------------------
    */

    public function items(): HasMany
    {
        return $this->hasMany(
            PurchaseOrderItem::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Administrator relationship
    |--------------------------------------------------------------------------
    */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status helpers
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status
            === self::STATUS_DRAFT;
    }

    public function isOrdered(): bool
    {
        return $this->status
            === self::STATUS_ORDERED;
    }

    public function isPartiallyReceived(): bool
    {
        return $this->status
            === self::STATUS_PARTIALLY_RECEIVED;
    }

    public function isReceived(): bool
    {
        return $this->status
            === self::STATUS_RECEIVED;
    }

    public function isCancelled(): bool
    {
        return $this->status
            === self::STATUS_CANCELLED;
    }

    /*
    |--------------------------------------------------------------------------
    | Status label
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT =>
            'Draft',

            self::STATUS_ORDERED =>
            'Ordered',

            self::STATUS_PARTIALLY_RECEIVED =>
            'Partially Received',

            self::STATUS_RECEIVED =>
            'Received',

            self::STATUS_CANCELLED =>
            'Cancelled',

            default =>
            Str::headline(
                (string) $this->status
            ),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Ordered and received quantities
    |--------------------------------------------------------------------------
    */

    public function getTotalOrderedQuantityAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return (int) $this->items
                ->sum('quantity_ordered');
        }

        return (int) $this->items()
            ->sum('quantity_ordered');
    }

    public function getTotalReceivedQuantityAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return (int) $this->items
                ->sum('quantity_received');
        }

        return (int) $this->items()
            ->sum('quantity_received');
    }

    public function getRemainingQuantityAttribute(): int
    {
        return max(
            0,
            $this->total_ordered_quantity
                - $this->total_received_quantity
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate purchase order reference
    |--------------------------------------------------------------------------
    */

    public static function generateReference(): string
    {
        $prefix = 'PO-' . now()->format('Ym') . '-';

        $lastReference = self::query()
            ->where(
                'reference',
                'like',
                $prefix . '%'
            )
            ->orderByDesc('id')
            ->value('reference');

        $nextNumber = 1;

        if (is_string($lastReference)) {
            $lastNumber = (int) Str::afterLast(
                $lastReference,
                '-'
            );

            $nextNumber = $lastNumber + 1;
        }

        return $prefix
            . str_pad(
                (string) $nextNumber,
                4,
                '0',
                STR_PAD_LEFT
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Recalculate purchase-order totals
    |--------------------------------------------------------------------------
    */

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items()
            ->sum('line_total');

        $taxAmount = (float) (
            $this->tax_amount ?? 0
        );

        $shippingAmount = (float) (
            $this->shipping_amount ?? 0
        );

        $discountAmount = (float) (
            $this->discount_amount ?? 0
        );

        $totalAmount =
            $subtotal
            + $taxAmount
            + $shippingAmount
            - $discountAmount;

        $this->forceFill([
            'subtotal' =>
            round($subtotal, 2),

            'total_amount' =>
            round(
                max(0, $totalAmount),
                2
            ),
        ])->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Update status from received quantities
    |--------------------------------------------------------------------------
    */

    public function refreshReceivingStatus(): void
    {
        if ($this->isCancelled()) {
            return;
        }

        $ordered = $this->total_ordered_quantity;

        $received = $this->total_received_quantity;

        if (
            $ordered > 0
            && $received >= $ordered
        ) {
            $this->forceFill([
                'status' =>
                self::STATUS_RECEIVED,

                'received_at' =>
                $this->received_at ?? now(),
            ])->save();

            return;
        }

        if ($received > 0) {
            $this->forceFill([
                'status' =>
                self::STATUS_PARTIALLY_RECEIVED,

                'received_at' =>
                null,
            ])->save();

            return;
        }

        /*
         * Do not automatically change a draft order to ordered.
         */
        if (!$this->isDraft()) {
            $this->forceFill([
                'status' =>
                self::STATUS_ORDERED,

                'received_at' =>
                null,
            ])->save();
        }
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            Supplier::class
        );
    }
}
