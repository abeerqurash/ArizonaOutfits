<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the purchase_order_items table.
     */
    public function up(): void
    {
        Schema::create(
            'purchase_order_items',
            function (Blueprint $table): void {
                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Parent purchase order
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('purchase_order_id')
                    ->constrained()
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Product and variant references
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('product_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table
                    ->foreignId('product_variant_id')
                    ->nullable()
                    ->constrained('product_variants')
                    ->nullOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Snapshot information
                |--------------------------------------------------------------------------
                |
                | Product details are copied here so old purchase orders remain
                | correct even if a product name, SKU or price changes later.
                |
                */

                $table
                    ->string('item_name');

                $table
                    ->string('sku', 100)
                    ->nullable();

                $table
                    ->string('variant_name')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Ordered and received quantities
                |--------------------------------------------------------------------------
                */

                $table
                    ->unsignedInteger('quantity_ordered')
                    ->default(0);

                $table
                    ->unsignedInteger('quantity_received')
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Cost and totals
                |--------------------------------------------------------------------------
                */

                $table
                    ->decimal('unit_cost', 14, 2)
                    ->default(0);

                $table
                    ->decimal('line_total', 14, 2)
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Inventory information at creation time
                |--------------------------------------------------------------------------
                */

                $table
                    ->unsignedInteger('stock_before')
                    ->default(0);

                $table
                    ->unsignedInteger('reorder_point')
                    ->nullable();

                $table
                    ->unsignedInteger('suggested_quantity')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Item notes
                |--------------------------------------------------------------------------
                */

                $table
                    ->text('notes')
                    ->nullable();

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | Helpful indexes
                |--------------------------------------------------------------------------
                */

                $table->index([
                    'purchase_order_id',
                    'product_id',
                ]);

                $table->index([
                    'purchase_order_id',
                    'product_variant_id',
                ]);
            }
        );
    }

    /**
     * Remove the purchase_order_items table.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'purchase_order_items'
        );
    }
};