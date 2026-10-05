<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table
                ->string('payment_provider')
                ->nullable()
                ->after('payment_method');

            $table
                ->string('payment_reference')
                ->nullable()
                ->index()
                ->after('payment_provider');

            $table
                ->string('payment_intent_id')
                ->nullable()
                ->unique()
                ->after('payment_reference');

            $table
                ->timestamp('paid_at')
                ->nullable()
                ->after('payment_status');

            $table
                ->timestamp('payment_failed_at')
                ->nullable()
                ->after('paid_at');

            $table
                ->text('payment_failure_message')
                ->nullable()
                ->after('payment_failed_at');

            $table
                ->json('payment_metadata')
                ->nullable()
                ->after('payment_failure_message');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex([
                'payment_reference',
            ]);

            $table->dropUnique([
                'payment_intent_id',
            ]);

            $table->dropColumn([
                'payment_provider',
                'payment_reference',
                'payment_intent_id',
                'paid_at',
                'payment_failed_at',
                'payment_failure_message',
                'payment_metadata',
            ]);
        });
    }
};