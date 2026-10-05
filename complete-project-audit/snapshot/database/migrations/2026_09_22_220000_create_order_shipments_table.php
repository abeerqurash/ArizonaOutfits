<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_shipments')) {
            return;
        }

        Schema::create('order_shipments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Internal courier/provider information. Never replaces orders.tracking_number.
            $table->string('courier_provider', 100)->nullable();
            $table->string('courier_tracking_number', 255)->nullable();

            // Provider identifiers/references used later for webhook/API matching.
            $table->string('external_shipment_id', 255)->nullable();
            $table->string('external_reference', 255)->nullable();

            // manual now; webhook/api can be used later.
            $table->string('tracking_mode', 20)->default('manual');

            // Raw courier status vs ArizonaOutfits-normalized status.
            $table->string('provider_status', 255)->nullable();
            $table->string('normalized_status', 50)->nullable();

            $table->text('tracking_url')->nullable();
            $table->timestamp('last_event_at')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('order_id', 'order_shipments_order_id_index');
            $table->index('courier_provider', 'order_shipments_provider_index');
            $table->index('courier_tracking_number', 'order_shipments_tracking_index');
            $table->index('external_shipment_id', 'order_shipments_external_id_index');
            $table->index('external_reference', 'order_shipments_reference_index');
            $table->index('tracking_mode', 'order_shipments_mode_index');
            $table->index('normalized_status', 'order_shipments_normalized_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shipments');
    }
};
