<?php
require __DIR__.'/../customer-dashboard-files/_checks/bootstrap.php';
$manifest=json_decode(file_get_contents(__DIR__.'/manifest.json'),true);$count=0;
foreach($manifest as $i=>$item){$p=__DIR__.'/_staged/'.$item['destination'];if(str_ends_with($p,'.blade.php')){$p2=__DIR__.'/compiled-'.$i.'.php';file_put_contents($p2,app('blade.compiler')->compileString(file_get_contents($p)));$p=$p2;}elseif(!str_ends_with($p,'.php'))continue;exec('C:\\xampp\\php\\php.exe -l '.escapeshellarg($p),$output,$code);if($code){echo implode(PHP_EOL,$output);exit(1);}$count++;unset($output);}
echo 'PASS: '.$count.' PHP / compiled Blade syntax checks.'.PHP_EOL;
