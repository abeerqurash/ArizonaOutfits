<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class OrderShipment extends Model
{
    use HasFactory;
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
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function trackingEvents(): HasMany
    {
        return $this->hasMany(ShipmentTrackingEvent::class, 'shipment_id');
    }
}