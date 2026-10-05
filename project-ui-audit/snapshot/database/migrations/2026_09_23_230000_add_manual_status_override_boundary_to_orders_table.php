<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('manual_status_override_at')
                ->nullable()
                ->after('order_status');

            $table->timestamp('manual_status_override_shipment_event_at')
                ->nullable()
                ->after('manual_status_override_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'manual_status_override_at',
                'manual_status_override_shipment_event_at',
            ]);
        });
    }
};
