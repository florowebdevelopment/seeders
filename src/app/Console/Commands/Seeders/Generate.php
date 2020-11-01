<?php

namespace Florowebdevelopment\Seeders\app\Console\Commands\Seeders;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Class Make
 * @package Florowebdevelopment\Seeders\app\Console\Commands\Seeders
 */
class Generate extends Command
{
    /**
     * Signature.
     *
     * @var string $signature
     */
    protected $signature = 'seeders:generate {table}';

    /**
     * Description.
     *
     * @var string $description
     */
    protected $description = 'Generating Seeder';

    /**
     * Handle.
     */
    public function handle(): void
    {
        $table = $this->argument('table');

        if ( ! $this->ensureTableExist($table)) {
            return;
        }

        $path = $this->getPath();
        $filename = $this->getFilename($table);
        $columns = $this->getColumns($table);

        if ( ! $this->checkColumns($columns)) {
            return;
        }

        $range = $this->getRange($table);
        $records = $this->getRecords($table, $columns, $range);
        $json = $this->getJson($records);

        $this->create($path, $filename, $json);
    }

    /**
     * Check Columns.
     *
     * @param array $columns
     *
     * @return bool
     */
    protected function checkColumns(array $columns): bool
    {
        if (count($columns) == 0) {
            $this->error('[ERROR] No columns have been added.');
            return false;
        }

        return true;
    }

    /**
     * Create.
     *
     * @param string $path
     * @param string $filename
     * @param string $json
     */
    protected function create(string $path, string $filename, string $json): void
    {
        File::put($path.'/'.$filename, $json);

        $this->line(sprintf('Created Seeder: %s/%s',
            $path,
            $filename
        ));
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

        return true;
    }

    /**
     * Get Columns
     *
     * @param string $table
     *
     * @return array $columns
     */
    protected function getColumns(string $table): array
    {
        $columns = [];

        $this->line("Columns");

        $columnListing = Schema::getColumnListing($table);

        foreach ($columnListing as $column) {
            if ($this->confirm(sprintf(
                'Add column "%s" ?',
                $column
            ))) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * Get Date Prefix.
     *
     * @return string
     */
    protected function getDatePrefix(): string
    {
        return date('Y_m_d_His');
    }

    /**
     * Get Filename.
     *
     * @param string $table
     *
     * @return string
     */
    protected function getFilename(string $table): string
    {
        return $this->getDatePrefix().'_'.$table.'.json';
    }

    /**
     * Get Json.
     *
     * @param \Illuminate\Support\Collection $records
     *
     * @return string
     */
    protected function getJson(\Illuminate\Support\Collection $records): string
    {
        $records = [
            'RECORDS' => $records->toArray()
        ];

        return json_encode($records, JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * Get Max Id.
     */
    protected function getMaxId(string $table)
    {
        return DB::table($table)->max('id');
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
     * Get Range
     *
     * @param string $table
     *
     * @return array
     */
    protected function getRange(string $table): array
    {
        $this->line("Range");

        $from = $this->ask(sprintf(
            '%s.id from',
            $table
        ), 0);

        $to = $this->ask(sprintf(
            '%s.id to',
            $table
        ), $this->getMaxId($table));

        return range($from, $to);
    }

    /**
     * Get Records.
     *
     * @param string $table
     * @param array $columns
     * @param array $range
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getRecords(string $table, array $columns, array $range): \Illuminate\Support\Collection
    {
        return DB::table($table)
            ->select($columns)
            ->whereIn('id', $range)
            ->get();
    }
}
