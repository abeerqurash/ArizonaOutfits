<?php
require __DIR__.'/bootstrap.php';
$manifest=json_decode(file_get_contents(__DIR__.'/../manifest.json'),true);$results=[];
foreach($manifest as $entry){
 if(!str_ends_with($entry['destination'],'.blade.php'))continue;
 $source=__DIR__.'/../_staged/'.$entry['destination'];$compiled=app('blade.compiler')->compileString(file_get_contents($source));
 $target=__DIR__.'/compiled/syntax-'.md5($entry['destination']).'.php';file_put_contents($target,$compiled);
 exec('C:\\xampp\\php\\php.exe -l '.escapeshellarg($target).' 2>&1',$output,$code);
 $results[]=['path'=>$entry['destination'],'pass'=>$code===0,'output'=>implode("\n",$output)];$output=[];
}
file_put_contents(__DIR__.'/../blade-syntax-checks.json',json_encode($results,JSON_PRETTY_PRINT));echo 'BLADE '.count($results).' compiled; '.count(array_filter($results,fn($r)=>!$r['pass']))." errors.\n";
