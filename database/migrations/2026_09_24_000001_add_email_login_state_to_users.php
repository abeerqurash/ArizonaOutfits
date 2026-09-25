<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('email_login_enabled_at')
                ->nullable()
                ->after('email_verified_at');

            $table->timestamp('email_login_pending_at')
                ->nullable()
                ->after('email_login_enabled_at');
        });

        /*
         * Preserve genuine existing local email logins.
         *
         * A stored social/contact email alone is NOT enough.
         * Existing accounts are only backfilled as connected when the email
         * was already verified, a password exists, and the account was not
         * explicitly created as phone/social.
         */
        DB::table('users')
            ->whereNotNull('email')
            ->whereNotNull('email_verified_at')
            ->whereNotNull('password')
            ->where(function ($query): void {
                $query->whereNull('registration_method')
                    ->orWhere('registration_method', 'email');
            })
            ->update([
                'email_login_enabled_at' => DB::raw('email_verified_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'email_login_enabled_at',
                'email_login_pending_at',
            ]);
        });
    }
};
