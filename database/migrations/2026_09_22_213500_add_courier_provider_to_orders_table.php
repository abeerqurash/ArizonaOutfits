<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'courier_provider')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('courier_provider', 100)
                    ->nullable()
                    ->after('courier');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'courier_provider')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('courier_provider');
            });
        }
    }
};
