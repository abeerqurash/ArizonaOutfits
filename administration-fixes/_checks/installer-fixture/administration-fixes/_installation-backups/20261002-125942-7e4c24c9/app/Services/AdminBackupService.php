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
        $stamp = now()->format('Y-m-d_H-i-s');
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

        $temporarySql = storage_path(
            'app/private/admin-backups/temp-' . $backup->id . '.sql.gz'
        );

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
        $connection = DB::connection();

        if (!in_array(
            $connection->getDriverName(),
            ['mysql', 'mariadb'],
            true
        )) {
            throw new RuntimeException(
                'The backup exporter currently supports MySQL and MariaDB.'
            );
        }

        $stream = gzopen($path, 'wb9');

        if ($stream === false) {
            throw new RuntimeException(
                'Unable to open the backup file for writing.'
            );
        }

        $pdo = $connection->getPdo();

        $tables = collect(
            $connection->select(
                'SHOW FULL TABLES WHERE Table_type = ?',
                ['BASE TABLE']
            )
        )->map(
            fn ($row) => array_values((array) $row)[0]
        )->values();

        $rowCount = 0;

        gzwrite(
            $stream,
            "-- Arizona Outfits database backup\n" .
            "-- Created: " . now()->toDateTimeString() . "\n" .
            "SET FOREIGN_KEY_CHECKS=0;\n" .
            "SET NAMES utf8mb4;\n\n"
        );

        foreach ($tables as $table) {
            $quotedTable = '`' . str_replace('`', '``', $table) . '`';

            $createResult = $connection->select(
                "SHOW CREATE TABLE {$quotedTable}"
            );

            $create = array_values((array) $createResult[0])[1] ?? null;

            if (!$create) {
                continue;
            }

            gzwrite(
                $stream,
                "DROP TABLE IF EXISTS {$quotedTable};\n{$create};\n\n"
            );

            $connection
                ->table($table)
                ->orderByRaw('1')
                ->chunk(
                    500,
                    function ($records) use (
                        $stream,
                        $pdo,
                        $quotedTable,
                        &$rowCount
                    ): void {
                        foreach ($records as $record) {
                            $values = array_map(
                                function ($value) use ($pdo): string {
                                    if ($value === null) {
                                        return 'NULL';
                                    }

                                    if (is_bool($value)) {
                                        return $value ? '1' : '0';
                                    }

                                    return $pdo->quote((string) $value);
                                },
                                array_values((array) $record)
                            );

                            gzwrite(
                                $stream,
                                "INSERT INTO {$quotedTable} VALUES (" .
                                implode(',', $values) .
                                ");\n"
                            );

                            $rowCount++;
                        }
                    }
                );

            gzwrite($stream, "\n");
        }

        gzwrite($stream, "SET FOREIGN_KEY_CHECKS=1;\n");
        gzclose($stream);

        return [$tables->count(), $rowCount];
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

        $zip->close();
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

            $zip->addFile(
                $file->getPathname(),
                $archiveRoot . '/' . $relative
            );
        }
    }
}
