<?php

namespace Tests\Stubs\Generators;

use Sciarcinski\LaravelSwagger\GeneratorContract;
use Sciarcinski\LaravelSwagger\Path;

class GenerateTest implements GeneratorContract
{
    /** @var Path */
    protected Path $path;

    /**
     * @param Path $path
     */
    public function __construct(Path $path)
    {
        $this->path = $path;
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->path->url = $this->path->url . '/test';
    }
}
