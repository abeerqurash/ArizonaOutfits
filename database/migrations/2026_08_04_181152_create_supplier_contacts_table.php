<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'supplier_contacts',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('supplier_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('name');
                $table->string('job_title')->nullable();
                $table->string('department', 120)->nullable();
                $table->string('email')->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('mobile', 50)->nullable();

                $table->boolean('is_primary')
                    ->default(false);

                $table->boolean('receives_purchase_orders')
                    ->default(false);

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique([
                    'supplier_id',
                    'email',
                ]);

                $table->index([
                    'supplier_id',
                    'is_primary',
                ]);

                $table->index([
                    'supplier_id',
                    'receives_purchase_orders',
                ], 'supplier_contacts_po_recipient_index');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'supplier_contacts'
        );
    }
};
