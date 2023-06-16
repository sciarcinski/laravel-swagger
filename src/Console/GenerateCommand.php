<?php

namespace Sciarcinski\LaravelSwagger\Console;

use Illuminate\Console\Command;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Arr;
use ReflectionException;
use Sciarcinski\LaravelSwagger\Documentation;
use Sciarcinski\LaravelSwagger\Generator;

class GenerateCommand extends Command
{
    /** @var string */
    protected $signature = 'doc:generate {--key= : Documentation key}';

    /** @var string */
    protected $description = 'Documentations generate';

    /**
     * @throws ReflectionException
     *
     * @return void
     */
    public function handle(): void
    {
        $key = $this->option('key');
        $docs = config('docs-swagger.documentations', []);

        if ($key) {
            $docs = Arr::where($docs, fn ($doc) => $doc['key'] === $key);
        }

        /** @var RouteCollection $routes */
        $routes = app('router')->getRoutes();

        foreach ($docs as $doc) {
            $this->info(date('Y-m-d H:i:s') . ' [' . $doc['key'] . '] Documentation generator started');
            $this->line('');

            $bar = null;
            $count = count($doc['names']);

            $generator = new Generator($doc, $routes, new Documentation());
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
            $this->info(date('Y-m-d H:i:s') . ' [' . $doc['key'] . '] Documentation generator ended');
        }
    }
}
