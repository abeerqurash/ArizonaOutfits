<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierRating extends Model
{
    protected $fillable = [
        'supplier_id',
        'purchase_order_id',
        'quality_rating',
        'delivery_rating',
        'communication_rating',
        'pricing_rating',
        'overall_rating',
        'title',
        'review',
        'would_recommend',
        'rated_by',
        'rated_by_admin_id',
        'rated_at',
    ];

    protected $casts = [
        'quality_rating' => 'integer',
        'delivery_rating' => 'integer',
        'communication_rating' => 'integer',
        'pricing_rating' => 'integer',
        'overall_rating' => 'decimal:2',
        'would_recommend' => 'boolean',
        'rated_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            Supplier::class
        );
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(
            PurchaseOrder::class
        );
    }

    public function ratedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rated_by');
    }

    public function ratedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'rated_by_admin_id');
    }

    public function getRatedByActorAttribute(): Admin|User|null
    {
        return $this->ratedByAdmin ?? $this->ratedBy;
    }

}
