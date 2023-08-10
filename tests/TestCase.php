<?php

namespace Sciarcinski\LaravelSwagger\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Sciarcinski\LaravelSwagger\LaravelSwaggerServiceProvider;
use Sciarcinski\LaravelSwagger\Tests\Stubs\Controllers\UserController;

class TestCase extends BaseTestCase
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
        $router->put('users/{user}/ban', [UserController::class, 'ban'])->name('users.ban');
        $router->apiResource('users', UserController::class);
    }
}
