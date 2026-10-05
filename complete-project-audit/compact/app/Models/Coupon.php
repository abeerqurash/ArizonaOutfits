<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'minimum_order_amount',
        'maximum_discount',
        'usage_limit',
        'per_user_usage_limit',
        'used_count',
        'target_type',
        'product_ids',
        'category_ids',
        'variant_ids',
        'include_child_categories',
        'event_name',
        'start_date',
        'end_date',
        'status',
    ];
    protected $casts = [
        'value' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'usage_limit' => 'integer',
        'per_user_usage_limit' => 'integer',
        'used_count' => 'integer',
        'product_ids' => 'array',
        'category_ids' => 'array',
        'variant_ids' => 'array',
        'include_child_categories' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => 'boolean',
    ];
    public function getRuntimeStatusAttribute(): string
    {
        if (!$this->status) {
            return 'inactive';
        }
        $now = Carbon::now();
        if ($this->start_date && $now->lt($this->start_date)) {
            return 'scheduled';
        }
        if ($this->end_date && $now->gt($this->end_date)) {
            return 'expired';
        }
        if (
            $this->usage_limit !== null
            && (int) $this->used_count >= (int) $this->usage_limit
        ) {
            return 'exhausted';
        }
        return 'active';
    }
    public function getRuntimeStatusLabelAttribute(): string
    {
        return match ($this->runtime_status) {
            'active' => 'Active',
            'scheduled' => 'Scheduled',
            'expired' => 'Expired',
            'exhausted' => 'Usage Limit Reached',
            default => 'Inactive',
        };
    }
}