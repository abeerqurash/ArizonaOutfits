<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('admins', 'phone')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->string('phone', 50)->nullable()->after('email');
            });
        }

        DB::statement(
            'UPDATE admins
             INNER JOIN users ON users.id = admins.legacy_user_id
             SET admins.phone = users.phone
             WHERE admins.phone IS NULL
               AND users.phone IS NOT NULL'
        );
    }

    public function down(): void
    {
        if (Schema::hasColumn('admins', 'phone')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn('phone');
            });
        }
    }
};
