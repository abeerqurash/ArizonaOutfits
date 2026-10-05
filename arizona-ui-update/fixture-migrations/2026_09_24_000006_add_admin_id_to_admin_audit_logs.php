<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('admin_audit_logs', 'admin_id')) {
            Schema::table('admin_audit_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('admin_id')->nullable()->after('user_id');
                $table->foreign('admin_id')->references('id')->on('admins')->nullOnDelete();
                $table->index('admin_id');
            });
        }

        /* MySQL-only legacy data conversion omitted in this empty SQLite fixture. */
    }

    public function down(): void
    {
        if (Schema::hasColumn('admin_audit_logs', 'admin_id')) {
            Schema::table('admin_audit_logs', function (Blueprint $table) {
                $table->dropForeign(['admin_id']);
                $table->dropIndex(['admin_id']);
                $table->dropColumn('admin_id');
            });
        }
    }
};
