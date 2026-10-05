<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prevent ambiguous courier webhook shipment resolution.
     *
     * A courier tracking number only needs to be unique within its provider,
     * because different courier companies may theoretically issue the same
     * tracking-number text.
     *
     * external_shipment_id follows the same provider-scoped rule.
     *
     * Nullable values remain allowed. MySQL permits multiple NULL values in
     * unique indexes, which is required while shipments are still manual or
     * have not yet been registered with a courier API.
     */
    public function up(): void
    {
        Schema::table('order_shipments', function (Blueprint $table) {
            $table->unique(
                ['courier_provider', 'courier_tracking_number'],
                'order_shipments_provider_tracking_unique'
            );

            $table->unique(
                ['courier_provider', 'external_shipment_id'],
                'order_shipments_provider_external_unique'
            );
        });
    }

    /**
     * Reverse the identity constraints.
     */
    public function down(): void
    {
        Schema::table('order_shipments', function (Blueprint $table) {
            $table->dropUnique(
                'order_shipments_provider_tracking_unique'
            );

            $table->dropUnique(
                'order_shipments_provider_external_unique'
            );
        });
    }
};
