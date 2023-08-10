<?php

namespace Sciarcinski\LaravelSwagger\Tests\Stubs\Generators;

use Sciarcinski\LaravelSwagger\Generator\Data;

class TestGenerator
{
    /** @var Data */
    protected Data $path;

    /**
     * @param Data $path
     */
    public function __construct(Data $path)
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
