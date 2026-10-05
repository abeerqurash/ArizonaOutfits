<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up(): void {
  Schema::create('catalog_order_state', function(Blueprint $table) { $table->unsignedInteger('id')->primary(); $table->unsignedBigInteger('generation')->default(1); });
  DB::table('catalog_order_state')->insert(['id'=>1,'generation'=>1]);
 }
 public function down(): void { Schema::dropIfExists('catalog_order_state'); }
};
