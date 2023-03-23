<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Sciarcinski\LaravelSwagger\LaravelSwaggerServiceProvider;

abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->registerRoutes();
    }

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
    protected function defineEnvironment($app)
    {
        $app->setBasePath(__DIR__ . '/..');
    }

    /**
     * @return void
     */
    public function registerRoutes(): void
    {
        Route::apiResource('users', \Tests\Stubs\Controllers\UserController::class);
    }
}
