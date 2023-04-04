<?php

namespace Sciarcinski\LaravelSwagger\Console;

use Illuminate\Console\Command;
use Sciarcinski\LaravelSwagger\DocumentationCreator;

class MakeCommand extends Command
{
    /** @var string */
    protected $signature = 'make:documentation
        {documentation : Create a new documentation file}
        {route : Create a new documentation file}
        {--resource : Create new documentation files}';

    /** @var string */
    protected $description = 'Create a new documentation file';

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return void
     */
    public function handle()
    {
        $creator = app()->make(DocumentationCreator::class);
        $creator->setDocKey($this->argument('documentation'));

        $routes = $creator->create(
            (string) $this->argument('route'),
            (bool) $this->option('resource')
        );

        foreach ($routes as $route) {
            $this->info('\'' . $route . '\',');
        }
    }
}
