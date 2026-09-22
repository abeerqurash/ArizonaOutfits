<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the internal courier tracking event history.
     *
     * Raw courier/provider events are for internal/admin use.
     * Customer-facing tracking must continue to use ArizonaOutfits
     * normalized order statuses and the permanent TRK-... code.
     */
    public function up(): void
    {
        if (Schema::hasTable('shipment_tracking_events')) {
            return;
        }

        Schema::create('shipment_tracking_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shipment_id')
                ->constrained('order_shipments')
                ->cascadeOnDelete();

            // Courier/service that produced the event.
            $table->string('provider', 100)->nullable();

            // Provider event ID used for webhook/API idempotency when available.
            $table->string('external_event_id', 255)->nullable();

            // Raw status supplied by the courier.
            $table->string('provider_status', 255)->nullable();

            // ArizonaOutfits-normalized equivalent, when mapped.
            $table->string('normalized_status', 50)->nullable();

            $table->text('description')->nullable();
            $table->string('location', 255)->nullable();

            // Actual courier event time, which may differ from received_at.
            $table->timestamp('event_time')->nullable();

            // When ArizonaOutfits received/recorded the event.
            $table->timestamp('received_at')->nullable();

            // Raw provider/webhook/API data for audit/debugging.
            $table->json('payload')->nullable();

            $table->timestamps();

            $table->index('shipment_id', 'shipment_events_shipment_id_index');
            $table->index('provider', 'shipment_events_provider_index');
            $table->index('external_event_id', 'shipment_events_external_event_index');
            $table->index('provider_status', 'shipment_events_provider_status_index');
            $table->index('normalized_status', 'shipment_events_normalized_status_index');
            $table->index('event_time', 'shipment_events_event_time_index');

            /*
             * Prevent the same provider event from being stored twice for
             * the same shipment while still allowing NULL event IDs for
             * manual/provider events that do not supply one.
             */
            $table->unique(
                ['shipment_id', 'provider', 'external_event_id'],
                'shipment_events_provider_event_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_tracking_events');
    }
};
