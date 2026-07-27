<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'minimum_order_amount',
        'usage_limit',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => 'boolean',
    ];
}