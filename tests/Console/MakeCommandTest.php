<?php

namespace Tests\Console;

use Tests\TestCase;

class MakeCommandTest extends TestCase
{
    /**
     * @return void
     */
    public function test_valid(): void
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

        // start
        $this->artisan('make:doc api users.show')->assertSuccessful();

        // verify
        $file = base_path('tests/doc_make/routes/') . 'users_show.json';

        $this->assertFileExists($file);

        $this->beforeApplicationDestroyed(function () use ($file) {
            @unlink($file);
        });
    }

    /**
     * @return void
     */
    public function test_valid_resource(): void
    {
        config()->set('docs-swagger', [
            'documentations' => [
                [
                    'key' => 'api',
                    'title' => 'API',
                    'description' => '',
                    'version' => '1.0.0',
                    'default_security' => 'BearerAuth',
                    'path_doc_json' => base_path('tests/doc_make/api.json'),
                    'path_components' => base_path('tests/doc_make/components/'),
                    'path_routes' => base_path('tests/doc_make/routes/'),
                    'names' => [],
                    'generators' => [],
                    'creators' => [],
                ],
            ],
        ]);

        // start
        $this->artisan('make:doc api users.show --resource')->assertSuccessful();

        // verify
        $index = base_path('tests/doc_make/routes/') . 'users_index.json';
        $show = base_path('tests/doc_make/routes/') . 'users_show.json';
        $store = base_path('tests/doc_make/routes/') . 'users_store.json';
        $update = base_path('tests/doc_make/routes/') . 'users_update.json';
        $destroy = base_path('tests/doc_make/routes/') . 'users_destroy.json';

        $this->assertFileExists($index);
        $this->assertFileExists($show);
        $this->assertFileExists($store);
        $this->assertFileExists($update);
        $this->assertFileExists($destroy);

        $this->beforeApplicationDestroyed(function () use ($index, $show, $store, $update, $destroy) {
            @unlink($index);
            @unlink($show);
            @unlink($store);
            @unlink($update);
            @unlink($destroy);
        });
    }

    /**
     * @return void
     */
    public function test_valid_route_ban(): void
    {
        config()->set('docs-swagger', [
            'documentations' => [
                [
                    'key' => 'api',
                    'title' => 'API',
                    'description' => '',
                    'version' => '1.0.0',
                    'default_security' => [],
                    'path_doc_json' => base_path('tests/doc_make/api.json'),
                    'path_components' => base_path('tests/doc_make/components/'),
                    'path_routes' => base_path('tests/doc_make/routes/'),
                    'names' => [],
                    'generators' => [],
                    'creators' => [],
                ],
            ],
        ]);

        // start
        $this->artisan('make:doc api users.ban')->assertSuccessful();

        // verify
        $file = base_path('tests/doc_make/routes/') . 'users_ban.json';

        $this->assertFileExists($file);

        $this->beforeApplicationDestroyed(function () use ($file) {
            @unlink($file);
        });
    }

    /**
     * @return void
     */
    public function test_fails_no_configuration(): void
    {
        config()->set('docs-swagger', []);

        // start
        $this->artisan('make:doc api users.show')
            ->assertFailed()
            ->expectsOutput('No configuration for documentation key: api');
    }

    /**
     * @return void
     */
    public function test_fails_route_does_not_exist(): void
    {
        config()->set('docs-swagger', [
            'documentations' => [
                [
                    'key' => 'api',
                    'title' => 'API',
                    'description' => '',
                    'version' => '1.0.0',
                    'default_security' => [],
                    'path_doc_json' => base_path('tests/doc_make/api.json'),
                    'path_components' => base_path('tests/doc_make/components/'),
                    'path_routes' => base_path('tests/doc_make/routes/'),
                    'names' => [],
                    'generators' => [],
                    'creators' => [],
                ],
            ],
        ]);

        // start
        $this->artisan('make:doc api users.not.exist')
            ->assertFailed()
            ->expectsOutput('Route name [users.not.exist] does not exist');
    }
}
