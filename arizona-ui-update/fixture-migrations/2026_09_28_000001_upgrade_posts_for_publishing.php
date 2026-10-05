<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->after('expert');
            $table->longText('content')->nullable()->after('excerpt');

            $table->foreignId('primary_category_id')
                ->nullable()
                ->after('template')
                ->constrained('categories')
                ->nullOnDelete();

            $table->foreignId('author_id')
                ->nullable()
                ->after('primary_category_id')
                ->constrained('admins')
                ->nullOnDelete();

            $table->string('status', 30)
                ->default('draft')
                ->after('author_id');

            $table->timestamp('published_at')
                ->nullable()
                ->after('status');

            $table->timestamp('scheduled_at')
                ->nullable()
                ->after('published_at');

            $table->string('feature_image_alt')
                ->nullable()
                ->after('feature_image');

            $table->string('canonical_url')
                ->nullable()
                ->after('meta_description');

            $table->boolean('robots_index')
                ->default(true)
                ->after('canonical_url');

            $table->boolean('robots_follow')
                ->default(true)
                ->after('robots_index');

            $table->string('og_title')
                ->nullable()
                ->after('robots_follow');

            $table->text('og_description')
                ->nullable()
                ->after('og_title');

            $table->string('og_image')
                ->nullable()
                ->after('og_description');

            $table->index(['status', 'published_at'], 'posts_status_published_index');
            $table->index('scheduled_at', 'posts_scheduled_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_status_published_index');
            $table->dropIndex('posts_scheduled_at_index');

            $table->dropForeign(['primary_category_id']);
            $table->dropForeign(['author_id']);

            $table->dropColumn([
                'excerpt',
                'content',
                'primary_category_id',
                'author_id',
                'status',
                'published_at',
                'scheduled_at',
                'feature_image_alt',
                'canonical_url',
                'robots_index',
                'robots_follow',
                'og_title',
                'og_description',
                'og_image',
            ]);
        });
    }
};
