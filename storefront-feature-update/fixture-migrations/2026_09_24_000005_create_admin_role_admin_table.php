<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admin_role_admin')) {
            Schema::create('admin_role_admin', function (Blueprint $table) {
                $table->unsignedBigInteger('admin_role_id');
                $table->unsignedBigInteger('admin_id');
                $table->timestamps();

                $table->primary(['admin_role_id', 'admin_id'], 'admin_role_admin_primary');

                $table->foreign('admin_role_id')
                    ->references('id')->on('admin_roles')->cascadeOnDelete();

                $table->foreign('admin_id')
                    ->references('id')->on('admins')->cascadeOnDelete();
            });
        }

        $assignments = DB::table('admin_role_user')
            ->join('admins', 'admins.legacy_user_id', '=', 'admin_role_user.user_id')
            ->select([
                'admin_role_user.admin_role_id',
                'admins.id as admin_id',
                'admin_role_user.created_at',
                'admin_role_user.updated_at',
            ])
            ->get();

        foreach ($assignments as $assignment) {
            DB::table('admin_role_admin')->updateOrInsert(
                [
                    'admin_role_id' => $assignment->admin_role_id,
                    'admin_id' => $assignment->admin_id,
                ],
                [
                    'created_at' => $assignment->created_at ?? now(),
                    'updated_at' => $assignment->updated_at ?? now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_role_admin');
    }
};
