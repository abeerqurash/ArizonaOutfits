<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the purchase_orders table.
     */
    public function up(): void
    {
        Schema::create(
            'purchase_orders',
            function (Blueprint $table): void {
                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Purchase order reference
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('reference', 60)
                    ->unique();

                /*
                |--------------------------------------------------------------------------
                | Supplier information
                |--------------------------------------------------------------------------
                |
                | We will create a full suppliers module later.
                | For now, supplier information is stored directly on the PO.
                |
                */

                $table
                    ->string('supplier_name')
                    ->nullable();

                $table
                    ->string('supplier_email')
                    ->nullable();

                $table
                    ->string('supplier_phone', 50)
                    ->nullable();

                $table
                    ->text('supplier_address')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('status', 40)
                    ->default('draft')
                    ->index();

                /*
                |--------------------------------------------------------------------------
                | Dates
                |--------------------------------------------------------------------------
                */

                $table
                    ->date('order_date')
                    ->nullable();

                $table
                    ->date('expected_date')
                    ->nullable();

                $table
                    ->timestamp('ordered_at')
                    ->nullable();

                $table
                    ->timestamp('received_at')
                    ->nullable();

                $table
                    ->timestamp('cancelled_at')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Financial totals
                |--------------------------------------------------------------------------
                */

                $table
                    ->decimal('subtotal', 14, 2)
                    ->default(0);

                $table
                    ->decimal('tax_amount', 14, 2)
                    ->default(0);

                $table
                    ->decimal('shipping_amount', 14, 2)
                    ->default(0);

                $table
                    ->decimal('discount_amount', 14, 2)
                    ->default(0);

                $table
                    ->decimal('total_amount', 14, 2)
                    ->default(0);

                /*
                |--------------------------------------------------------------------------
                | Additional information
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('currency', 3)
                    ->default('GBP');

                $table
                    ->text('notes')
                    ->nullable();

                $table
                    ->text('internal_notes')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Administrator who created the order
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();
            }
        );
    }

    /**
     * Remove the purchase_orders table.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'purchase_orders'
        );
    }
};