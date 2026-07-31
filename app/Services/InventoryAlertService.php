<?php

namespace App\Services;

use App\Mail\InventoryAlertMail;
use App\Models\InventoryAlert;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class InventoryAlertService
{
    /**
     * Check the remaining inventory for every unique
     * product and variant included in an order.
     */
    public function checkOrder(Order $order): void
    {
        try {
            $order->loadMissing('items');

            $checkedProductIds = [];
            $checkedVariantIds = [];

            foreach ($order->items as $item) {
                /*
                 * Variant inventory.
                 */
                if ($item->variant_id) {
                    $variantId = (int) $item->variant_id;

                    if (
                        in_array(
                            $variantId,
                            $checkedVariantIds,
                            true
                        )
                    ) {
                        continue;
                    }

                    $variant = ProductVariant::query()
                        ->with('product')
                        ->find($variantId);

                    if ($variant) {
                        $this->checkVariant($variant);

                        $checkedVariantIds[] = $variantId;
                    }

                    continue;
                }

                /*
                 * Normal product inventory.
                 */
                if ($item->product_id) {
                    $productId = (int) $item->product_id;

                    if (
                        in_array(
                            $productId,
                            $checkedProductIds,
                            true
                        )
                    ) {
                        continue;
                    }

                    $product = Product::query()
                        ->find($productId);

                    if ($product) {
                        $this->checkProduct($product);

                        $checkedProductIds[] = $productId;
                    }
                }
            }
        } catch (Throwable $exception) {
            /*
             * Alert failures must never fail checkout,
             * payment confirmation or stock deduction.
             */
            report($exception);
        }
    }

    /**
     * Check the stock of a normal product.
     */
    public function checkProduct(Product $product): void
    {
        try {
            $product->refresh();

            $this->synchronizeAlert(
                product: $product,
                variant: null,
                stockLevel: max(
                    0,
                    (int) $product->stock
                )
            );
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Check the stock of a product variant.
     */
    public function checkVariant(
        ProductVariant $variant
    ): void {
        try {
            $variant->refresh();
            $variant->loadMissing('product');

            if (!$variant->product) {
                throw new RuntimeException(
                    'The parent product for variant #'
                        . $variant->id
                        . ' could not be found.'
                );
            }

            $this->synchronizeAlert(
                product: $variant->product,
                variant: $variant,
                stockLevel: max(
                    0,
                    (int) $variant->stock
                )
            );
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * Create, update or resolve an inventory alert.
     */
    private function synchronizeAlert(
        Product $product,
        ?ProductVariant $variant,
        int $stockLevel
    ): void {
        $threshold = max(
            0,
            (int) config(
                'inventory.low_stock_threshold',
                5
            )
        );

        /*
         * Inventory has been replenished above the threshold.
         */
        if ($stockLevel > $threshold) {
            $this->resolveActiveAlerts(
                $product,
                $variant
            );

            return;
        }

        $alertType = $stockLevel === 0
            ? 'out_of_stock'
            : 'low_stock';

        $alertToNotify = DB::transaction(
            function () use (
                $product,
                $variant,
                $stockLevel,
                $threshold,
                $alertType
            ): ?InventoryAlert {
                /*
                 * Lock all active alerts for this inventory item.
                 */
                $activeAlerts = InventoryAlert::query()
                    ->where(
                        'product_id',
                        $product->id
                    )
                    ->when(
                        $variant,
                        fn ($query) => $query->where(
                            'product_variant_id',
                            $variant->id
                        ),
                        fn ($query) => $query->whereNull(
                            'product_variant_id'
                        )
                    )
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->get();

                /*
                 * Resolve the previous alert type when the
                 * stock state changes.
                 *
                 * Example:
                 * low_stock → out_of_stock
                 */
                foreach ($activeAlerts as $activeAlert) {
                    if (
                        $activeAlert->alert_type
                        !== $alertType
                    ) {
                        $activeAlert->update([
                            'status' => 'resolved',
                            'resolved_at' => now(),
                        ]);
                    }
                }

                $existingAlert = $activeAlerts->first(
                    fn (InventoryAlert $alert): bool =>
                        $alert->alert_type === $alertType
                );

                /*
                 * Update the stock value without sending
                 * another duplicate notification.
                 */
                if ($existingAlert) {
                    $existingAlert->update([
                        'stock_level' => $stockLevel,
                        'threshold' => $threshold,
                    ]);

                    return null;
                }

                return InventoryAlert::query()->create([
                    'product_id' => $product->id,

                    'product_variant_id' =>
                        $variant?->id,

                    'alert_type' => $alertType,

                    'stock_level' => $stockLevel,

                    'threshold' => $threshold,

                    'status' => 'active',

                    'notified_at' => null,

                    'resolved_at' => null,
                ]);
            },
            3
        );

        if (!$alertToNotify) {
            return;
        }

        $alertToNotify->load([
            'product',
            'variant.product',
        ]);

        $this->sendAlertEmail(
            $alertToNotify
        );
    }

    /**
     * Resolve active alerts after inventory is replenished.
     */
    private function resolveActiveAlerts(
        Product $product,
        ?ProductVariant $variant
    ): void {
        InventoryAlert::query()
            ->where(
                'product_id',
                $product->id
            )
            ->when(
                $variant,
                fn ($query) => $query->where(
                    'product_variant_id',
                    $variant->id
                ),
                fn ($query) => $query->whereNull(
                    'product_variant_id'
                )
            )
            ->where('status', 'active')
            ->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);
    }

    /**
     * Send the administrator inventory notification.
     */
    private function sendAlertEmail(
        InventoryAlert $alert
    ): void {
        $recipient = trim(
            (string) (
                config('inventory.alert_email')
                ?: config('mail.admin_order_email')
            )
        );

        if ($recipient === '') {
            report(
                new RuntimeException(
                    'INVENTORY_ALERT_EMAIL is not configured.'
                )
            );

            return;
        }

        try {
            Mail::to($recipient)->send(
                new InventoryAlertMail($alert)
            );

            $alert->update([
                'notified_at' => now(),
            ]);
        } catch (Throwable $exception) {
            /*
             * Email failure is logged but does not affect
             * the successfully completed order.
             */
            report($exception);
        }
    }
}