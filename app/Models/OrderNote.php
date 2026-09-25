<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'admin_id',
        'note',
        'is_customer_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_customer_visible' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Current administrator author.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Historical user author retained during the transition.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function scopeInternal(Builder $query): Builder
    {
        return $query->where('is_customer_visible', false);
    }

    public function scopeCustomerVisible(Builder $query): Builder
    {
        return $query->where('is_customer_visible', true);
    }

    public function getAuthorNameAttribute(): string
    {
        return $this->admin?->name
            ?: $this->user?->name
            ?: 'System';
    }
}
