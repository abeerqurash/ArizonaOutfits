<?php
$a=__DIR__;$project=dirname(__DIR__);$staged=$a.'/replacement-files/_staged';require $a.'/dependencies/vendor/autoload.php';spl_autoload_register(function($class)use($project,$staged){if(str_starts_with($class,'App\\')){$path='/app/'.str_replace('\\','/',substr($class,4)).'.php';$file=is_file($staged.$path)?$staged.$path:$project.$path;if(is_file($file))require $file;}},true,true);
$app=require $project.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['filesystems.disks.public.root'=>$a.'/preview-storage','cache.default'=>'array','logging.default'=>'preview_audit','logging.channels.preview_audit'=>['driver'=>'single','path'=>$a.'/preview.log']]);
$root=realpath(Illuminate\Support\Facades\Storage::disk('public')->path(''));
if($root!==realpath($a.'/preview-storage'))throw new RuntimeException('Preview storage isolation failed.');echo 'Writes restricted to copied preview uploads: '.$root.PHP_EOL;
Illuminate\Support\Facades\Artisan::registerCommand(new App\Console\Commands\OptimizePublicMedia);
$exit=Illuminate\Support\Facades\Artisan::call('media:optimize');echo Illuminate\Support\Facades\Artisan::output();exit($exit);
