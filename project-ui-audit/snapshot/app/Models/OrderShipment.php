<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderShipment extends Model
{
    use HasFactory;

    /**
     * Shipment/courier information is internal operational data.
     *
     * Important:
     * orders.tracking_number remains the permanent ArizonaOutfits
     * customer-facing tracking code (TRK-...).
     */
    protected $fillable = [
        'order_id',
        'courier_provider',
        'courier_tracking_number',
        'external_shipment_id',
        'external_reference',
        'tracking_mode',
        'provider_status',
        'normalized_status',
        'tracking_url',
        'last_event_at',
        'metadata',
    ];

    protected $casts = [
        'last_event_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * The ArizonaOutfits order that owns this shipment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Internal courier tracking event history for this shipment.
     *
     * Raw provider events remain internal/admin data. Customer-facing
     * tracking continues to use ArizonaOutfits-normalized order statuses.
     */
    public function trackingEvents(): HasMany
    {
        return $this->hasMany(ShipmentTrackingEvent::class, 'shipment_id');
    }
}
