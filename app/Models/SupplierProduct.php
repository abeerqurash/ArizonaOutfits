<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProduct extends Model
{
    protected $fillable = [
        'supplier_id',
        'product_id',
        'product_variant_id',
        'supplier_sku',
        'unit_cost',
        'minimum_order_quantity',
        'lead_time_days',
        'is_preferred',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'minimum_order_quantity' => 'integer',
        'lead_time_days' => 'integer',
        'is_preferred' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(
            function (SupplierProduct $supplierProduct): void {
                $supplierProduct->variant_key =
                    (int) ($supplierProduct->product_variant_id ?? 0);

                $supplierProduct->minimum_order_quantity = max(
                    1,
                    (int) $supplierProduct->minimum_order_quantity
                );

                if ($supplierProduct->is_preferred) {
                    $supplierProduct->is_active = true;
                }
            }
        );

        static::saved(
            function (SupplierProduct $supplierProduct): void {
                if (!$supplierProduct->is_preferred) {
                    return;
                }

                /*
                 * Only one supplier can be preferred for the same product
                 * or exact variant. Query Builder update avoids model-event
                 * recursion while clearing the previous preferred record.
                 */
                self::query()
                    ->where('product_id', $supplierProduct->product_id)
                    ->where('variant_key', $supplierProduct->variant_key)
                    ->where('id', '!=', $supplierProduct->getKey())
                    ->update(['is_preferred' => false]);
            }
        );
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getItemLabelAttribute(): string
    {
        $productTitle = $this->product?->title
            ?: 'Product #' . $this->product_id;

        if (!$this->variant) {
            return $productTitle . ' — Default price';
        }

        return $productTitle
            . ' — '
            . ($this->variant->sku ?: 'Variant #' . $this->variant->id);
    }
}
