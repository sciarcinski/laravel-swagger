<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Sciarcinski\LaravelSwagger\LaravelSwaggerServiceProvider;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelSwaggerServiceProvider::class,
        ];
    }

    /**
     * @param Application $app
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        $app->setBasePath(__DIR__ . '/..');
    }

    /**
     * @param $router
     * @return void
     */
    protected function defineRoutes($router): void
    {
        $router->put('users/{user}/ban', [\Tests\Stubs\Controllers\UserController::class, 'ban'])->name('users.ban');
        $router->apiResource('users', \Tests\Stubs\Controllers\UserController::class);
    }
}
