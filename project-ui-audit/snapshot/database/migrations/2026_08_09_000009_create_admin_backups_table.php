<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admin_backups')) {
            Schema::create('admin_backups', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('name');
                $table->string('type', 30);
                $table->string('disk', 50)->default('local');
                $table->string('file_path');
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('status', 30)->default('processing');
                $table->unsignedInteger('table_count')->default(0);
                $table->unsignedBigInteger('row_count')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'created_at']);
                $table->index(['type', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_backups');
    }
};
