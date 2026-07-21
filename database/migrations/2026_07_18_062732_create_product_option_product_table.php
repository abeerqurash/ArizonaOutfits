<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_option_product')) {
            Schema::create(
                'product_option_product',
                function (Blueprint $table) {
                    $table->id();

                    $table
                        ->foreignId('product_id')
                        ->constrained('products')
                        ->cascadeOnDelete();

                    $table
                        ->foreignId('product_option_id')
                        ->constrained('product_options')
                        ->cascadeOnDelete();

                    $table->timestamps();

                    $table->unique(
                        [
                            'product_id',
                            'product_option_id',
                        ],
                        'product_option_unique'
                    );
                }
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'product_option_product'
        );
    }
};