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
        Schema::create('order_activities', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table
                ->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('type', 100);

            $table->string('title');

            $table->text('description')->nullable();

            $table->string('field_name')->nullable();

            $table->text('old_value')->nullable();

            $table->text('new_value')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index([
                'order_id',
                'created_at',
            ]);

            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_activities');
    }
};