<?php
require __DIR__.'/../customer-dashboard-files/_checks/bootstrap.php';
$compiler=app('blade.compiler');
foreach(['partials/header','admin/navigation-menus/index'] as $view){$path=__DIR__.'/_staged/resources/views/'.$view.'.blade.php';$compiled=$compiler->compileString(file_get_contents($path));$target=__DIR__.'/'.str_replace('/','-',$view).'.compiled.php';file_put_contents($target,$compiled);passthru('C:\\xampp\\php\\php.exe -l '.escapeshellarg($target),$code);if($code)exit($code);}
echo 'PASS: both Blade views compile.'.PHP_EOL;
