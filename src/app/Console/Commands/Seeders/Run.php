<?php

namespace Florowebdevelopment\Seeders\app\Console\Commands\Seeders;

use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class Run
 * @package Florowebdevelopment\Seeders\app\Console\Commands\Seeders
 */
class Run extends Command
{
    /**
     * Signature.
     *
     * @var string $signature
     */
    protected $signature = 'seeders:run';

    /**
     * Description.
     *
     * @var string $description
     */
    protected $description = 'Running Seeders';

    /**
     * Handle.
     */
    public function handle(): void
    {
        $path = $this->getPath();
        $files = $this->getSeederFiles($path);
        $seederNames = $this->getSeederNames($files);
        $ran = $this->getRan();
        $pendingSeeders = $this->pendingSeeders($seederNames, $ran);

        $this->runPending($pendingSeeders);
    }

    /**
     * Check Pending Seeders.
     *
     * @param array $seeders
     *
     * @return bool
     */
    protected function checkPendingSeeders(array $seeders): bool
    {
        $errors = 0;

        foreach ($seeders as $seeder) {
            $this->info(sprintf(
                'Check seeder: %s',
                $seeder
            ));

            $table = $this->getTable($seeder);

            if ( ! $this->ensureTableExist($table)) {
                ++$errors;
                continue;
            }

            $file = $this->getPath().'/'.$seeder .'.json';
            $records = $this->getRecords($file);
            $columns = $this->getColumns($records);

            if ( ! $this->ensureColumnsExist($table, $columns)) {
                ++$errors;
            }
        }

        return $errors == 0 ? true : false;
    }

    /**
     * Ensure Columns Exist.
     *
     * @param string $table
     * @param array $columns
     *
     * @return bool
     */
    protected function ensureColumnsExist(string $table, array $columns): bool
    {
        $errors = 0;

        $exists = [];

        foreach ($columns as $column) {
            if ( ! Schema::hasColumn($table, $column)) {
                $this->error(sprintf(
                    '[ERROR] Column "%s" does not exists.',
                    $column
                ));
                ++$errors;
            } else {
                $exists[] = $column;
            }
        }

        if ($errors > 0) {
            return false;
        }

        sort($exists);

        $this->line(sprintf(
            '[OK] Columns "%s" exists.',
            implode(', ', $exists)
        ));

        return true;
    }

    /**
     * Ensure Table Exist.
     *
     * @param string $table
     *
     * @return bool
     */
    protected function ensureTableExist(string $table): bool
    {
        if ( ! Schema::hasTable($table)) {
            $this->error(sprintf(
                '[ERROR] Table "%s" does not exists.',
                $table
            ));
            return false;
        }

        $this->line(sprintf(
            '[OK] Table "%s" exists.',
            $table
        ));

        return true;
    }

    /**
     * Get Columns.
     *
     * @param array $records
     *
     * @return array $columns
     */
    protected function getColumns(&$records): array
    {
        $columns = [];

        foreach ($records['RECORDS'] as $record) {
            foreach (array_keys($record) as $column) {
                if ( ! in_array($column, $columns)) {
                    $columns[] = $column;
                }
            }
        }

        return $columns;
    }

    /**
     * Get Next Batch Number.
     */
    protected function getNextBatchNumber()
    {
        return DB::table('seeders')->max('batch') + 1;
    }

    /**
     * Get Path.
     *
     * @return string
     */
    protected function getPath(): string
    {
        return database_path('seeders' );
    }

    /**
     * Get Ran.
     *
     * @return array
     */
    protected function getRan(): array
    {
        return DB::table('seeders')
            ->orderBy('batch', 'asc')
            ->orderBy('seeder', 'asc')
            ->pluck('seeder')
            ->all();
    }

    /**
     * Get Records.
     *
     * @param string $file
     *
     * @return array $records
     */
    protected function getRecords(string $file): array
    {
        $records = file_get_contents($file);
        $records = json_decode($records, true);

        if ( ! Arr::exists($records, 'RECORDS')) {
            $this->error('[ERROR] Invalid JSON structure.');
            return [];
        }

        return $records;
    }

    /**
     * Get Seeder Files.
     *
     * @param string $path
     *
     * @return array
     */
    protected function getSeederFiles(string $path): array
    {
        return glob($path.'/*.json');
    }

    /**
     * Get Seeder Name.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getSeederName(string $path): string
    {
        // Remove .json
        return str_replace('.json', '', basename($path));
    }

    /**
     * Get Seeder Names.
     *
     * @param array $files
     *
     * @return array $seederNames
     */
    protected function getSeederNames(array $files): array
    {
        $seederNames = [];

        foreach ($files as $path) {
            $seederNames[] = $this->getSeederName($path);
        }

        return $seederNames;
    }

    /**
     * Get Table.
     *
     * @param string $seeder
     *
     * @return string
     */
    protected function getTable(string $seeder): string
    {
        // Remove Prefix 'YYYY_MM_DD_XXXXXX_'
        $prefix = substr($seeder, 0, 18);

        return str_replace($prefix, '', $seeder);
    }

    /**
     * Log.
     *
     * @param string $file
     * @param int $batch
     */
    protected function log(string $seeder, int $batch)
    {
        DB::table('seeders')->insert([
            'seeder' => $seeder,
            'batch' => $batch
        ]);
    }

    /**
     * Pending Seeders.
     *
     * @param array $seederNames
     * @param array $ran
     *
     * @return array
     */
    protected function pendingSeeders(array $seederNames, array $ran): array
    {
        return array_diff($seederNames, $ran);
    }

    /**
     * Run Pending.
     *
     * @param array $seeders
     */
    protected function runPending(array $seeders): void
    {
        if (count($seeders) == 0) {
            $this->info('Nothing to seed.');
            return;
        }

        if ( ! $this->checkPendingSeeders($seeders)) {
            return;
        }

        $this->runPendingSeeders($seeders);
    }

    /**
     * Run Pending Seeders.
     *
     * @param array $seeders
     */
    protected function runPendingSeeders(array $seeders): void
    {
        $batch = $this->getNextBatchNumber();
        $this->line('Batch: ' . $batch);

        foreach ($seeders as $seeder) {
            $this->info(sprintf(
                'Running seeder: %s ',
                $seeder
            ));

            $table = $this->getTable($seeder);
            $file = $this->getPath().'/'.$seeder .'.json';
            $records = $this->getRecords($file);

            $this->seed($table, $records);
            $this->log($seeder, $batch);
        }
    }

    /**
     * Seed.
     *
     * @param string $table
     * @param array $records
     */
    protected function seed(string $table, array &$records): void
    {
        Schema::disableForeignKeyConstraints();

        $updateOrInsert = 0;
        $insert = 0;

        foreach ($records['RECORDS'] as $aRecord) {
            if (Arr::exists($aRecord,'id')) {
                $updateOrInsert += DB::table($table)->updateOrInsert(
                    [
                        'id' => $aRecord['id']
                    ],
                    $aRecord
                );
            } else {
                $insert += DB::table($table)->insert($aRecord);
            }
        }

        $this->line('Update Or Insert: ' . $updateOrInsert);
        $this->line('Insert: ' . $insert);

        Schema::enableForeignKeyConstraints();
    }
}
