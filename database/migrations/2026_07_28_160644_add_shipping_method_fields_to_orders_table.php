<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shipping_method')) {
                $table->string('shipping_method')
                    ->nullable()
                    ->after('shipping');
            }

            if (!Schema::hasColumn('orders', 'shipping_price')) {
                $table->decimal('shipping_price', 10, 2)
                    ->default(0)
                    ->after('shipping_method');
            }

            if (!Schema::hasColumn('orders', 'estimated_delivery')) {
                $table->string('estimated_delivery')
                    ->nullable()
                    ->after('shipping_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('orders', 'shipping_method')) {
                $columns[] = 'shipping_method';
            }

            if (Schema::hasColumn('orders', 'shipping_price')) {
                $columns[] = 'shipping_price';
            }

            if (Schema::hasColumn('orders', 'estimated_delivery')) {
                $columns[] = 'estimated_delivery';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};