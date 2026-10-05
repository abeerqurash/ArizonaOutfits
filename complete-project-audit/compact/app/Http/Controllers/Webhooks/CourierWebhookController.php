<?php
namespace App\Http\Controllers\Webhooks;
use App\Http\Controllers\Controller;
use App\Models\OrderShipment;
use App\Services\ShipmentOrderStatusSyncService;
use App\Services\ShipmentTrackingEventService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
class CourierWebhookController extends Controller
{
    public function __construct(
        private readonly ShipmentTrackingEventService $trackingEventService,
        private readonly ShipmentOrderStatusSyncService $orderStatusSyncService
    ) {
    }
    public function processVerifiedEvent(
        string $provider,
        array $eventData
    ): array {
        $provider = trim($provider);
        if ($provider === '') {
            throw new InvalidArgumentException(
                'Courier provider is required.'
            );
        }
        $courierTrackingNumber = $this->nullableString(
            $eventData['courier_tracking_number'] ?? null
        );
        $externalShipmentId = $this->nullableString(
            $eventData['external_shipment_id'] ?? null
        );
        if (
            $courierTrackingNumber === null
            && $externalShipmentId === null
        ) {
            throw new InvalidArgumentException(
                'Courier tracking number or external shipment ID is required.'
            );
        }
        $providerStatus = $this->nullableString(
            $eventData['provider_status'] ?? null
        );
        if ($providerStatus === null) {
            throw new InvalidArgumentException(
                'Courier provider status is required.'
            );
        }
        $shipmentQuery = OrderShipment::query()
            ->whereRaw(
                'LOWER(TRIM(courier_provider)) = ?',
                [mb_strtolower($provider)]
            );
        if ($courierTrackingNumber !== null) {
            $shipmentQuery->where(
                'courier_tracking_number',
                $courierTrackingNumber
            );
        }
        if ($externalShipmentId !== null) {
            $shipmentQuery->where(
                'external_shipment_id',
                $externalShipmentId
            );
        }
        $shipments = $shipmentQuery
            ->limit(2)
            ->get();
        if ($shipments->isEmpty()) {
            throw new InvalidArgumentException(
                'Matching shipment could not be found.'
            );
        }
        if ($shipments->count() !== 1) {
            throw new InvalidArgumentException(
                'Shipment lookup is ambiguous.'
            );
        }
        $shipment = $shipments->first();
        $recordData = [
            'provider' => $provider,
            'external_event_id' =>
                $eventData['external_event_id'] ?? null,
            'provider_status' => $providerStatus,
            'normalized_status' =>
                $eventData['normalized_status'] ?? null,
            'description' =>
                $eventData['description'] ?? null,
            'location' =>
                $eventData['location'] ?? null,
            'event_time' =>
                $eventData['event_time'] ?? null,
            'received_at' =>
                $eventData['received_at'] ?? now(),
            'payload' =>
                $eventData['payload'] ?? $eventData,
        ];
        $event = $this->trackingEventService->record(
            $shipment,
            $recordData
        );
        $shipment->refresh();
        $shipment->loadMissing('order');
        $eventEffectiveTime = $event->event_time
            ?? $event->received_at;
        $eventTimeMatchesSnapshot =
            $eventEffectiveTime !== null
            && $shipment->last_event_at !== null
            && $this->toDatabaseSecondPrecision(
                $eventEffectiveTime
            )->equalTo(
                $this->toDatabaseSecondPrecision(
                    $shipment->last_event_at
                )
            );
        $eventStatusMatchesSnapshot =
            $event->normalized_status !== null
            && $shipment->normalized_status !== null
            && $event->normalized_status ===
                $shipment->normalized_status;
        $eventOwnsCurrentTrustedSnapshot =
            $eventTimeMatchesSnapshot
            && $eventStatusMatchesSnapshot;
        if ($event->normalized_status === null) {
            $syncResult = $this->blockedSyncResult(
                $shipment,
                'event_has_no_trusted_normalized_status'
            );
        } elseif (!$eventOwnsCurrentTrustedSnapshot) {
            $syncResult = $this->blockedSyncResult(
                $shipment,
                'event_is_not_current_trusted_snapshot'
            );
        } else {
            $syncResult = $this->orderStatusSyncService->sync(
                $shipment
            );
        }
        return [
            'accepted' => true,
            'shipment_id' => $shipment->id,
            'order_id' => $shipment->order_id,
            'event_id' => $event->id,
            'provider' => $provider,
            'provider_status' => $event->provider_status,
            'normalized_status' => $event->normalized_status,
            'shipment_normalized_status' =>
                $shipment->normalized_status,
            'order_sync' => $syncResult,
        ];
    }
    public function handle(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' =>
                'Courier webhook provider authentication is not configured.',
        ], 503);
    }
    private function blockedSyncResult(
        OrderShipment $shipment,
        string $reason
    ): array {
        return [
            'allowed' => false,
            'updated' => false,
            'reason' => $reason,
            'old_status' => $shipment->order?->order_status,
            'new_status' => null,
            'order_id' => $shipment->order_id,
            'order_number' => $shipment->order?->order_number,
        ];
    }
    private function toDatabaseSecondPrecision(
        CarbonInterface $value
    ): CarbonInterface {
        return $value->copy()->setMicrosecond(0);
    }
    private function nullableDateTime(
        mixed $value
    ): ?CarbonInterface {
        if ($value === null || $value === '') {
            return null;
        }
        if ($value instanceof CarbonInterface) {
            return $value;
        }
        return Carbon::parse($value);
    }
    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_array($value) || is_object($value)) {
            return null;
        }
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}