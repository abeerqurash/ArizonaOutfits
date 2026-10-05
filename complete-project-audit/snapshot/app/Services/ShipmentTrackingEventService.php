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

    /**
     * Store one internal courier tracking event safely.
     *
     * Every valid event remains in shipment_tracking_events as history.
     * Only a strictly newer event is allowed to update the shipment's current
     * provider/normalized status snapshot.
     *
     * Raw provider data stays internal. This service does not change the
     * customer-facing ArizonaOutfits tracking number or order status.
     */
    public function record(
        OrderShipment $shipment,
        array $eventData
    ): ShipmentTrackingEvent {
        $provider = $this->nullableString(
            $eventData['provider'] ?? $shipment->courier_provider
        );

        /*
         * Provider identity protection.
         */
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

        /*
         * Fast idempotency check when the courier supplies a stable event ID.
         */
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

        /*
         * A trusted normalized status may be supplied explicitly by a future
         * authenticated provider adapter. Otherwise derive it conservatively
         * from the provider status using ShipmentStatusMapper.
         */
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
                /*
                 * Lock the shipment snapshot before deciding whether this
                 * event is new enough to become the current shipment state.
                 */
                $lockedShipment = OrderShipment::query()
                    ->whereKey($shipment->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                 * Re-check idempotency after obtaining the shipment lock.
                 */
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

                /*
                 * DQ40C DATABASE-PRECISION SAFETY
                 * --------------------------------
                 * order_shipments.last_event_at is a MySQL TIMESTAMP without
                 * fractional-second precision. Carbon values may still contain
                 * microseconds before they are persisted.
                 *
                 * Therefore both sides are normalized to whole-second precision
                 * before comparing them. Without this, for example:
                 *
                 * 19:40:00.900000 (incoming Carbon)
                 * can incorrectly compare newer than
                 * 19:40:00.000000 (same timestamp after DB round-trip).
                 *
                 * Equal database-second timestamps are treated as a tie:
                 * the later-arriving event is stored in history but does not
                 * replace the snapshot established by the first event.
                 */
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

                    /*
                     * Persist the same whole-second value used for comparison
                     * so in-memory and database precision stay consistent.
                     */
                    $lockedShipment->last_event_at =
                        $effectiveEventTimeForComparison;

                    $lockedShipment->save();
                }

                return $event->fresh();
            });
        } catch (QueryException $exception) {
            /*
             * If two identical deliveries race, return the event row that won.
             */
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

    /**
     * Convert optional values into trimmed nullable strings.
     */
    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * Normalize an explicitly supplied ArizonaOutfits shipment status before
     * checking it against the mapper's controlled allow-list.
     */
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

    /**
     * Convert an optional date/time value into Carbon.
     */
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

    /**
     * Match the precision of order_shipments.last_event_at (MySQL TIMESTAMP).
     *
     * copy() is important: never mutate the Carbon instance supplied by the
     * caller or the event model.
     */
    private function toDatabaseSecondPrecision(
        CarbonInterface $value
    ): CarbonInterface {
        return $value->copy()->setMicrosecond(0);
    }

    /**
     * Keep payload compatible with the model's JSON/array cast.
     */
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

    /**
     * Detect common SQL unique-constraint errors without tying the service to
     * only one database driver.
     */
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
