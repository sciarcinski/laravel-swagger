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
    protected array $rulesRequest = [];

    /** @var array */
    protected array $required = [];

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
     * @return array
     */
    public function getRulesRequest(): array
    {
        return $this->rulesRequest;
    }

    /**
     * @return array
     */
    public function getRequired(): array
    {
        return $this->required;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return strtolower($this->route->methods()[0]);
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return '/' . $this->route->uri();
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

        foreach ($routeParameters as $routeParameter) {
            $parameter = new ProcessParameter($routeParameter);
            $parameter->process();

            $this->addRequired($parameter->getRequired());
            $this->addRulesRequest($parameter->getRulesRequest());
        }

        return $this;
    }

    /**
     * @param array $items
     * @return void
     */
    protected function addRequired(array $items): void
    {
        if (! empty($items)) {
            $this->required = array_merge($this->required, $items);
        }
    }

    /**
     * @param array $items
     * @return void
     */
    protected function addRulesRequest(array $items): void
    {
        if (! empty($items)) {
            $this->rulesRequest = array_merge($this->rulesRequest, $items);
        }
    }
}
