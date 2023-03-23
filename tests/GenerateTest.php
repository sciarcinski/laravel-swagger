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
            'path_doc_json' => base_path('tests/docs/api.json'),
            'path_components' => base_path('tests/docs/components/'),
            'path_routes' => base_path('tests/docs/routes/'),
            'names' => [
                'users.index',
                'users.show',
                'users.store',
                //'users.update',
                //'users.destroy',
            ],
        ];

        /** @var RouteCollection $routes */
        $routes = app('router')->getRoutes();

        $generator = new Generator($config, $routes, new Documentation());
        $generator->on('error', function ($name, $e) {
            dd($e);
        });
        $generator->process();

        //dd($generator);
    }
}
