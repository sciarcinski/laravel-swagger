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
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return void
     */
    public function handle(): void
    {
        $documentations = config('docs-swagger.documentations', []);
        $generator = app()->make(DocumentationGenerator::class);

        foreach ($documentations as $docKey => $documentation) {
            $this->info(date('Y-m-d H:i:s') . ' [' . $docKey . '] Documentation generator started');
            $this->line('');

            $routes = count($documentation['routes']);
            $bar = $this->output->createProgressBar($routes);

            $generator->setDocKey($docKey);
            $generator->on('progress', function () use ($bar) {
            $bar->advance();
            });
            $generator->generate($documentation);

            $bar->finish();

            $this->line('');
            $this->line('');
            $this->info(date('Y-m-d H:i:s') . ' [' . $docKey . '] Documentation generator ended');
        }
    }
}
