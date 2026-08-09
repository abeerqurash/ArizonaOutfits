<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('supplier_products')) {
            return;
        }

        Schema::create(
            'supplier_products',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('supplier_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('product_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->constrained('product_variants')
                    ->cascadeOnDelete();

                /*
                 * Zero represents the product-level/default supplier price.
                 * A real variant uses its product_variants.id value. This
                 * makes the unique database rule work reliably in MySQL.
                 */
                $table->unsignedBigInteger('variant_key')
                    ->default(0);

                $table->string('supplier_sku', 120)
                    ->nullable();

                $table->decimal('unit_cost', 14, 2);

                $table->unsignedInteger('minimum_order_quantity')
                    ->default(1);

                $table->unsignedInteger('lead_time_days')
                    ->nullable();

                $table->boolean('is_preferred')
                    ->default(false);

                $table->boolean('is_active')
                    ->default(true);

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(
                    [
                        'supplier_id',
                        'product_id',
                        'variant_key',
                    ],
                    'supplier_product_variant_unique'
                );

                $table->index(
                    [
                        'product_id',
                        'variant_key',
                        'is_preferred',
                    ],
                    'supplier_products_preferred_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_products');
    }
};
