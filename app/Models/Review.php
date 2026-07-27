<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'name',
        'email',
        'rating',
        'title',
        'review',
        'status',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'user_id' => 'integer',
        'rating' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    /**
     * Only approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where(
            'status',
            'approved'
        );
    }

    /**
     * Pending reviews.
     */
    public function scopePending($query)
    {
        return $query->where(
            'status',
            'pending'
        );
    }
}