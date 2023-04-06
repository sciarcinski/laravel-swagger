<?php

namespace Tests\Stubs\Generators;

use Sciarcinski\LaravelSwagger\Generator\Data;

class GenerateTest
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
