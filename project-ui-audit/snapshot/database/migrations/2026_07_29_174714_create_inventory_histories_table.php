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
        Schema::create('inventory_histories', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Inventory item
            |--------------------------------------------------------------------------
            */

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->foreignId('product_variant_id')
                ->nullable()
                ->constrained('product_variants')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Related order
            |--------------------------------------------------------------------------
            */

            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Administrator or user responsible
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Stock movement
            |--------------------------------------------------------------------------
            */

            $table->integer('quantity_change');

            $table->unsignedInteger('stock_before');

            $table->unsignedInteger('stock_after');

            /*
            |--------------------------------------------------------------------------
            | Movement details
            |--------------------------------------------------------------------------
            */

            $table->string('movement_type', 50);

            $table->string('reason', 255)->nullable();

            $table->text('notes')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Additional information
            |--------------------------------------------------------------------------
            */

            $table->string('reference_type', 100)->nullable();

            $table->string('reference_id', 100)->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'product_id',
                'created_at',
            ]);

            $table->index([
                'product_variant_id',
                'created_at',
            ]);

            $table->index([
                'movement_type',
                'created_at',
            ]);

            $table->index([
                'order_id',
                'created_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_histories');
    }
};