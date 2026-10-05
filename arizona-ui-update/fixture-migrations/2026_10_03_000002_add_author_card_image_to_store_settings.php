<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void{Schema::table('ecommerce_settings',function(Blueprint $table){$table->string('author_card_image')->nullable();});}
 public function down():void{Schema::table('ecommerce_settings',function(Blueprint $table){$table->dropColumn('author_card_image');});}
};
