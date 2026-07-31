<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryHistory extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Movement type constants
    |--------------------------------------------------------------------------
    */

    public const TYPE_ORDER_DEDUCTION = 'order_deduction';

    public const TYPE_RESTOCK = 'restock';

    public const TYPE_MANUAL_ADJUSTMENT = 'manual_adjustment';

    public const TYPE_ORDER_CANCELLED = 'order_cancelled';

    public const TYPE_ORDER_REFUND = 'order_refund';

    public const TYPE_INVENTORY_CORRECTION = 'inventory_correction';

    public const TYPE_VARIANT_ADJUSTMENT = 'variant_adjustment';

    public const TYPE_INITIAL_STOCK = 'initial_stock';

    /*
    |--------------------------------------------------------------------------
    | Mass assignable fields
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'order_id',
        'user_id',
        'quantity_change',
        'stock_before',
        'stock_after',
        'movement_type',
        'reason',
        'notes',
        'reference_type',
        'reference_id',
        'metadata',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attribute casting
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'product_variant_id' => 'integer',
            'order_id' => 'integer',
            'user_id' => 'integer',
            'quantity_change' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForProduct(
        Builder $query,
        int $productId
    ): Builder {
        return $query->where('product_id', $productId);
    }

    public function scopeForVariant(
        Builder $query,
        int $variantId
    ): Builder {
        return $query->where(
            'product_variant_id',
            $variantId
        );
    }

    public function scopeForOrder(
        Builder $query,
        int $orderId
    ): Builder {
        return $query->where('order_id', $orderId);
    }

    public function scopeOfType(
        Builder $query,
        string $movementType
    ): Builder {
        return $query->where(
            'movement_type',
            $movementType
        );
    }

    public function scopeStockAdded(Builder $query): Builder
    {
        return $query->where('quantity_change', '>', 0);
    }

    public function scopeStockRemoved(Builder $query): Builder
    {
        return $query->where('quantity_change', '<', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Movement helpers
    |--------------------------------------------------------------------------
    */

    public function isStockAddition(): bool
    {
        return $this->quantity_change > 0;
    }

    public function isStockDeduction(): bool
    {
        return $this->quantity_change < 0;
    }

    public function isNoChange(): bool
    {
        return $this->quantity_change === 0;
    }

    public function isOrderMovement(): bool
    {
        return in_array($this->movement_type, [
            self::TYPE_ORDER_DEDUCTION,
            self::TYPE_ORDER_CANCELLED,
            self::TYPE_ORDER_REFUND,
        ], true);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getMovementLabelAttribute(): string
    {
        return match ($this->movement_type) {
            self::TYPE_ORDER_DEDUCTION => 'Order Deduction',
            self::TYPE_RESTOCK => 'Restock',
            self::TYPE_MANUAL_ADJUSTMENT => 'Manual Adjustment',
            self::TYPE_ORDER_CANCELLED => 'Order Cancellation',
            self::TYPE_ORDER_REFUND => 'Order Refund',
            self::TYPE_INVENTORY_CORRECTION => 'Inventory Correction',
            self::TYPE_VARIANT_ADJUSTMENT => 'Variant Adjustment',
            self::TYPE_INITIAL_STOCK => 'Initial Stock',
            default => ucwords(
                str_replace('_', ' ', $this->movement_type)
            ),
        };
    }

    public function getFormattedQuantityChangeAttribute(): string
    {
        if ($this->quantity_change > 0) {
            return '+' . number_format($this->quantity_change);
        }

        return number_format($this->quantity_change);
    }

    public function getDirectionAttribute(): string
    {
        if ($this->quantity_change > 0) {
            return 'increase';
        }

        if ($this->quantity_change < 0) {
            return 'decrease';
        }

        return 'unchanged';
    }

    public function getItemNameAttribute(): string
    {
        $productTitle = $this->variant?->product?->title
            ?? $this->product?->title
            ?? 'Deleted product';

        $variantDescription = $this->variantDescription();

        if ($variantDescription === '') {
            return $productTitle;
        }

        return $productTitle . ' — ' . $variantDescription;
    }

    public function getPerformedByAttribute(): string
    {
        return $this->user?->name ?? 'System';
    }

    public function getOrderReferenceAttribute(): ?string
    {
        if (!$this->order) {
            return null;
        }

        return $this->order->order_number
            ?? '#' . $this->order->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Variant display helper
    |--------------------------------------------------------------------------
    */

    public function variantDescription(): string
    {
        if (!$this->variant) {
            return '';
        }

        $parts = [];

        $possibleFields = [
            'title',
            'name',
            'sku',
            'size',
            'color',
        ];

        foreach ($possibleFields as $field) {
            $value = $this->variant->{$field} ?? null;

            if (
                is_string($value)
                && trim($value) !== ''
                && !in_array(trim($value), $parts, true)
            ) {
                $parts[] = trim($value);
            }
        }

        if (!empty($parts)) {
            return implode(' / ', $parts);
        }

        return 'Variant #' . $this->variant->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Available movement types
    |--------------------------------------------------------------------------
    */

    public static function movementTypes(): array
    {
        return [
            self::TYPE_ORDER_DEDUCTION => 'Order Deduction',
            self::TYPE_RESTOCK => 'Restock',
            self::TYPE_MANUAL_ADJUSTMENT => 'Manual Adjustment',
            self::TYPE_ORDER_CANCELLED => 'Order Cancellation',
            self::TYPE_ORDER_REFUND => 'Order Refund',
            self::TYPE_INVENTORY_CORRECTION => 'Inventory Correction',
            self::TYPE_VARIANT_ADJUSTMENT => 'Variant Adjustment',
            self::TYPE_INITIAL_STOCK => 'Initial Stock',
        ];
    }
}