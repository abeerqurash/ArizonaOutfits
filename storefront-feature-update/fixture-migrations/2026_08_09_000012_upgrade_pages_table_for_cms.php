<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            if (!Schema::hasColumn('pages','title')) $table->string('title')->after('id');
            if (!Schema::hasColumn('pages','slug')) $table->string('slug')->unique()->after('title');
            if (!Schema::hasColumn('pages','excerpt')) $table->text('excerpt')->nullable()->after('slug');
            if (!Schema::hasColumn('pages','content')) $table->longText('content')->nullable()->after('excerpt');
            if (!Schema::hasColumn('pages','status')) $table->string('status',30)->default('draft')->index()->after('content');
            if (!Schema::hasColumn('pages','template')) $table->string('template',60)->default('default')->after('status');
            if (!Schema::hasColumn('pages','meta_title')) $table->string('meta_title')->nullable()->after('template');
            if (!Schema::hasColumn('pages','meta_description')) $table->text('meta_description')->nullable()->after('meta_title');
            if (!Schema::hasColumn('pages','published_at')) $table->timestamp('published_at')->nullable()->index()->after('meta_description');
            if (!Schema::hasColumn('pages','created_by')) $table->foreignId('created_by')->nullable()->after('published_at')->constrained('users')->nullOnDelete();
            if (!Schema::hasColumn('pages','updated_by')) $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            foreach (['created_by','updated_by'] as $column) if (Schema::hasColumn('pages',$column)) $table->dropConstrainedForeignId($column);
            $columns=['title','slug','excerpt','content','status','template','meta_title','meta_description','published_at'];
            foreach ($columns as $column) if (Schema::hasColumn('pages',$column)) $table->dropColumn($column);
        });
    }
};
