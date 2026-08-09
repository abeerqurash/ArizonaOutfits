<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the suppliers table.
     */
    public function up(): void
    {
        Schema::create(
            'suppliers',
            function (Blueprint $table): void {
                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Supplier identity
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('company_name')
                    ->index();

                $table
                    ->string('slug')
                    ->unique();

                $table
                    ->string('supplier_code', 60)
                    ->nullable()
                    ->unique();

                $table
                    ->string('contact_person')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Contact information
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('email')
                    ->nullable()
                    ->index();

                $table
                    ->string('phone', 50)
                    ->nullable();

                $table
                    ->string('alternate_phone', 50)
                    ->nullable();

                $table
                    ->string('website')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Address
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('address_line_1')
                    ->nullable();

                $table
                    ->string('address_line_2')
                    ->nullable();

                $table
                    ->string('city', 120)
                    ->nullable();

                $table
                    ->string('state', 120)
                    ->nullable();

                $table
                    ->string('postal_code', 40)
                    ->nullable();

                $table
                    ->string('country', 120)
                    ->nullable()
                    ->index();

                /*
                |--------------------------------------------------------------------------
                | Commercial information
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('currency', 3)
                    ->default('GBP');

                $table
                    ->string('payment_terms', 60)
                    ->nullable();

                $table
                    ->unsignedInteger('lead_time_days')
                    ->nullable();

                $table
                    ->decimal('credit_limit', 14, 2)
                    ->nullable();

                $table
                    ->string('tax_number', 120)
                    ->nullable();

                $table
                    ->string('registration_number', 120)
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Banking information
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('bank_name')
                    ->nullable();

                $table
                    ->string('account_name')
                    ->nullable();

                $table
                    ->string('account_number', 120)
                    ->nullable();

                $table
                    ->string('sort_code', 60)
                    ->nullable();

                $table
                    ->string('iban', 120)
                    ->nullable();

                $table
                    ->string('swift_code', 60)
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Supplier status
                |--------------------------------------------------------------------------
                */

                $table
                    ->string('status', 40)
                    ->default('active')
                    ->index();

                $table
                    ->boolean('is_preferred')
                    ->default(false)
                    ->index();

                /*
                |--------------------------------------------------------------------------
                | Notes
                |--------------------------------------------------------------------------
                */

                $table
                    ->text('notes')
                    ->nullable();

                $table
                    ->text('internal_notes')
                    ->nullable();

                /*
                |--------------------------------------------------------------------------
                | Administrator
                |--------------------------------------------------------------------------
                */

                $table
                    ->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();
                $table->softDeletes();

                /*
                |--------------------------------------------------------------------------
                | Helpful indexes
                |--------------------------------------------------------------------------
                */

                $table->index([
                    'status',
                    'company_name',
                ]);

                $table->index([
                    'country',
                    'status',
                ]);
            }
        );
    }

    /**
     * Remove the suppliers table.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};