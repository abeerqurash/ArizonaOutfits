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
        Schema::table('orders', function (Blueprint $table): void {
            $table
                ->timestamp('inventory_deducted_at')
                ->nullable()
                ->after('paid_at');

            $table
                ->index('inventory_deducted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex([
                'inventory_deducted_at',
            ]);

            $table->dropColumn(
                'inventory_deducted_at'
            );
        });
    }
};