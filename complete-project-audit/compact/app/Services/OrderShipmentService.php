<?php
namespace App\Services;
use App\Models\Order;
use App\Models\OrderShipment;
use Illuminate\Support\Facades\DB;
class OrderShipmentService
{
    public function syncFromOrder(Order $order): ?OrderShipment
    {
        $courierProvider = $this->cleanNullableString(
            $order->courier_provider
        );
        $courierTrackingNumber = $this->cleanNullableString(
            $order->courier
        );
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
            $shipment->external_reference = $this->cleanNullableString(
                $order->order_number
            );
            $shipment->save();
            return $shipment->fresh();
        });
    }
    private function cleanNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}