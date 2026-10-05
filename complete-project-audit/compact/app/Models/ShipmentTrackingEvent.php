<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ShipmentTrackingEvent extends Model
{
    use HasFactory;
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
    protected $casts = [
        'event_time' => 'datetime',
        'received_at' => 'datetime',
        'payload' => 'array',
    ];
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(OrderShipment::class);
    }
}