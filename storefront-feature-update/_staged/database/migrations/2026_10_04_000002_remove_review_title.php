<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { if(Schema::hasColumn('reviews','title')) Schema::table('reviews',fn(Blueprint $table)=>$table->dropColumn('title')); }
 public function down(): void { if(!Schema::hasColumn('reviews','title')) Schema::table('reviews',fn(Blueprint $table)=>$table->string('title')->nullable()); }
};
