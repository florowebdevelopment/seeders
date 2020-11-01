<?php

namespace Florowebdevelopment\Seeders\app\Console\Commands\Seeders;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Class Make
 * @package Florowebdevelopment\Seeders\app\Console\Commands\Seeders
 */
class Make extends Command
{
    /**
     * Signature.
     *
     * @var string $signature
     */
    protected $signature = 'seeders:make {table}';

    /**
     * Description.
     *
     * @var string $description
     */
    protected $description = 'Make Seeder';

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
        $json = $this->getJson();

        $this->create($path, $filename, $json);
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
     * @return string
     */
    protected function getJson(): string
    {
        $records = [
            'RECORDS' => [
                [
                    'name' => 'Example'
                ]
            ]
        ];

        return json_encode($records, JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
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
}
