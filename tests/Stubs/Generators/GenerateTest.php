<?php

namespace Tests\Stubs\Generators;

use Sciarcinski\LaravelSwagger\Storage;
use Sciarcinski\LaravelSwagger\TransformerContract;

class GenerateTest implements TransformerContract
{
    /** @var Storage */
    protected Storage $path;

    /**
     * @param Storage $path
     */
    public function __construct(Storage $path)
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
