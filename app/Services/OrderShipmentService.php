<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderShipment;
use Illuminate\Support\Facades\DB;

class OrderShipmentService
{
    /**
     * Synchronize the current internal courier fields on an order with its
     * shipment record.
     *
     * Important:
     * - Order::tracking_number is the permanent ArizonaOutfits customer-facing
     *   TRK-... code and is never changed here.
     * - Order::courier is the external courier tracking number.
     * - Order::courier_provider is the courier/service name.
     * - Raw courier/provider information remains internal/admin data.
     */
    public function syncFromOrder(Order $order): ?OrderShipment
    {
        $courierProvider = $this->cleanNullableString(
            $order->courier_provider
        );

        $courierTrackingNumber = $this->cleanNullableString(
            $order->courier
        );

        /*
         * Do not create an empty shipment merely because an order exists.
         * A shipment record begins once courier information is available.
         */
        if ($courierProvider === null && $courierTrackingNumber === null) {
            return null;
        }

        return DB::transaction(function () use (
            $order,
            $courierProvider,
            $courierTrackingNumber
        ): OrderShipment {
            $shipment = OrderShipment::query()
                ->where('order_id', $order->id)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$shipment) {
                $shipment = new OrderShipment();
                $shipment->order_id = $order->id;
                $shipment->tracking_mode = 'manual';
            }

            $shipment->courier_provider = $courierProvider;
            $shipment->courier_tracking_number = $courierTrackingNumber;

            /*
             * Use ArizonaOutfits' public AO-... order number as the external
             * merchant/reference value for future courier integrations.
             */
            $shipment->external_reference = $this->cleanNullableString(
                $order->order_number
            );

            /*
             * Do not overwrite future webhook/API-owned fields here:
             * external_shipment_id, provider_status, normalized_status,
             * tracking_url, last_event_at, metadata.
             */
            $shipment->save();

            return $shipment->fresh();
        });
    }

    /**
     * Normalize optional text values before persistence.
     */
    private function cleanNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
