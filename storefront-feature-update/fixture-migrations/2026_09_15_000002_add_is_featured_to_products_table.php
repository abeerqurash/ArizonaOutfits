<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table
                ->boolean('is_featured')
                ->default(false)
                ->after('status')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex([
                'is_featured',
            ]);

            $table->dropColumn(
                'is_featured'
            );
        });
    }
};