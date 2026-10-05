<?php
require __DIR__.'/../customer-dashboard-files/_checks/bootstrap.php';
foreach(['admin/settings/edit','partials/blog-author-card-info'] as $name){$source=__DIR__.'/_staged/resources/views/'.$name.'.blade.php';$out=__DIR__.'/'.str_replace('/','-',$name).'.compiled.php';file_put_contents($out,app('blade.compiler')->compileString(file_get_contents($source)));passthru('C:\\xampp\\php\\php.exe -l '.escapeshellarg($out));}
$migration=require __DIR__.'/_staged/database/migrations/2026_10_03_000002_add_author_card_image_to_store_settings.php';$migration->up();
echo Illuminate\Support\Facades\Schema::hasColumn('ecommerce_settings','author_card_image')?'PASS: image column migration.':'FAIL';
