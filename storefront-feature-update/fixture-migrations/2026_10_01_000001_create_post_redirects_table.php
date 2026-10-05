<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_redirects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnDelete();

            $table->string('old_slug')->unique();

            $table->timestamps();

            $table->index('post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_redirects');
    }
};
