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
                    'path_components' => base_path('tests/doc_make/components/'),
                    'path_routes' => base_path('tests/doc_make/routes/'),
                    'names' => [],
                    'generators' => [],
                    'creators' => [],
                ],
            ],
        ]);

        $this->artisan('make:documentation api users.show');

        // verify
        $file = base_path('tests/doc_make/routes/') . 'users_show.json';

        $this->assertFileExists($file);

        $this->beforeApplicationDestroyed(function () use ($file) {
            @unlink($file);
        });
    }
}
