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
        'used_count',
        'start_date',
        'end_date',
        'status',
    ];
}
