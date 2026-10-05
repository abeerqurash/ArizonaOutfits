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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('sku')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();
            $table->longText('additional_info')->nullable();
            $table->decimal('regular_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('status')->default('active'); // active, draft, inactive
            $table->string('featured_image')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('favorites_count')->default(0);
            $table->integer('cart_count')->default(0);
            $table->integer('purchase_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
