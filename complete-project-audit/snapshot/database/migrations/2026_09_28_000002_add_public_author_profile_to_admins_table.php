<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('profile_image')->nullable()->after('phone');
            $table->string('author_title')->nullable()->after('profile_image');

            $table->string('facebook_url', 2048)->nullable()->after('author_title');
            $table->string('instagram_url', 2048)->nullable()->after('facebook_url');
            $table->string('x_url', 2048)->nullable()->after('instagram_url');
            $table->string('linkedin_url', 2048)->nullable()->after('x_url');
            $table->string('youtube_url', 2048)->nullable()->after('linkedin_url');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn([
                'profile_image',
                'author_title',
                'facebook_url',
                'instagram_url',
                'x_url',
                'linkedin_url',
                'youtube_url',
            ]);
        });
    }
};
