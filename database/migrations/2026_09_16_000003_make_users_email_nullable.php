<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Allow phone-only customer accounts
        |--------------------------------------------------------------------------
        |
        | Phone-created accounts do not initially have an email address.
        |
        | Existing email accounts remain unchanged and the existing UNIQUE
        | index on email remains in place.
        |
        | MySQL allows multiple NULL values inside a UNIQUE index.
        |
        */

        DB::statement(
            'ALTER TABLE users MODIFY email VARCHAR(255) NULL'
        );
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Restore email as required
        |--------------------------------------------------------------------------
        |
        | Rollback is only safe if there are no phone-only users whose
        | email value is NULL.
        |
        */

        if (
            DB::table('users')
                ->whereNull('email')
                ->exists()
        ) {
            throw new RuntimeException(
                'Cannot make users.email NOT NULL because phone-only users with NULL email currently exist.'
            );
        }

        DB::statement(
            'ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL'
        );
    }
};