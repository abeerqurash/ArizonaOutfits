<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_order_receipts')) {
            Schema::create(
                'purchase_order_receipts',
                function (Blueprint $table): void {
                    $table->id();
                    $table->string('reference', 40)->unique();
                    $table->foreignId('purchase_order_id')
                        ->constrained()
                        ->cascadeOnDelete();
                    $table->foreignId('supplier_id')
                        ->nullable()
                        ->constrained('suppliers')
                        ->nullOnDelete();
                    $table->dateTime('received_at');
                    $table->text('notes')->nullable();
                    $table->foreignId('received_by')
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();
                    $table->timestamps();

                    $table->index([
                        'purchase_order_id',
                        'received_at',
                    ]);
                }
            );
        }

        if (!Schema::hasTable('purchase_order_receipt_items')) {
            Schema::create(
                'purchase_order_receipt_items',
                function (Blueprint $table): void {
                    $table->id();
                    $table->foreignId('purchase_order_receipt_id')
                        ->constrained()
                        ->cascadeOnDelete();
                    $table->foreignId('purchase_order_item_id')
                        ->constrained()
                        ->cascadeOnDelete();
                    $table->foreignId('product_id')
                        ->nullable()
                        ->constrained()
                        ->nullOnDelete();
                    $table->foreignId('product_variant_id')
                        ->nullable()
                        ->constrained('product_variants')
                        ->nullOnDelete();
                    $table->unsignedInteger('quantity_received');
                    $table->unsignedInteger('quantity_corrected')->default(0);
                    $table->unsignedInteger('stock_before');
                    $table->unsignedInteger('stock_after');
                    $table->timestamps();

                    $table->unique(
                        [
                            'purchase_order_receipt_id',
                            'purchase_order_item_id',
                        ],
                        'receipt_purchase_order_item_unique'
                    );
                }
            );
        }

        if (!Schema::hasTable('supplier_returns')) {
            Schema::create(
                'supplier_returns',
                function (Blueprint $table): void {
                    $table->id();
                    $table->string('reference', 40)->unique();
                    $table->foreignId('purchase_order_id')
                        ->constrained()
                        ->cascadeOnDelete();
                    $table->foreignId('supplier_id')
                        ->nullable()
                        ->constrained('suppliers')
                        ->nullOnDelete();
                    $table->string('status', 30)->default('submitted');
                    $table->string('reason_category', 60);
                    $table->text('reason');
                    $table->dateTime('returned_at');
                    $table->decimal('expected_credit', 14, 2)->default(0);
                    $table->decimal('actual_credit', 14, 2)->nullable();
                    $table->text('notes')->nullable();
                    $table->foreignId('created_by')
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();
                    $table->foreignId('completed_by')
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();
                    $table->dateTime('completed_at')->nullable();
                    $table->foreignId('cancelled_by')
                        ->nullable()
                        ->constrained('users')
                        ->nullOnDelete();
                    $table->dateTime('cancelled_at')->nullable();
                    $table->text('cancellation_reason')->nullable();
                    $table->timestamps();

                    $table->index([
                        'purchase_order_id',
                        'status',
                    ]);
                }
            );
        }

        if (!Schema::hasTable('supplier_return_items')) {
            Schema::create(
                'supplier_return_items',
                function (Blueprint $table): void {
                    $table->id();
                    $table->foreignId('supplier_return_id')
                        ->constrained()
                        ->cascadeOnDelete();
                    $table->foreignId('purchase_order_item_id')
                        ->constrained()
                        ->cascadeOnDelete();
                    $table->foreignId('product_id')
                        ->nullable()
                        ->constrained()
                        ->nullOnDelete();
                    $table->foreignId('product_variant_id')
                        ->nullable()
                        ->constrained('product_variants')
                        ->nullOnDelete();
                    $table->unsignedInteger('quantity');
                    $table->decimal('unit_cost', 14, 2);
                    $table->decimal('line_total', 14, 2);
                    $table->unsignedInteger('stock_before');
                    $table->unsignedInteger('stock_after');
                    $table->timestamps();

                    $table->unique(
                        [
                            'supplier_return_id',
                            'purchase_order_item_id',
                        ],
                        'supplier_return_purchase_order_item_unique'
                    );
                }
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_return_items');
        Schema::dropIfExists('supplier_returns');
        Schema::dropIfExists('purchase_order_receipt_items');
        Schema::dropIfExists('purchase_order_receipts');
    }
};
