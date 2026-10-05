<?php
require __DIR__.'/bootstrap.php';
$manifest=json_decode(file_get_contents(__DIR__.'/../manifest.json'),true);$rows=[];
foreach($manifest as $entry){if(!str_ends_with($entry['destination'],'.blade.php'))continue;
 $text=file_get_contents(__DIR__.'/../_staged/'.$entry['destination']);preg_match_all('/route\(\s*[\x22\x27]([^\x22\x27]+)[\x22\x27]/',$text,$matches);
 foreach(array_unique($matches[1])as $name)$rows[]=['file'=>$entry['destination'],'route'=>$name,'pass'=>app('router')->getRoutes()->getByName($name)!==null];
}
file_put_contents(__DIR__.'/../all-view-route-checks.json',json_encode($rows,JSON_PRETTY_PRINT));echo count($rows).' view route references; '.count(array_filter($rows,fn($r)=>!$r['pass']))." unresolved.\n";