<?php
namespace App\Services;
use App\Models\OrderShipment;
use App\Models\ShipmentTrackingEvent;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
class ShipmentTrackingEventService
{
    public function __construct(
        private ShipmentStatusMapper $statusMapper
    ) {
    }
    public function record(
        OrderShipment $shipment,
        array $eventData
    ): ShipmentTrackingEvent {
        $provider = $this->nullableString(
            $eventData['provider'] ?? $shipment->courier_provider
        );
        $shipmentProvider = $this->nullableString(
            $shipment->courier_provider
        );
        if (
            $shipmentProvider !== null
            && $provider !== null
            && mb_strtolower($shipmentProvider) !== mb_strtolower($provider)
        ) {
            throw new InvalidArgumentException(
                'Courier provider mismatch for shipment.'
            );
        }
        $externalEventId = $this->nullableString(
            $eventData['external_event_id'] ?? null
        );
        if ($externalEventId !== null) {
            $existing = ShipmentTrackingEvent::query()
                ->where('shipment_id', $shipment->id)
                ->where('provider', $provider)
                ->where('external_event_id', $externalEventId)
                ->first();
            if ($existing) {
                return $existing;
            }
        }
        $eventTime = $this->nullableDateTime(
            $eventData['event_time'] ?? null
        );
        $receivedAt = $this->nullableDateTime(
            $eventData['received_at'] ?? null
        ) ?? now();
        $providerStatus = $this->nullableString(
            $eventData['provider_status'] ?? null
        );
        $normalizedStatus = $this->nullableString(
            $eventData['normalized_status'] ?? null
        );
        if ($normalizedStatus !== null) {
            $normalizedStatus = $this->normalizeStatusKey(
                $normalizedStatus
            );
            if (
                !in_array(
                    $normalizedStatus,
                    $this->statusMapper->allowedNormalizedStatuses(),
                    true
                )
            ) {
                $normalizedStatus = null;
            }
        }
        if ($normalizedStatus === null) {
            $normalizedStatus = $this->statusMapper->map(
                $providerStatus
            );
        }
        $attributes = [
            'shipment_id' => $shipment->id,
            'provider' => $provider,
            'external_event_id' => $externalEventId,
            'provider_status' => $providerStatus,
            'normalized_status' => $normalizedStatus,
            'description' => $this->nullableString(
                $eventData['description'] ?? null
            ),
            'location' => $this->nullableString(
                $eventData['location'] ?? null
            ),
            'event_time' => $eventTime,
            'received_at' => $receivedAt,
            'payload' => $this->normalizePayload(
                $eventData['payload'] ?? null
            ),
        ];
        try {
            return DB::transaction(function () use (
                $shipment,
                $attributes,
                $eventTime
            ): ShipmentTrackingEvent {
                $lockedShipment = OrderShipment::query()
                    ->whereKey($shipment->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                if ($attributes['external_event_id'] !== null) {
                    $existing = ShipmentTrackingEvent::query()
                        ->where('shipment_id', $lockedShipment->id)
                        ->where('provider', $attributes['provider'])
                        ->where(
                            'external_event_id',
                            $attributes['external_event_id']
                        )
                        ->first();
                    if ($existing) {
                        return $existing;
                    }
                }
                $event = ShipmentTrackingEvent::create($attributes);
                $effectiveEventTime =
                    $eventTime ?? $attributes['received_at'];
                $effectiveEventTimeForComparison =
                    $this->toDatabaseSecondPrecision(
                        $effectiveEventTime
                    );
                $lastEventAtForComparison =
                    $lockedShipment->last_event_at === null
                        ? null
                        : $this->toDatabaseSecondPrecision(
                            $lockedShipment->last_event_at
                        );
                $isLatestEvent =
                    $lastEventAtForComparison === null
                    || $effectiveEventTimeForComparison->greaterThan(
                        $lastEventAtForComparison
                    );
                if ($isLatestEvent) {
                    $lockedShipment->provider_status =
                        $attributes['provider_status'];
                    if ($attributes['normalized_status'] !== null) {
                        $lockedShipment->normalized_status =
                            $attributes['normalized_status'];
                    }
                    $lockedShipment->last_event_at =
                        $effectiveEventTimeForComparison;
                    $lockedShipment->save();
                }
                return $event->fresh();
            });
        } catch (QueryException $exception) {
            if (
                $externalEventId !== null
                && $this->isUniqueViolation($exception)
            ) {
                $existing = ShipmentTrackingEvent::query()
                    ->where('shipment_id', $shipment->id)
                    ->where('provider', $provider)
                    ->where('external_event_id', $externalEventId)
                    ->first();
                if ($existing) {
                    return $existing;
                }
            }
            throw $exception;
        }
    }
    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
    private function normalizeStatusKey(string $status): ?string
    {
        $status = trim(mb_strtolower($status));
        if ($status === '') {
            return null;
        }
        $status = preg_replace('/[^a-z0-9]+/u', '_', $status);
        $status = trim((string) $status, '_');
        return $status === '' ? null : $status;
    }
    private function nullableDateTime(mixed $value): ?CarbonInterface
    {
        if ($value === null || $value === '') {
            return null;
        }
        if ($value instanceof CarbonInterface) {
            return $value;
        }
        return Carbon::parse($value);
    }
    private function toDatabaseSecondPrecision(
        CarbonInterface $value
    ): CarbonInterface {
        return $value->copy()->setMicrosecond(0);
    }
    private function normalizePayload(mixed $payload): ?array
    {
        if ($payload === null) {
            return null;
        }
        if (is_array($payload)) {
            return $payload;
        }
        if (is_object($payload)) {
            return json_decode(
                json_encode($payload, JSON_THROW_ON_ERROR),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }
        return [
            'value' => $payload,
        ];
    }
    private function isUniqueViolation(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        if (in_array($sqlState, ['23000', '23505'], true)) {
            return true;
        }
        $message = Str::lower($exception->getMessage());
        return str_contains($message, 'unique')
            || str_contains($message, 'duplicate');
    }
}