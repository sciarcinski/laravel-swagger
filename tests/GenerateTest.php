<?php

namespace Tests;

use Illuminate\Routing\RouteCollection;
use Sciarcinski\LaravelSwagger\Documentation;
use Sciarcinski\LaravelSwagger\Generator;

class GenerateTest extends TestCase
{
    /**
     * @test
     */
    public function valid()
    {
        $config = [
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
                'users.update',
                'users.destroy',
                'users.ban',
            ],
            'generators' => [
                \Tests\Stubs\Generators\GenerateTest::class,
            ],
        ];

        /** @var RouteCollection $routes */
        $routes = app('router')->getRoutes();

        $generator = new Generator($config, $routes, new Documentation());
        $generator->process();

        // verify
        $this->assertFileExists($config['path_doc_json']);

        $this->beforeApplicationDestroyed(function () use ($config) {
            @unlink($config['path_doc_json']);
        });
    }
}
