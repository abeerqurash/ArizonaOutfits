<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'supplier_purchase_order_deliveries',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('supplier_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('purchase_order_id')
                    ->constrained('purchase_orders')
                    ->cascadeOnDelete();

                $table->json('recipient_emails');
                $table->string('subject');
                $table->text('message')->nullable();
                $table->boolean('attach_pdf')
                    ->default(true);
                $table->string('status', 30)
                    ->default('queued');
                $table->text('error_message')->nullable();

                $table->foreignId('sent_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('queued_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->timestamps();

                $table->index([
                    'supplier_id',
                    'status',
                ], 'supplier_po_delivery_status_index');

                $table->index([
                    'purchase_order_id',
                    'created_at',
                ], 'supplier_po_delivery_order_index');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'supplier_purchase_order_deliveries'
        );
    }
};
