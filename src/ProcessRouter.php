<?php

namespace Sciarcinski\LaravelSwagger;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use ReflectionClass;

class ProcessRouter
{
    /** @var Route */
    protected Route $route;

    /** @var array */
    protected array $parameters = [];

    /**
     * @param string $route
     */
    public function __construct(string $route)
    {
        $this->route = app()
            ->make(Router::class)
            ->getRoutes()
            ->getByName($route);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return strtolower($this->route->methods()[0]);
    }

    /**
     * @throws \ReflectionException
     *
     * @return $this
     */
    public function process(): static
    {
        $method = (new ReflectionClass($this->route->getController()))->getMethod($this->route->getActionMethod());
        $routeParameters = $method->getParameters();

        $parameters = [];

        foreach ($routeParameters as $routeParameter) {
            $parameter = new ProcessParameter($routeParameter);
            $parameter->process();

            $parameters[] = $parameter;
        }

        return $this;
    }
}
