<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('pages',function(Blueprint $table):void { if(!Schema::hasColumn('pages','render_mode'))$table->string('render_mode',30)->default('editor')->after('template'); if(!Schema::hasColumn('pages','blade_template'))$table->string('blade_template')->nullable()->after('render_mode'); }); }
    public function down(): void { Schema::table('pages',function(Blueprint $table):void { if(Schema::hasColumn('pages','blade_template'))$table->dropColumn('blade_template'); if(Schema::hasColumn('pages','render_mode'))$table->dropColumn('render_mode'); }); }
};
