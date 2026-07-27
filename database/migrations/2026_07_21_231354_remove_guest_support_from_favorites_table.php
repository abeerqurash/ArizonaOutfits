<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('favorites')
            ->whereNull('user_id')
            ->delete();

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropColumn('session_id');
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable(false)
                ->change();

            $table->unique([
                'user_id',
                'product_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropUnique([
                'user_id',
                'product_id',
            ]);

            $table->foreignId('user_id')
                ->nullable()
                ->change();

            $table->string('session_id')
                ->nullable()
                ->after('product_id');
        });
    }
};