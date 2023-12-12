<?php

namespace Fintreen\Laravel;

use Illuminate\Support\ServiceProvider;

class FintreenServiceProvider extends ServiceProvider
{

    protected $commands = [
        \Fintreen\Laravel\app\Console\Commands\FintreenTransacionsCheck::class
    ];

    public function boot()
    {

        $this->loadRoutes();
        //$this->loadConfigs();
        $this->publishFiles();
        $this->publisMigrations();
        //$this->publishFiles();
        // register the artisan commands
        $this->commands($this->commands);
    }

    public function register()
    {
        // register the artisan commands
        $this->commands($this->commands);
    }

    public function loadRoutes() {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }

    public function loadConfigs()
    {

    }

    public function publisMigrations() {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    public function publishFiles()
    {
        $configFiles = [__DIR__.'/config' => config_path()];


        $minimum = [
            $configFiles
        ];
        $this->publishes($configFiles, 'config');

        $this->publishes($minimum, 'minimum');
    }
}