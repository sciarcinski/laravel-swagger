<?php

namespace Sciarcinski\LaravelSwagger\Tests\Console;

use Sciarcinski\LaravelSwagger\Tests\TestCase;

class GenerateCommandTest extends TestCase
{
    /**
     * @test
     */
    public function valid()
    {
        config()->set('docs-swagger', [
            'documentations' => [
                [
                    'key' => 'api',
                    'title' => 'API',
                    'default_security' => [],
                    'path_doc_json' => base_path('tests/doc_generate/api.json'),
                    'path_components' => base_path('tests/doc_generate/components/'),
                    'path_routes' => base_path('tests/doc_generate/routes/'),
                    'names' => [
                        'users.index',
                        'users.show',
                        'users.store',
                    ],
                ],
            ],
        ]);

        $this->artisan('doc:generate', ['--key' => 'api']);

        // verify
        $this->assertFileExists(base_path('tests/doc_generate/api.json'));

        $this->beforeApplicationDestroyed(function () {
            @unlink(base_path('tests/doc_generate/api.json'));
        });
    }
}
