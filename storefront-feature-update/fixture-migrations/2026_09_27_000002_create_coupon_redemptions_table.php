<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('coupon_id')
                ->constrained('coupons')
                ->cascadeOnDelete();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('coupon_code', 100);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->timestamp('redeemed_at');

            $table->timestamps();

            // One order can consume its coupon only once.
            $table->unique('order_id', 'coupon_redemptions_order_unique');

            $table->index(
                ['coupon_id', 'user_id'],
                'coupon_redemptions_coupon_user_index'
            );

            $table->index(
                ['coupon_id', 'redeemed_at'],
                'coupon_redemptions_coupon_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_redemptions');
    }
};
