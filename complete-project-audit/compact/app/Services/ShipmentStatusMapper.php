<?php
namespace App\Services;
class ShipmentStatusMapper
{
    private const NORMALIZED_STATUSES = [
        'processing',
        'packed',
        'shipped',
        'out_for_delivery',
        'delivered',
        'cancelled',
    ];
    private const STATUS_MAP = [
        // Shipment created / awaiting physical movement.
        'booked' => 'processing',
        'booking_created' => 'processing',
        'created' => 'processing',
        'shipment_created' => 'processing',
        'manifested' => 'processing',
        'ready_for_pickup' => 'processing',
        'awaiting_pickup' => 'processing',
        // Packed / prepared by fulfilment.
        'packed' => 'packed',
        'ready_to_ship' => 'packed',
        'ready_for_dispatch' => 'packed',
        // Courier has possession / parcel is moving.
        'picked_up' => 'shipped',
        'pickup_complete' => 'shipped',
        'collected' => 'shipped',
        'accepted_by_courier' => 'shipped',
        'dispatched' => 'shipped',
        'shipped' => 'shipped',
        'in_transit' => 'shipped',
        'transit' => 'shipped',
        'at_hub' => 'shipped',
        'arrived_at_hub' => 'shipped',
        'departed_hub' => 'shipped',
        // Final-mile delivery.
        'out_for_delivery' => 'out_for_delivery',
        'with_courier' => 'out_for_delivery',
        'with_rider' => 'out_for_delivery',
        'on_vehicle_for_delivery' => 'out_for_delivery',
        // Successfully delivered.
        'delivered' => 'delivered',
        'delivery_complete' => 'delivered',
        'proof_of_delivery' => 'delivered',
        // Explicit courier cancellation.
        'cancelled' => 'cancelled',
        'canceled' => 'cancelled',
        'shipment_cancelled' => 'cancelled',
        'shipment_canceled' => 'cancelled',
    ];
    public function map(?string $providerStatus): ?string
    {
        $key = $this->normalizeKey($providerStatus);
        if ($key === null) {
            return null;
        }
        $mapped = self::STATUS_MAP[$key] ?? null;
        if (
            $mapped === null
            || !in_array($mapped, self::NORMALIZED_STATUSES, true)
        ) {
            return null;
        }
        return $mapped;
    }
    public function canMap(?string $providerStatus): bool
    {
        return $this->map($providerStatus) !== null;
    }
    public function allowedNormalizedStatuses(): array
    {
        return self::NORMALIZED_STATUSES;
    }
    private function normalizeKey(?string $status): ?string
    {
        if ($status === null) {
            return null;
        }
        $status = trim(mb_strtolower($status));
        if ($status === '') {
            return null;
        }
        $status = preg_replace('/[^a-z0-9]+/u', '_', $status);
        $status = trim((string) $status, '_');
        return $status === '' ? null : $status;
    }
}