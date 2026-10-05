<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_alerts', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table
                ->foreignId('product_variant_id')
                ->nullable()
                ->constrained('product_variants')
                ->nullOnDelete();

            $table->string('alert_type', 50);

            $table
                ->unsignedInteger('stock_level')
                ->default(0);

            $table
                ->unsignedInteger('threshold')
                ->default(5);

            $table
                ->string('status', 30)
                ->default('active');

            $table
                ->timestamp('notified_at')
                ->nullable();

            $table
                ->timestamp('resolved_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'product_id',
                'status',
            ]);

            $table->index([
                'product_variant_id',
                'status',
            ]);

            $table->index([
                'alert_type',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_alerts');
    }
};