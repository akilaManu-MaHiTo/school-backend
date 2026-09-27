<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class BackupDatabaseController extends Controller
{
    public function backupDatabase()
    {
        try {
            $connectionName = config('database.default');
            $connection = DB::connection($connectionName);
            $driver = $connection->getDriverName();
            $databaseName = config("database.connections.{$connectionName}.database", 'database');

            $backupDirectory = storage_path('app/backups');
            File::ensureDirectoryExists($backupDirectory);

            $timestamp = now()->format('Y-m-d_His');
            $backupPath = $backupDirectory . DIRECTORY_SEPARATOR . $databaseName . '_backup_' . $timestamp . '.sql';

            $dump = match ($driver) {
                'mysql', 'mariadb' => $this->buildRelationalDatabaseDump($connection, 'mysql'),
                'sqlite' => $this->buildRelationalDatabaseDump($connection, 'sqlite'),
                default => throw new \RuntimeException("Database driver [{$driver}] is not supported for backups."),
            };

            File::put($backupPath, $dump);

            return response()->download($backupPath)->deleteFileAfterSend(true);
        } catch (Throwable $throwable) {
            return response()->json([
                'message' => 'Failed to create database backup.',
                'error' => $throwable->getMessage(),
            ], 500);
        }
    }

    protected function buildRelationalDatabaseDump($connection, string $driver): string
    {
        $pdo = $connection->getPdo();
        $tables = $this->getTableNames($connection, $driver);

        $dump = "-- Database backup generated at " . now()->toDateTimeString() . PHP_EOL;
        $dump .= "SET FOREIGN_KEY_CHECKS=0;" . PHP_EOL . PHP_EOL;

        foreach ($tables as $table) {
            $quotedTable = $this->quoteIdentifier($table);

            if ($driver === 'mysql') {
                $createTable = $connection->selectOne("SHOW CREATE TABLE {$quotedTable}");
                $createTableSql = (array) $createTable;
                $createStatement = $createTableSql['Create Table'] ?? array_values($createTableSql)[1] ?? null;

                if ($createStatement) {
                    $dump .= "DROP TABLE IF EXISTS {$quotedTable};" . PHP_EOL;
                    $dump .= $createStatement . ';' . PHP_EOL . PHP_EOL;
                }
            } else {
                $createTable = $connection->selectOne(
                    'SELECT sql FROM sqlite_master WHERE type = ? AND name = ?',
                    ['table', $table]
                );

                $createStatement = $createTable?->sql;

                if ($createStatement) {
                    $dump .= "DROP TABLE IF EXISTS {$quotedTable};" . PHP_EOL;
                    $dump .= $createStatement . ';' . PHP_EOL . PHP_EOL;
                }
            }

            $rows = $connection->select("SELECT * FROM {$quotedTable}");

            if (empty($rows)) {
                continue;
            }

            $columns = array_keys((array) $rows[0]);
            $columnList = implode(', ', array_map([$this, 'quoteIdentifier'], $columns));

            foreach (array_chunk($rows, 500) as $chunk) {
                $valueStatements = [];

                foreach ($chunk as $row) {
                    $rowValues = [];

                    foreach ($columns as $column) {
                        $value = data_get($row, $column);

                        if ($value === null) {
                            $rowValues[] = 'NULL';
                            continue;
                        }

                        if (is_bool($value)) {
                            $value = $value ? 1 : 0;
                        }

                        $rowValues[] = $pdo->quote((string) $value);
                    }

                    $valueStatements[] = '(' . implode(', ', $rowValues) . ')';
                }

                $dump .= "INSERT INTO {$quotedTable} ({$columnList}) VALUES" . PHP_EOL;
                $dump .= implode(',' . PHP_EOL, $valueStatements) . ';' . PHP_EOL . PHP_EOL;
            }
        }

        $dump .= 'SET FOREIGN_KEY_CHECKS=1;' . PHP_EOL;

        return $dump;
    }

    protected function getTableNames($connection, string $driver): array
    {
        if ($driver === 'mysql') {
            $tables = $connection->select('SHOW FULL TABLES WHERE Table_type = ?', ['BASE TABLE']);
            $tableKey = null;

            if (!empty($tables)) {
                $tableKey = array_key_first((array) $tables[0]);
            }

            return array_values(array_filter(array_map(function ($table) use ($tableKey) {
                $tableArray = (array) $table;

                return $tableKey ? ($tableArray[$tableKey] ?? null) : (array_values($tableArray)[0] ?? null);
            }, $tables)));
        }

        $tables = $connection->select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name");

        return array_values(array_filter(array_map(static fn ($table) => $table->name ?? null, $tables)));
    }

    protected function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
