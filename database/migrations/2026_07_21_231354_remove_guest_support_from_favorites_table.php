<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('favorites')) {
            return;
        }

        if (Schema::hasColumn('favorites', 'user_id')) {
            DB::table('favorites')
                ->whereNull('user_id')
                ->delete();
        }

        if (Schema::hasColumn('favorites', 'session_id')) {
            Schema::table('favorites', function (Blueprint $table): void {
                $table->dropColumn('session_id');
            });
        }

        if (Schema::hasColumn('favorites', 'user_id')) {
            Schema::table('favorites', function (Blueprint $table): void {
                $table->foreignId('user_id')
                    ->nullable(false)
                    ->change();
            });
        }

        if (
            Schema::hasColumn('favorites', 'user_id')
            && Schema::hasColumn('favorites', 'product_id')
            && !Schema::hasIndex(
                'favorites',
                ['user_id', 'product_id'],
                'unique'
            )
        ) {
            Schema::table('favorites', function (Blueprint $table): void {
                $table->unique([
                    'user_id',
                    'product_id',
                ]);
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('favorites')) {
            return;
        }

        if (
            Schema::hasColumn('favorites', 'user_id')
            && Schema::hasColumn('favorites', 'product_id')
            && Schema::hasIndex(
                'favorites',
                ['user_id', 'product_id'],
                'unique'
            )
        ) {
            Schema::table('favorites', function (Blueprint $table): void {
                $table->dropUnique([
                    'user_id',
                    'product_id',
                ]);
            });
        }

        if (Schema::hasColumn('favorites', 'user_id')) {
            Schema::table('favorites', function (Blueprint $table): void {
                $table->foreignId('user_id')
                    ->nullable()
                    ->change();
            });
        }

        if (!Schema::hasColumn('favorites', 'session_id')) {
            Schema::table('favorites', function (Blueprint $table): void {
                $table->string('session_id')
                    ->nullable()
                    ->after('product_id');
            });
        }
    }
};
