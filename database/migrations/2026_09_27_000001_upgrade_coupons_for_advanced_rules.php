<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->decimal('maximum_discount', 10, 2)->nullable()->after('minimum_order_amount');
            $table->unsignedInteger('per_user_usage_limit')->nullable()->after('usage_limit');

            $table->string('target_type', 30)->default('all')->after('per_user_usage_limit');
            $table->json('product_ids')->nullable()->after('target_type');
            $table->json('category_ids')->nullable()->after('product_ids');
            $table->json('variant_ids')->nullable()->after('category_ids');
            $table->boolean('include_child_categories')->default(true)->after('variant_ids');

            $table->string('event_name', 120)->nullable()->after('include_child_categories');
        });

        /*
         * The original columns are DATE. Convert them to DATETIME so the
         * administrator can control the exact activation/expiration time.
         * Existing dates are preserved at 00:00:00.
         */
        DB::statement('ALTER TABLE coupons MODIFY start_date DATETIME NULL');
        DB::statement('ALTER TABLE coupons MODIFY end_date DATETIME NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE coupons MODIFY start_date DATE NULL');
        DB::statement('ALTER TABLE coupons MODIFY end_date DATE NULL');

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn([
                'maximum_discount',
                'per_user_usage_limit',
                'target_type',
                'product_ids',
                'category_ids',
                'variant_ids',
                'include_child_categories',
                'event_name',
            ]);
        });
    }
};
