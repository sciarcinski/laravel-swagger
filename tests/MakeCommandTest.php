<?php

namespace Tests;

class MakeCommandTest extends TestCase
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
                    'description' => '',
                    'version' => '1.0.0',
                    'default_security' => ['BearerAuth'],
                    'path_doc_json' => base_path('tests/doc_make/api.json'),
                    'path_components' => base_path('tests/docs_make/components/'),
                    'path_routes' => base_path('tests/docs_make/routes/'),
                    'names' => [],
                ],
            ],
        ]);

        $this->artisan('make:documentation api users.index');
    }
}
