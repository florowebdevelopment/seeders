<?php

namespace Florowebdevelopment\Seeders;

use Florowebdevelopment\Seeders\app\Console\Commands\Seeders\Generate as SeedersGenerateCommand;
use Florowebdevelopment\Seeders\app\Console\Commands\Seeders\Make as SeedersMakeCommand;
use Florowebdevelopment\Seeders\app\Console\Commands\Seeders\Run as SeedersRunCommand;
use Illuminate\Support\Facades\File;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Create Directory

        if ( ! File::exists(database_path('seeders'))) {
            File::makeDirectory(database_path('seeders'), 0775, true);
        }

        // Migrations
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Commands

        $this->commands([
            SeedersGenerateCommand::class,
            SeedersMakeCommand::class,
            SeedersRunCommand::class
        ]);
    }
}
