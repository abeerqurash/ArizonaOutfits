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

        DB::statement(
            'UPDATE pages
             INNER JOIN admins ON admins.legacy_user_id = pages.created_by
             SET pages.creator_admin_id = admins.id
             WHERE pages.created_by IS NOT NULL
               AND pages.creator_admin_id IS NULL'
        );

        DB::statement(
            'UPDATE pages
             INNER JOIN admins ON admins.legacy_user_id = pages.updated_by
             SET pages.editor_admin_id = admins.id
             WHERE pages.updated_by IS NOT NULL
               AND pages.editor_admin_id IS NULL'
        );

        DB::statement(
            'UPDATE purchase_orders
             INNER JOIN admins ON admins.legacy_user_id = purchase_orders.created_by
             SET purchase_orders.admin_id = admins.id
             WHERE purchase_orders.created_by IS NOT NULL
               AND purchase_orders.admin_id IS NULL'
        );

        DB::statement(
            'UPDATE inventory_histories
             INNER JOIN admins ON admins.legacy_user_id = inventory_histories.user_id
             SET inventory_histories.admin_id = admins.id
             WHERE inventory_histories.user_id IS NOT NULL
               AND inventory_histories.admin_id IS NULL'
        );
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
