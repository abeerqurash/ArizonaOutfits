<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_notification_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('notification_type', 100);

            $table->string('recipient_email');

            $table->timestamp('sent_at')->nullable();

            $table->text('failure_message')->nullable();

            $table->timestamps();

            $table->unique(
                [
                    'order_id',
                    'notification_type',
                    'recipient_email',
                ],
                'order_notification_unique'
            );

            $table->index([
                'order_id',
                'notification_type',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_notification_logs');
    }
};