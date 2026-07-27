<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderNote extends Model
{
    use HasFactory;

    /**
     * Mass-assignable attributes.
     */
    protected $fillable = [
        'order_id',
        'user_id',
        'note',
        'is_customer_visible',
    ];

    /**
     * Attribute casts.
     */
    protected function casts(): array
    {
        return [
            'is_customer_visible' => 'boolean',
        ];
    }

    /**
     * Order associated with the note.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Admin or user who created the note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Return notes from newest to oldest.
     */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Return internal notes only.
     */
    public function scopeInternal(Builder $query): Builder
    {
        return $query->where(
            'is_customer_visible',
            false
        );
    }

    /**
     * Return customer-visible notes only.
     */
    public function scopeCustomerVisible(
        Builder $query
    ): Builder {
        return $query->where(
            'is_customer_visible',
            true
        );
    }

    /**
     * Human-readable author name.
     */
    public function getAuthorNameAttribute(): string
    {
        return $this->user?->name ?: 'System';
    }
}