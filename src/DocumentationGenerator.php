<?php

namespace Sciarcinski\LaravelSwagger;

use Evenement\EventEmitter;

class DocumentationGenerator extends EventEmitter
{
    use Pathable;

    public function generate(array $config)
    {
        $paths = [];
        $tags = [];

        foreach ($config['routes'] as $route) {
            $router = new ProcessRouter($route);
            $router->process();

            dd($router);
        }
    }
}
