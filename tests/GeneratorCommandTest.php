<?php

namespace Tests;

class GeneratorCommandTest extends TestCase
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

        $this->artisan('documentation:generator');

        // verify
        $this->assertFileExists(base_path('tests/doc_generate/api.json'));

        $this->beforeApplicationDestroyed(function () {
            @unlink(base_path('tests/doc_generate/api.json'));
        });
    }
}
