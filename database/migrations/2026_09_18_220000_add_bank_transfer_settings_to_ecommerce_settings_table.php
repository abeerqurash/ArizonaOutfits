<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_settings', function (Blueprint $table) {
            $table->boolean('bank_transfer_enabled')
                ->default(false)
                ->after('cash_on_delivery_enabled');

            $table->string('bank_name')
                ->nullable()
                ->after('order_email_message');

            $table->string('bank_account_name')
                ->nullable()
                ->after('bank_name');

            $table->string('bank_account_number')
                ->nullable()
                ->after('bank_account_name');

            $table->string('bank_iban')
                ->nullable()
                ->after('bank_account_number');

            $table->string('bank_swift_code')
                ->nullable()
                ->after('bank_iban');

            $table->string('bank_branch_name')
                ->nullable()
                ->after('bank_swift_code');

            $table->text('bank_transfer_instructions')
                ->nullable()
                ->after('bank_branch_name');
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_settings', function (Blueprint $table) {
            $table->dropColumn([
                'bank_transfer_enabled',
                'bank_name',
                'bank_account_name',
                'bank_account_number',
                'bank_iban',
                'bank_swift_code',
                'bank_branch_name',
                'bank_transfer_instructions',
            ]);
        });
    }
};
