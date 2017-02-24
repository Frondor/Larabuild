<?php

namespace Frondor\Larabuild;

use Illuminate\Support\ServiceProvider;
use Frondor\Larabuild\Commands\Build as BuildCommand;

class LarabuildServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__).'/config/larabuild.php' => config_path('larabuild.php'),
        ], 'config');

        $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                BuildCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/config/larabuild.php', 'larabuild'
        );
    }
}
