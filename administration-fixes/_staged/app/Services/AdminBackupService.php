<?php

namespace App\Services;

use App\Models\AdminBackup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class AdminBackupService
{
    public function create(string $type, ?int $adminId): AdminBackup
    {
        $stamp = now()->format('Y-m-d_H-i-s') . '_' . \Illuminate\Support\Str::uuid();
        if (!in_array($type, ['database','full'], true)) throw new RuntimeException('Invalid backup type.');
        $extension = $type === 'full' ? 'zip' : 'sql.gz';
        $name = "arizona-outfits_{$type}_{$stamp}.{$extension}";
        $path = 'admin-backups/' . $name;

        $backup = AdminBackup::create([
            'admin_id' => $adminId,
            'created_by' => null,
            'name' => $name,
            'type' => $type,
            'disk' => 'local',
            'file_path' => $path,
            'status' => 'processing',
        ]);

        $temporarySql = Storage::disk('local')->path('admin-backups/temp-' . $backup->id . '-' . $stamp . '.sql.gz');

        try {
            File::ensureDirectoryExists(dirname($temporarySql));
            [$tables, $rows] = $this->writeDatabaseDump($temporarySql);

            if ($type === 'full') {
                $this->writeFullArchive(
                    Storage::disk('local')->path($path),
                    $temporarySql
                );
                File::delete($temporarySql);
            } else {
                File::move(
                    $temporarySql,
                    Storage::disk('local')->path($path)
                );
            }

            $backup->update([
                'status' => 'completed',
                'file_size' => Storage::disk('local')->size($path),
                'table_count' => $tables,
                'row_count' => $rows,
                'completed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            File::delete($temporarySql);
            Storage::disk('local')->delete($path);

            $backup->update([
                'status' => 'failed',
                'error_message' => mb_substr(
                    $exception->getMessage(),
                    0,
                    2000
                ),
            ]);

            throw $exception;
        }

        return $backup->fresh(['creator', 'legacyCreator']);
    }

    private function writeDatabaseDump(string $path): array
    {
        $source=DB::connection();
        if(!in_array($source->getDriverName(),['mysql','mariadb'],true))throw new RuntimeException('The backup exporter supports MySQL and MariaDB.');
        $name='administration_backup_snapshot';config(['database.connections.'.$name=>$source->getConfig()]);DB::purge($name);
        $connection=DB::connection($name);$pdo=$connection->getPdo();$stream=gzopen($path,'wb9');
        if($stream===false)throw new RuntimeException('Unable to open the backup file.');
        $write=function(string $sql)use($stream):void{if(gzwrite($stream,$sql)!==strlen($sql))throw new RuntimeException('Unable to finish writing the backup.');};
        $quote=fn(string $identifier):string=>'`'.str_replace('`','``',$identifier).'`';$rows=0;$count=0;
        try{
            $pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT');
            $tables=$connection->select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            $sqlMode=(string)$pdo->query('SELECT @@SESSION.sql_mode')->fetchColumn();
            $sqlMode=implode(',',array_unique(array_filter(array_merge(explode(',',$sqlMode),['NO_AUTO_VALUE_ON_ZERO']))));
            $write("-- Arizona Outfits database backup\n-- Created: ".now()->toDateTimeString()."\nSET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\nSET SQL_MODE=".$pdo->quote($sqlMode).";\n\n");
            foreach($tables as $record){
                $table=array_values((array)$record)[0];$quoted=$quote($table);
                $create=array_values((array)$connection->select('SHOW CREATE TABLE '.$quoted)[0])[1];
                $columns=collect($connection->select('SHOW FULL COLUMNS FROM '.$quoted))->filter(fn($column)=>!preg_match('/(?:VIRTUAL|STORED) GENERATED/i',$column->Extra))->pluck('Field')->all();
                $primary=collect($connection->select('SHOW KEYS FROM '.$quoted." WHERE Key_name = 'PRIMARY'"))->sortBy('Seq_in_index')->pluck('Column_name')->all();
                $list=implode(',',array_map($quote,$columns));
                $write('DROP TABLE IF EXISTS '.$quoted.";\n".$create.";\n\n");
                // Use a dedicated unbuffered connection: no live business transaction is changed.
                $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,false);
                $statement=$pdo->query('SELECT '.$list.' FROM '.$quoted.($primary?' ORDER BY '.implode(',',array_map($quote,$primary)):''));
                while($row=$statement->fetch(\PDO::FETCH_NUM)){
                    $values=array_map(fn($value)=>$value===null?'NULL':$pdo->quote((string)$value),$row);
                    $write('INSERT INTO '.$quoted.' ('.$list.') VALUES ('.implode(',',$values).");\n");$rows++;
                }
                $statement->closeCursor();$pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);$count++;$write("\n");
            }
            $write("SET FOREIGN_KEY_CHECKS=1;\n");$pdo->exec('COMMIT');
            if(!gzclose($stream))throw new RuntimeException('Unable to close the backup stream.');$stream=null;
            return [$count,$rows];
        }catch(Throwable $exception){try{$pdo->exec('ROLLBACK');}catch(Throwable){}throw $exception;}
        finally{if(is_resource($stream))gzclose($stream);DB::purge($name);}
    }

    private function writeFullArchive(
        string $destination,
        string $databaseDump
    ): void {
        $zip = new ZipArchive();

        if (
            $zip->open(
                $destination,
                ZipArchive::CREATE | ZipArchive::OVERWRITE
            ) !== true
        ) {
            throw new RuntimeException(
                'Unable to create the ZIP backup archive.'
            );
        }

        $zip->addFile(
            $databaseDump,
            'database/database.sql.gz'
        );

        $this->addDirectory(
            $zip,
            storage_path('app/public'),
            'files/storage'
        );

        $this->addDirectory(
            $zip,
            public_path('uploads'),
            'files/public-uploads'
        );

        $zip->addFromString(
            'RESTORE-INSTRUCTIONS.txt',
            "Database: decompress database/database.sql.gz and import it with MySQL.\r\n" .
            "Files: copy the contents of files/ back to their matching project folders.\r\n" .
            "Always restore on a test copy first.\r\n"
        );

        if (!$zip->close()) throw new RuntimeException('Unable to finish the backup archive.');
    }

    private function addDirectory(
        ZipArchive $zip,
        string $directory,
        string $archiveRoot
    ): void {
        if (!File::isDirectory($directory)) {
            return;
        }

        foreach (File::allFiles($directory) as $file) {
            if ($file->isLink()) {
                continue;
            }

            $relative = str_replace(
                '\\',
                '/',
                $file->getRelativePathname()
            );

            if (!$zip->addFile($file->getPathname(), $archiveRoot . '/' . $relative)) throw new RuntimeException('Unable to add an uploaded file to the backup.');
        }
    }
}
