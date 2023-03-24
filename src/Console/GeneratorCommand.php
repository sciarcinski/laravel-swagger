<?php

namespace Sciarcinski\LaravelSwagger\Console;

use Illuminate\Console\Command;
use Illuminate\Routing\RouteCollection;
use ReflectionException;
use Sciarcinski\LaravelSwagger\Documentation;
use Sciarcinski\LaravelSwagger\Generator;

class GeneratorCommand extends Command
{
    /** @var string */
    protected $signature = 'documentation:generator';

    /** @var string */
    protected $description = 'Documentation generator';

    /**
     * @throws ReflectionException
     *
     * @return void
     */
    public function handle(): void
    {
        $documentations = config('docs-swagger.documentations', []);

        /** @var RouteCollection $routes */
        $routes = app('router')->getRoutes();

        foreach ($documentations as $documentation) {
            $this->info(date('Y-m-d H:i:s') . ' [' . $documentation['key'] . '] Documentation generator started');
            $this->line('');

            $bar = null;
            $count = count($documentation['names']);

            $generator = new Generator($documentation, $routes, new Documentation());
            $generator->once('start', function () use (&$bar, $count) {
                $bar = $this->output->createProgressBar($count);
            });
            $generator->once('progress', function () use (&$bar) {
                $bar->advance();
            });
            $generator->once('finish', function () use (&$bar) {
                $bar->finish();
            });
            $generator->process();

            $this->line('');
            $this->line('');
            $this->info(date('Y-m-d H:i:s') . ' [' . $documentation['key'] . '] Documentation generator ended');
        }
    }
}
