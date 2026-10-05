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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();

            $table->enum('type', [
                'fixed',
                'percentage',
            ]);

            $table->decimal('value', 10, 2);

            $table->decimal('minimum_order_amount', 10, 2)
                ->default(0);

            $table->timestamp('starts_at')->nullable();

            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
