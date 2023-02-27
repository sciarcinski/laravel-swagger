<?php

namespace Tests\Unit\DocumentationGenerator;

use Sciarcinski\LaravelSwagger\DocumentationGenerator;
use Tests\TestCase;

class GenerateTest extends TestCase
{
    /**
     * @test
     */
    public function valid()
    {
        $generator = new DocumentationGenerator;
    }
}
