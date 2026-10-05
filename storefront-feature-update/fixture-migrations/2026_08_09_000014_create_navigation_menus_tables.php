<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if(!Schema::hasTable('navigation_menus')) Schema::create('navigation_menus',function(Blueprint $table):void{$table->id();$table->string('name');$table->string('location',40)->unique();$table->boolean('is_active')->default(true);$table->timestamps();});
        if(!Schema::hasTable('navigation_menu_items')) Schema::create('navigation_menu_items',function(Blueprint $table):void{$table->id();$table->foreignId('navigation_menu_id')->constrained()->cascadeOnDelete();$table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();$table->string('label');$table->string('link_type',30)->default('custom');$table->string('url')->nullable();$table->string('route_name')->nullable();$table->unsignedInteger('position')->default(0);$table->boolean('is_active')->default(true);$table->boolean('open_in_new_tab')->default(false);$table->timestamps();$table->index(['navigation_menu_id','is_active','position'],'nav_items_menu_active_position_idx');});
        $now=now(); foreach([['Header Navigation','header'],['Footer Navigation','footer']] as [$name,$location]) DB::table('navigation_menus')->updateOrInsert(['location'=>$location],['name'=>$name,'is_active'=>true,'created_at'=>$now,'updated_at'=>$now]);
    }
    public function down(): void { Schema::dropIfExists('navigation_menu_items');Schema::dropIfExists('navigation_menus'); }
};
