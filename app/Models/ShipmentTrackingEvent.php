<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentTrackingEvent extends Model
{
    use HasFactory;

    /**
     * Internal courier tracking event data.
     *
     * Raw provider information stored here is intended for
     * admin/internal processing. Customer-facing tracking should
     * continue to use ArizonaOutfits-normalized order statuses.
     */
    protected $fillable = [
        'shipment_id',
        'provider',
        'external_event_id',
        'provider_status',
        'normalized_status',
        'description',
        'location',
        'event_time',
        'received_at',
        'payload',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'event_time' => 'datetime',
        'received_at' => 'datetime',
        'payload' => 'array',
    ];

    /**
     * Shipment that owns this tracking event.
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(OrderShipment::class);
    }
}
