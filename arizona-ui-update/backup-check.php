<?php
require dirname(__DIR__).'/vendor/autoload.php';
require __DIR__.'/_staged/app/Services/AdminBackupService.php';
$fixture=__DIR__.'/backup-fixture';
foreach(['app','vendor','public/asset','storage/app/private','storage/app/private/admin-backups','storage/app/admin-backups','node_modules','storage/logs','.git'] as $path){if(!is_dir($fixture.'/'.$path))mkdir($fixture.'/'.$path,0777,true);file_put_contents($fixture.'/'.$path.'/fixture.txt','Fixture only');}
file_put_contents($fixture.'/.env','FIXTURE_ONLY=true');
$archive=__DIR__.'/backup-fixture.zip';$zip=new ZipArchive;$zip->open($archive,ZipArchive::CREATE|ZipArchive::OVERWRITE);
$method=new ReflectionMethod(App\Services\AdminBackupService::class,'addWebsite');$method->invoke(new App\Services\AdminBackupService,$zip,$fixture,'website');$zip->close();
$zip=new ZipArchive;$zip->open($archive);
foreach(['website/app/fixture.txt','website/vendor/fixture.txt','website/public/asset/fixture.txt','website/storage/app/private/fixture.txt','website/.env'] as $path)if($zip->locateName($path)===false)throw new RuntimeException('Missing '.$path);
foreach(['website/.git/fixture.txt','website/node_modules/fixture.txt','website/storage/logs/fixture.txt','website/storage/app/private/admin-backups/fixture.txt','website/storage/app/admin-backups/fixture.txt'] as $path)if($zip->locateName($path)!==false)throw new RuntimeException('Unexpected '.$path);
$zip->close();echo "Website archive fixture: required files included; previous archives, dependencies cache and logs excluded.\n";
