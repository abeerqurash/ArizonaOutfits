<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_revisions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnDelete();

            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->unsignedInteger('revision_number');

            /*
             * Complete pre-update snapshot of the post and its category
             * assignments. JSON keeps the revision system flexible as the
             * Blog CMS gains additional fields later.
             */
            $table->json('snapshot');

            $table->timestamps();

            $table->unique(
                ['post_id', 'revision_number'],
                'post_revisions_post_revision_unique'
            );

            $table->index(['post_id', 'created_at']);
            $table->index('admin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_revisions');
    }
};
