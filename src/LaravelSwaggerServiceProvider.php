<?php

namespace Sciarcinski\LaravelSwagger;

use Illuminate\Support\ServiceProvider;

class LaravelSwaggerServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/docs-swagger.php' => config_path('docs-swagger.php'),
            ], 'config');
        }
    }

    /**
     * @return void
     */
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\MakeCommand::class,
                Console\GeneratorCommand::class,
            ]);
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/docs-swagger.php', 'docs-swagger');
    }
}
