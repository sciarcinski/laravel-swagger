<?php

namespace Sciarcinski\LaravelSwagger\Console;

use Illuminate\Console\Command;
use Sciarcinski\LaravelSwagger\Generator;

class GeneratorCommand extends Command
{
    /** @var string */
    protected $signature = 'documentation:generator';

    /** @var string */
    protected $description = 'Documentation generator';

    /**
     * @throws \ReflectionException
     *
     * @return void
     */
    public function handle(): void
    {
        $documentations = config('docs-swagger.documentations', []);
        $generator = new Generator;

        foreach ($documentations as $documentation) {
            $this->info(date('Y-m-d H:i:s') . ' [' . $documentation['key'] . '] Documentation generator started');
            $this->line('');

            $bar = null;
            $routes = count($documentation['routes']);

            $generator->once('start', function () use (&$bar, $routes) {
                $bar = $this->output->createProgressBar($routes);
            });
            $generator->once('progress', function () use (&$bar) {
                $bar->advance();
            });
            $generator->once('finish', function () use (&$bar) {
                $bar->finish();
            });

            //$generator->setDocKey($documentation['key']);
            //$generator->generate($documentation);

            $this->line('');
            $this->line('');
            $this->info(date('Y-m-d H:i:s') . ' [' . $documentation['key'] . '] Documentation generator ended');
        }
    }
}
