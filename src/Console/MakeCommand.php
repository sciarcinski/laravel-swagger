<?php

namespace Sciarcinski\LaravelSwagger\Console;

use Illuminate\Console\Command;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Arr;
use Sciarcinski\LaravelSwagger\Creator;

class MakeCommand extends Command
{
    /** @var string */
    protected $signature = 'make:documentation
        {apiKey : API key}
        {route : Route name}
        {--resource : Resource controller}';

    /** @var string */
    protected $description = 'Create a new documentation file';

    /**
     * @return int
     */
    public function handle()
    {
        $apiKey = $this->argument('apiKey');
        $config = Arr::first(config('docs-swagger.documentations', []), function ($doc) use ($apiKey) {
            return $doc['key'] === $apiKey;
        });

        if (empty($config)) {
            $this->error('No configuration for API key: ' . $apiKey);

            return 1;
        }

        /** @var RouteCollection $routes */
        $routes = app('router')->getRoutes();

        $creator = new Creator(
            $config,
            $routes,
            $this->argument('apiKey'),
            $this->argument('route'),
            (bool) $this->option('resource')
        );

        $routes = $creator->create();

        foreach ($routes as $route) {
            $this->info('\'' . $route . '\',');
        }

        return 0;
    }
}
