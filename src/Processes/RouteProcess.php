<?php

namespace Sciarcinski\LaravelSwagger\Processes;

use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class RouteProcess
{
    /** @var Route */
    protected Route $route;

    /** @var string|null */
    protected ?string $path;

    /** @var array */
    protected array $config = [];

    /** @var array */
    protected array $responses = [];

    /** @var array */
    protected array $parameters = [];

    /**
     * @param Route $route
     * @param string|null $path
     */
    public function __construct(Route $route, string $path = null)
    {
        $this->route = $route;
        $this->path = $path;
    }

    /**
     * @throws ReflectionException
     *
     * @return void
     */
    public function process(): void
    {
        if (! is_null($this->path)) {
            $this->config = json_decode(file_get_contents($this->path), true);
            $this->responses = $this->processResponses();
        }

        $this->parameters = $this->processParameters();
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function config(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->config, $key, $default);
    }

    /**
     * @return string
     */
    public function getOperationId(): string
    {
        return $this->route->getName();
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->config('tags', []);
    }

    /**
     * @return string
     */
    public function getSummary(): string
    {
        $summary = $this->config('summary');

        return empty($summary) ? $this->getOperationId() : $summary;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        $description = $this->config('description');

        return empty($description) ? $this->getSummary() : $description;
    }

    /**
     * @return array
     */
    public function getSecurity(): array
    {
        return $this->config('security', []);
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    /**
     * @return array
     */
    public function getMerge(): array
    {
        return $this->config('merge', []);
    }

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return (bool) $this->config('deprecated', false);
    }

    /**
     * @return bool
     */
    public function isMerge(): bool
    {
        return ! empty($this->getMerge());
    }

    /**
     * @return array
     */
    protected function processResponses(): array
    {
        $responses = [];

        foreach ($this->config('responses', []) as $code => $value) {
            $response = new ResponseProcess($code, $value);
            $response->process();

            $responses[] = $response;
        }

        return $responses;
    }

    /**
     * @throws ReflectionException
     *
     * @return array
     */
    private function processParameters(): array
    {
        $parameters = [];

        foreach ($this->reflectionMethod()->getParameters() as $parameter) {
            $parameter = new ParameterProcess($parameter);
            $parameter->process();

            $parameters[] = $parameter;
        }

        return $parameters;
    }

    /**
     * @throws ReflectionException
     *
     * @return ReflectionClass
     */
    public function reflection(): ReflectionClass
    {
        return new ReflectionClass($this->route->getController());
    }

    /**
     * @throws ReflectionException
     *
     * @return ReflectionMethod
     */
    public function reflectionMethod(): ReflectionMethod
    {
        return $this->reflection()->getMethod($this->route->getActionMethod());
    }
}
