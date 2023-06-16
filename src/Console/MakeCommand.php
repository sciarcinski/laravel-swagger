<?php

namespace Sciarcinski\LaravelSwagger\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Arr;
use Sciarcinski\LaravelSwagger\Creator;
use Throwable;

class MakeCommand extends Command
{
    /** @var string */
    protected $signature = 'make:doc
        {docKey : Documentation key}
        {route : Route name}
        {--resource : Resource controller}';

    /** @var string */
    protected $description = 'Create a new documentation file';

    /**
     * @return int
     */
    public function handle(): int
    {
        try {
            $docKey = $this->argument('docKey');

            $config = Arr::first(config('docs-swagger.documentations', []), function ($doc) use ($docKey) {
                return $doc['key'] === $docKey;
            });

            if (empty($config)) {
                throw new Exception('No configuration for documentation key: ' . $docKey);
            }

            /** @var RouteCollection $routes */
            $routes = app('router')->getRoutes();

            $creator = new Creator(
                $config,
                $routes,
                $this->argument('docKey'),
                $this->argument('route'),
                (bool) $this->option('resource')
            );

            $routes = $creator->create();

            foreach ($routes as $route) {
                $this->info('\'' . $route . '\',');
            }

            return 0;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return 1;
        }
    }
}
