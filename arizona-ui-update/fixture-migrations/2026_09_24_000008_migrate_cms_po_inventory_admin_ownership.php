<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pages', 'creator_admin_id')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->unsignedBigInteger('creator_admin_id')->nullable()->after('created_by');
                $table->foreign('creator_admin_id')->references('id')->on('admins')->nullOnDelete();
                $table->index('creator_admin_id');
            });
        }

        if (!Schema::hasColumn('pages', 'editor_admin_id')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->unsignedBigInteger('editor_admin_id')->nullable()->after('updated_by');
                $table->foreign('editor_admin_id')->references('id')->on('admins')->nullOnDelete();
                $table->index('editor_admin_id');
            });
        }

        if (!Schema::hasColumn('purchase_orders', 'admin_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('admin_id')->nullable()->after('created_by');
                $table->foreign('admin_id')->references('id')->on('admins')->nullOnDelete();
                $table->index('admin_id');
            });
        }

        if (!Schema::hasColumn('inventory_histories', 'admin_id')) {
            Schema::table('inventory_histories', function (Blueprint $table) {
                $table->unsignedBigInteger('admin_id')->nullable()->after('user_id');
                $table->foreign('admin_id')->references('id')->on('admins')->nullOnDelete();
                $table->index('admin_id');
            });
        }

        /* MySQL-only legacy data conversion omitted in this empty SQLite fixture. */

        /* MySQL-only legacy data conversion omitted in this empty SQLite fixture. */

        /* MySQL-only legacy data conversion omitted in this empty SQLite fixture. */

        /* MySQL-only legacy data conversion omitted in this empty SQLite fixture. */
    }

    public function down(): void
    {
        if (Schema::hasColumn('inventory_histories', 'admin_id')) {
            Schema::table('inventory_histories', function (Blueprint $table) {
                $table->dropForeign(['admin_id']);
                $table->dropIndex(['admin_id']);
                $table->dropColumn('admin_id');
            });
        }

        if (Schema::hasColumn('purchase_orders', 'admin_id')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropForeign(['admin_id']);
                $table->dropIndex(['admin_id']);
                $table->dropColumn('admin_id');
            });
        }

        if (Schema::hasColumn('pages', 'editor_admin_id')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropForeign(['editor_admin_id']);
                $table->dropIndex(['editor_admin_id']);
                $table->dropColumn('editor_admin_id');
            });
        }

        if (Schema::hasColumn('pages', 'creator_admin_id')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropForeign(['creator_admin_id']);
                $table->dropIndex(['creator_admin_id']);
                $table->dropColumn('creator_admin_id');
            });
        }
    }
};
