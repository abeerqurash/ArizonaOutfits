<?php
require __DIR__.'/render-check.php';
$count=0;$failures=[];
$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__).'/resources/views'));
foreach($iterator as $file){if(!$file->isFile()||!str_ends_with($file->getFilename(),'.blade.php'))continue;try{app('blade.compiler')->compileString(file_get_contents($file->getPathname()));$count++;}catch(Throwable $e){$failures[]=$file->getPathname().': '.$e->getMessage();}}
foreach(glob(__DIR__.'/_staged/resources/views/*/layouts/*.blade.php') as $file){app('blade.compiler')->compileString(file_get_contents($file));$count++;}
file_put_contents(__DIR__.'/blade-checks.json',json_encode(['compiled'=>$count,'failures'=>$failures],JSON_PRETTY_PRINT));echo "\nCompiled $count templates; ".count($failures)." failures.\n";
