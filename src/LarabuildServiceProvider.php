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

        if ($this->app->config['app.env'] == 'production') {
            $update_public_path = $this->app->config['larabuild.new_public_folder'];
            if ($update_public_path) {
                $this->app->bind('path.public', function() {
                    return str_replace('laravel', $update_public_path, base_path());
                });
            }
            $update_public_path = null;
        }
    }
}
