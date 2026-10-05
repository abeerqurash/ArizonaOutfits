<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Make password optional
        |--------------------------------------------------------------------------
        |
        | Phone-only / Google-only / Facebook-only customers do not need a
        | meaningless generated password.
        |
        */

        DB::statement(
            'ALTER TABLE users MODIFY password VARCHAR(255) NULL'
        );


        /*
        |--------------------------------------------------------------------------
        | Add multi-login/security fields
        |--------------------------------------------------------------------------
        */

        Schema::table('users', function (Blueprint $table) {

            /*
             * Existing phone column stays unchanged.
             * This timestamp tells us whether that number has actually
             * been verified by OTP.
             */
            $table
                ->timestamp('phone_verified_at')
                ->nullable()
                ->after('phone');


            /*
             * Used for once-per-day security setup reminders.
             */
            $table
                ->timestamp('security_reminder_shown_at')
                ->nullable()
                ->after('phone_verified_at');


            /*
             * Records how the customer originally created the account.
             */
            $table
                ->string('registration_method', 30)
                ->nullable()
                ->after('security_reminder_shown_at');
        });


        /*
        |--------------------------------------------------------------------------
        | Protect phone numbers from duplicate accounts
        |--------------------------------------------------------------------------
        |
        | Multiple NULL values are allowed by MySQL unique indexes.
        |
        */

        Schema::table('users', function (Blueprint $table) {
            $table->unique('phone');
        });
    }


    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropUnique([
                'phone',
            ]);

            $table->dropColumn([
                'phone_verified_at',
                'security_reminder_shown_at',
                'registration_method',
            ]);
        });


        /*
         * Restore the original required password structure.
         *
         * WARNING:
         * Rollback should only be performed if all users have passwords.
         */
        DB::statement(
            'UPDATE users
             SET password = ""
             WHERE password IS NULL'
        );

        DB::statement(
            'ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL'
        );
    }
};