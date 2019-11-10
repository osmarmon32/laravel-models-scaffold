<?php

namespace Reddireccion\ModelsScaffold;

use Illuminate\Support\ServiceProvider;
use \Reddireccion\ModelsScaffold\Database\MySqlConnection;
use Reddireccion\ModelsScaffold\Console\CreateModelScaffold;
use Schema;

class ModelsScaffoldServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        \Illuminate\Database\Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MySqlConnection($connection,$database,$prefix,$config);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateModelScaffold::class
            ]);
        }
    }
}
