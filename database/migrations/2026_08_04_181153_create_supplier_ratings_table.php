<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('supplier_ratings')) {
            return;
        }

        if (!Schema::hasColumn('supplier_ratings', 'title')) {
            Schema::table(
                'supplier_ratings',
                function (Blueprint $table): void {
                    $table->string('title')
                        ->nullable()
                        ->after('overall_rating');
                }
            );
        }

        if (!Schema::hasColumn(
            'supplier_ratings',
            'would_recommend'
        )) {
            Schema::table(
                'supplier_ratings',
                function (Blueprint $table): void {
                    $table->boolean('would_recommend')
                        ->default(false)
                        ->after('review');
                }
            );
        }

        if (!Schema::hasColumn('supplier_ratings', 'rated_at')) {
            Schema::table(
                'supplier_ratings',
                function (Blueprint $table): void {
                    $table->timestamp('rated_at')
                        ->nullable()
                        ->after('rated_by');
                }
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('supplier_ratings')) {
            return;
        }

        $columns = collect([
            'title',
            'would_recommend',
            'rated_at',
        ])->filter(
            fn (string $column): bool =>
                Schema::hasColumn(
                    'supplier_ratings',
                    $column
                )
        )->values()->all();

        if ($columns !== []) {
            Schema::table(
                'supplier_ratings',
                function (Blueprint $table) use (
                    $columns
                ): void {
                    $table->dropColumn($columns);
                }
            );
        }
    }
};
