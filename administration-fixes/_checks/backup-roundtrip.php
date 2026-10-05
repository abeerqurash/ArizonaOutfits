<?php
require __DIR__.'/bootstrap.php';
use Illuminate\Support\Facades\{DB,Schema,Storage};
use Illuminate\Database\Schema\Blueprint;
$configuration=config('database.connections.mysql');$server=DB::connection('mysql')->getPdo();
$source='codex_arizona_backup_'.date('Ymd').'_'.bin2hex(random_bytes(5));$restore=$source.'_restore';
$created=[];$oldDefault=config('database.default');$oldStorage=$app->storagePath();$oldPublic=$app->publicPath();
try{
 foreach([$source,$restore]as $name){if(!preg_match('/^codex_arizona_backup_\d{8}_[a-f0-9]{10}(?:_restore)?$/D',$name))throw new RuntimeException('Invalid fixture database name.');$server->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');$created[]=$name;}
 foreach(['backup_fixture'=>$source,'restore_fixture'=>$restore]as $name=>$database){config(['database.connections.'.$name=>array_merge($configuration,['database'=>$database])]);DB::purge($name);}
 config(['database.default'=>'backup_fixture']);DB::setDefaultConnection('backup_fixture');
 Schema::create('users',fn(Blueprint $t)=>$t->id());Schema::create('admins',fn(Blueprint $t)=>$t->id());
 (require __DIR__.'/../../database/migrations/2026_08_09_000009_create_admin_backups_table.php')->up();
 Schema::table('admin_backups',fn(Blueprint $t)=>$t->unsignedBigInteger('admin_id')->nullable());
 DB::statement('CREATE TABLE fixture_items (id INT PRIMARY KEY, label TEXT, raw_bytes BLOB, quantity INT, doubled INT AS (quantity * 2) STORED) ENGINE=InnoDB');
 DB::table('fixture_items')->insert([['id'=>0,'label'=>"Quotes ' \" \\ and Unicode سلام",'raw_bytes'=>"\x00\x01\xff",'quantity'=>3],['id'=>1,'label'=>null,'raw_bytes'=>null,'quantity'=>5]]);
 // ZIP paths are isolated; no real upload directories are archived.
 $app->useStoragePath(__DIR__.'/files/backup-storage');$app->usePublicPath(__DIR__.'/files/backup-public');
 @mkdir(storage_path('app/public'),0777,true);@mkdir(public_path('uploads'),0777,true);
 file_put_contents(storage_path('app/public/fixture.txt'),'storage fixture');file_put_contents(public_path('uploads/fixture.txt'),'upload fixture');
 Carbon\Carbon::setTestNow('2026-10-02 12:00:00');$service=app(App\Services\AdminBackupService::class);
 $backup=$service->create('database',null);$next=$service->create('database',null);
 check('Actual MariaDB exporter completes',$backup->status==='completed' && $backup->table_count===4 && $backup->row_count>=2);
 check('Two successful same-second backups remain separate',$backup->file_path!==$next->file_path && Storage::disk('local')->exists($backup->file_path) && Storage::disk('local')->exists($next->file_path));
 $sql=gzdecode(Storage::disk('local')->get($backup->file_path));
 check('Dump uses explicit writable column names',str_contains($sql,'INSERT INTO `fixture_items` (`id`,`label`,`raw_bytes`,`quantity`)'));
 DB::connection('restore_fixture')->getPdo()->exec($sql);
 $expected=DB::table('fixture_items')->orderBy('id')->get()->toArray();$actual=DB::connection('restore_fixture')->table('fixture_items')->orderBy('id')->get()->toArray();
 check('Dump restores text, Unicode, NULL, binary and generated values',$expected==$actual);
 check('Zero primary key survives restoration',(int)$actual[0]->id===0);
 $full=$service->create('full',null);$zip=new ZipArchive;$zip->open(Storage::disk('local')->path($full->file_path));
 check('Full backup completes and contains database',$full->status==='completed' && $zip->locateName('database/database.sql.gz')!==false);
 check('Full backup includes both isolated upload roots',$zip->getFromName('files/storage/fixture.txt')==='storage fixture' && $zip->getFromName('files/public-uploads/fixture.txt')==='upload fixture');
 check('Full backup includes restore instructions',$zip->locateName('RESTORE-INSTRUCTIONS.txt')!==false);$zip->close();
}catch(Throwable $exception){$results[]=['result'=>'ERROR','check'=>'MariaDB isolated round trip','detail'=>$exception->getMessage()];echo get_class($exception).' '.$exception->getMessage()."\n";}
finally{
 Carbon\Carbon::setTestNow();config(['database.default'=>$oldDefault]);DB::setDefaultConnection($oldDefault);DB::purge('backup_fixture');DB::purge('restore_fixture');$app->useStoragePath($oldStorage);$app->usePublicPath($oldPublic);
 foreach(array_reverse($created)as $name){if(!preg_match('/^codex_arizona_backup_\d{8}_[a-f0-9]{10}(?:_restore)?$/D',$name))throw new RuntimeException('Refusing fixture cleanup.');$server->exec('DROP DATABASE `'.$name.'`');}
}
file_put_contents(__DIR__.'/../backup-roundtrip-checks.json',json_encode($results,JSON_PRETTY_PRINT));echo 'BACKUP '.count($results).' checks; '.count(array_filter($results,fn($r)=>$r['result']!=='PASS'))." failures. Only temporary isolated databases and fixture uploads were used; fixture databases removed.\n";
