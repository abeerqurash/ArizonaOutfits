<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_backups', function (Blueprint $table) {
            $table->foreignId('admin_id')
                ->nullable()
                ->after('created_by')
                ->constrained('admins')
                ->nullOnDelete();
        });

        Schema::table('email_templates', function (Blueprint $table) {
            $table->foreignId('admin_id')
                ->nullable()
                ->after('updated_by')
                ->constrained('admins')
                ->nullOnDelete();
        });

        Schema::table('order_notes', function (Blueprint $table) {
            $table->foreignId('admin_id')
                ->nullable()
                ->after('user_id')
                ->constrained('admins')
                ->nullOnDelete();
        });

        Schema::table('order_activities', function (Blueprint $table) {
            $table->foreignId('admin_id')
                ->nullable()
                ->after('user_id')
                ->constrained('admins')
                ->nullOnDelete();
        });

        $this->backfill('admin_backups', 'created_by');
        $this->backfill('email_templates', 'updated_by');
        $this->backfill('order_notes', 'user_id');
        $this->backfill('order_activities', 'user_id');
    }

    public function down(): void
    {
        Schema::table('order_activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_id');
        });

        Schema::table('order_notes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_id');
        });

        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_id');
        });

        Schema::table('admin_backups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_id');
        });
    }

    private function backfill(string $table, string $legacyColumn): void
    {
        DB::table($table)
            ->join('admins', 'admins.legacy_user_id', '=', $table . '.' . $legacyColumn)
            ->whereNull($table . '.admin_id')
            ->update([
                $table . '.admin_id' => DB::raw('admins.id'),
            ]);
    }
};
