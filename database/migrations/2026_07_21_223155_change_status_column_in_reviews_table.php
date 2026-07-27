<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Convert existing boolean values
        |--------------------------------------------------------------------------
        |
        | Existing true values become approved.
        | Existing false values become pending.
        |
        */

        if (Schema::hasColumn('reviews', 'status')) {
            DB::table('reviews')
                ->where('status', 1)
                ->update([
                    'status' => 'approved',
                ]);

            DB::table('reviews')
                ->where(function ($query) {
                    $query
                        ->where('status', 0)
                        ->orWhereNull('status');
                })
                ->update([
                    'status' => 'pending',
                ]);
        }

        Schema::table('reviews', function (Blueprint $table) {
            $table->string(
                'status',
                20
            )
                ->default('pending')
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('reviews')
            ->where('status', 'approved')
            ->update([
                'status' => 1,
            ]);

        DB::table('reviews')
            ->whereIn(
                'status',
                [
                    'pending',
                    'rejected',
                ]
            )
            ->update([
                'status' => 0,
            ]);

        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('status')
                ->default(false)
                ->change();
        });
    }
};