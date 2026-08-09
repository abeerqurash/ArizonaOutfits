<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add reorder planning fields.
     */
    public function up(): void
    {
        Schema::table(
            'products',
            function (Blueprint $table): void {
                $table
                    ->unsignedInteger('reorder_point')
                    ->nullable()
                    ->after('stock');

                $table
                    ->unsignedInteger('reorder_quantity')
                    ->nullable()
                    ->after('reorder_point');
            }
        );

        /*
         * Product variants may have their own independent stock.
         */
        if (Schema::hasTable('product_variants')) {
            Schema::table(
                'product_variants',
                function (Blueprint $table): void {
                    $table
                        ->unsignedInteger('reorder_point')
                        ->nullable()
                        ->after('stock');

                    $table
                        ->unsignedInteger('reorder_quantity')
                        ->nullable()
                        ->after('reorder_point');
                }
            );
        }
    }

    /**
     * Remove reorder planning fields.
     */
    public function down(): void
    {
        if (Schema::hasTable('product_variants')) {
            Schema::table(
                'product_variants',
                function (Blueprint $table): void {
                    $table->dropColumn([
                        'reorder_point',
                        'reorder_quantity',
                    ]);
                }
            );
        }

        Schema::table(
            'products',
            function (Blueprint $table): void {
                $table->dropColumn([
                    'reorder_point',
                    'reorder_quantity',
                ]);
            }
        );
    }
};