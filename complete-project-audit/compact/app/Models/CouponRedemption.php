<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CouponRedemption extends Model
{
    protected $fillable = [
        'coupon_id',
        'order_id',
        'user_id',
        'coupon_code',
        'discount_amount',
        'redeemed_at',
    ];
    protected $casts = [
        'discount_amount' => 'decimal:2',
        'redeemed_at' => 'datetime',
    ];
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}