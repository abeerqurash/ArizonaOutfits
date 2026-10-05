<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void { Schema::table('ecommerce_settings',function(Blueprint $t){$t->string('facebook_url',1000)->nullable();$t->string('instagram_url',1000)->nullable();$t->string('linkedin_url',1000)->nullable();$t->string('youtube_url',1000)->nullable();$t->string('x_url',1000)->nullable();$t->string('footer_copyright')->nullable();$t->text('footer_about')->nullable();}); }
 public function down():void { Schema::table('ecommerce_settings',function(Blueprint $t){$t->dropColumn(['facebook_url','instagram_url','linkedin_url','youtube_url','x_url','footer_copyright','footer_about']);}); }
};
