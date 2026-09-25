<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_email_identities', function (Blueprint $table) {
            $table->timestamp('provider_verified_at')
                ->nullable()
                ->after('provider_user_id');

            $table->timestamp('email_verified_at')
                ->nullable()
                ->after('provider_verified_at');

            $table->timestamp('email_verification_pending_at')
                ->nullable()
                ->after('email_verified_at');
        });

        /*
         * DQ60ZM used verified_at for two different meanings:
         *
         * custom   = ArizonaOutfits email-link verification
         * social   = successful OAuth/provider authentication
         *
         * Split those meanings without deleting the legacy columns yet.
         * Keeping verified_at / verification_pending_at temporarily makes
         * this migration safe for the already-passed code while later steps
         * move each reader/writer to the explicit fields.
         */
        DB::table('customer_email_identities')
            ->where('source', 'custom')
            ->whereNotNull('verified_at')
            ->update([
                'email_verified_at' => DB::raw('verified_at'),
            ]);

        DB::table('customer_email_identities')
            ->where('source', 'custom')
            ->whereNotNull('verification_pending_at')
            ->update([
                'email_verification_pending_at' =>
                    DB::raw('verification_pending_at'),
            ]);

        DB::table('customer_email_identities')
            ->whereIn('source', ['google', 'facebook'])
            ->whereNotNull('verified_at')
            ->update([
                'provider_verified_at' => DB::raw('verified_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('customer_email_identities', function (Blueprint $table) {
            $table->dropColumn([
                'provider_verified_at',
                'email_verified_at',
                'email_verification_pending_at',
            ]);
        });
    }
};
