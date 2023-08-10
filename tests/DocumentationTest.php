<?php

namespace Sciarcinski\LaravelSwagger\Tests;

use Illuminate\Support\Str;
use Sciarcinski\LaravelSwagger\Documentation;

class DocumentationTest extends TestCase
{
    /**
     * @test
     */
    public function valid()
    {
        $description = Str::random();
        $server = [Str::random(), Str::random()];
        $path = base_path('tests/doc_generate/api.json');

        $doc = new Documentation();
        $doc->setDescription($description);
        $doc->setServer($server[0], $server[1]);
        $doc->save($path);

        // verify
        $json = $doc->toJson();

        $this->assertStringContainsString('"description": "' . $description . '"', $json);
        $this->assertStringContainsString('"url": "' . $server[0] . '"', $json);
        $this->assertStringContainsString('"description": "' . $server[1] . '"', $json);
    }
}
