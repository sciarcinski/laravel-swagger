<?php

namespace Sciarcinski\LaravelSwagger\Console;

use Illuminate\Console\Command;
use Sciarcinski\LaravelSwagger\DocumentationGenerator;

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
        $generator = new DocumentationGenerator;

        foreach ($documentations as $docKey => $documentation) {
            $this->info(date('Y-m-d H:i:s') . ' [' . $docKey . '] Documentation generator started');
            $this->line('');

            $bar = null;
            $routes = count($documentation['routes']);

            $generator->on('start', function () use (&$bar, $routes) {
                $bar = $this->output->createProgressBar($routes);
            });
            $generator->on('progress', function () use (&$bar) {
                $bar->advance();
            });
            $generator->on('finish', function () use (&$bar) {
                $bar->finish();
            });

            $generator->setDocKey($docKey);
            $generator->generate($documentation);

            $this->line('');
            $this->line('');
            $this->info(date('Y-m-d H:i:s') . ' [' . $docKey . '] Documentation generator ended');
        }
    }
}
