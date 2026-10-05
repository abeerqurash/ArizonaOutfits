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

        /* MySQL-only legacy data conversion omitted in this empty SQLite fixture. */
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
