<?php
require __DIR__.'/bootstrap.php';
$paths=json_decode(file_get_contents(__DIR__.'/../../optimization-round-2/changed.json'),true);$count=0;
foreach($paths as $path){if(str_ends_with($path,'.blade.php')){$app['blade.compiler']->compileString(file_get_contents(__DIR__.'/../../optimization-round-2/_staged/'.$path));$count++;}}
echo "Compiled $count replacement Blade templates.\n";
