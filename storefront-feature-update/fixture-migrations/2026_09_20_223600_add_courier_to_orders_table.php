<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'courier')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('courier', 255)
                    ->nullable()
                    ->after('tracking_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('orders', 'courier')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('courier');
            });
        }
    }
};
