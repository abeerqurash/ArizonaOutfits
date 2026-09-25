<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('status')->default('active');
                $table->boolean('is_super_admin')->default(false);
                $table->unsignedBigInteger('legacy_user_id')->nullable()->unique();
                $table->rememberToken();
                $table->timestamps();
                $table->index('status');
            });
        }

        $legacyAdmins = DB::table('users')
            ->where('is_admin', 1)
            ->whereNotNull('email')
            ->whereNotNull('password')
            ->get();

        foreach ($legacyAdmins as $admin) {
            DB::table('admins')->updateOrInsert(
                ['legacy_user_id' => $admin->id],
                [
                    'name' => $admin->name,
                    'email' => mb_strtolower(trim($admin->email)),
                    'email_verified_at' => $admin->email_verified_at,
                    'password' => $admin->password,
                    'status' => $admin->status ?? 'active',
                    'is_super_admin' => (bool)($admin->is_super_admin ?? false),
                    'remember_token' => null,
                    'created_at' => $admin->created_at ?? now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
