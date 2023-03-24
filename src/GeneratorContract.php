<?php

namespace Sciarcinski\LaravelSwagger;

interface GeneratorContract
{
    /**
     * @param Path $path
     */
    public function __construct(Path $path);

    /**
     * @return void
     */
    public function handle(): void;
}
