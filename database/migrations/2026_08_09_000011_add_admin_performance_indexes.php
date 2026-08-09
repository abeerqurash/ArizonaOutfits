<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('orders', ['created_at'], 'orders_created_at_idx');
        $this->addIndex('orders', ['payment_status','order_status','created_at'], 'orders_payment_order_date_idx');
        $this->addIndex('orders', ['user_id','created_at'], 'orders_user_date_idx');
        $this->addIndex('order_items', ['order_id','product_id'], 'order_items_order_product_idx');
        $this->addIndex('products', ['status','stock'], 'products_status_stock_idx');
        $this->addIndex('inventory_alerts', ['status','created_at'], 'inventory_alerts_status_date_idx');
        $this->addIndex('users', ['is_admin','status'], 'users_admin_status_idx');
        $this->addIndex('reviews', ['status','created_at'], 'reviews_status_date_idx');
    }

    public function down(): void
    {
        $this->dropIndex('orders','orders_created_at_idx');
        $this->dropIndex('orders','orders_payment_order_date_idx');
        $this->dropIndex('orders','orders_user_date_idx');
        $this->dropIndex('order_items','order_items_order_product_idx');
        $this->dropIndex('products','products_status_stock_idx');
        $this->dropIndex('inventory_alerts','inventory_alerts_status_date_idx');
        $this->dropIndex('users','users_admin_status_idx');
        $this->dropIndex('reviews','reviews_status_date_idx');
    }

    private function addIndex(string $table, array $columns, string $name): void
    {
        if (Schema::hasTable($table) && !Schema::hasIndex($table, $name)) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
        }
    }

    private function dropIndex(string $table, string $name): void
    {
        if (Schema::hasTable($table) && Schema::hasIndex($table, $name)) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
        }
    }
};
