<?php

namespace Sciarcinski\LaravelSwagger;

use Sciarcinski\LaravelSwagger\Generator\Data;

trait Module
{
    /**
     * @param Data $path
     * @param array $modules
     * @return void
     */
    public function module(Data $path, array $modules = []): void
    {
        array_map(function ($module) use ($path) {
            if (method_exists($module, 'handle')) {
                (new $module($path))->handle();
            }
        }, $modules);
    }
}
