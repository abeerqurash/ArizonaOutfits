<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tax')) {
                $table
                    ->decimal('tax', 10, 2)
                    ->default(0)
                    ->after('shipping');
            }

            if (!Schema::hasColumn('orders', 'billing_state')) {
                $table
                    ->string('billing_state')
                    ->nullable()
                    ->after('billing_city');
            }

            if (!Schema::hasColumn('orders', 'billing_zip')) {
                $table
                    ->string('billing_zip', 30)
                    ->nullable()
                    ->after('billing_state');
            }

            if (!Schema::hasColumn('orders', 'shipping_state')) {
                $table
                    ->string('shipping_state')
                    ->nullable()
                    ->after('shipping_city');
            }

            if (!Schema::hasColumn('orders', 'shipping_zip')) {
                $table
                    ->string('shipping_zip', 30)
                    ->nullable()
                    ->after('shipping_state');
            }

            if (!Schema::hasColumn('orders', 'order_notes')) {
                $table
                    ->text('order_notes')
                    ->nullable()
                    ->after('shipping_country');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'tax',
                'billing_state',
                'billing_zip',
                'shipping_state',
                'shipping_zip',
                'order_notes',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};