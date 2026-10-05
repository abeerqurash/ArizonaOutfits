<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the supplier relationship.
     */
    public function up(): void
    {
        Schema::table(
            'purchase_orders',
            function (Blueprint $table): void {
                $table
                    ->foreignId('supplier_id')
                    ->nullable()
                    ->after('reference')
                    ->constrained('suppliers')
                    ->nullOnDelete();
            }
        );
    }

    /**
     * Remove the supplier relationship.
     */
    public function down(): void
    {
        Schema::table(
            'purchase_orders',
            function (Blueprint $table): void {
                $table->dropConstrainedForeignId(
                    'supplier_id'
                );
            }
        );
    }
};