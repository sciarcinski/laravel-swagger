<?php

namespace Sciarcinski\LaravelSwagger;

use Illuminate\Support\ServiceProvider;

class LaravelSwaggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '../config/documentation.php' => config_path('documentation.php'),
            ], 'config');
        }
    }

    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\MakeCommand::class,
                Console\GeneratorCommand::class,
            ]);
        }
    }
}
