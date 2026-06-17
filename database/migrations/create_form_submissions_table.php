<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();

            $table->string('form_source'); // home, contact, blog

            $table->string('form_type')->nullable(); // Contact Us, PR

            $table->string('full_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            $table->string('subject')->nullable();

            $table->string('email');
            $table->string('phone')->nullable();

            $table->longText('message')->nullable();

            $table->string('blog_title')->nullable();
            $table->string('blog_slug')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};