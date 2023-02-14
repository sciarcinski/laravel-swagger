<?php

namespace Sciarcinski\LaravelSwagger\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
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
     * @throws BindingResolutionException
     *
     * @return void
     */
    public function handle(): void
    {
        /** @var DocumentationCreator $creator */
        $creator = app()->make(DocumentationCreator::class);
        $creator->setDoc($this->argument('documentation'));
        $creator->setRouteName($this->argument('route'));

        $routes = $creator->create((bool) $this->option('resource'));

        foreach ($routes as $route) {
            $this->info('\'' . $route . '\',');
        }
    }
}
