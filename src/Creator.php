<?php

namespace Sciarcinski\LaravelSwagger;

use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionException;
use Sciarcinski\LaravelSwagger\Processes\ParameterProcess;
use Sciarcinski\LaravelSwagger\Processes\RouteProcess;

class Creator
{
    /** @var array */
    protected array $config;

    /** @var RouteCollection */
    protected RouteCollection $routes;

    /** @var string */
    protected string $apiKey;

    /** @var string */
    protected string $route;

    /** @var bool */
    protected bool $resource;

    /**
     * @param array $config
     * @param RouteCollection $routes
     * @param string $apiKey
     * @param string $route
     * @param bool $resource
     */
    public function __construct(array $config, RouteCollection $routes, string $apiKey, string $route, bool $resource = false)
    {
        $this->config = $config;
        $this->routes = $routes;
        $this->apiKey = $apiKey;
        $this->route = $route;
        $this->resource = $resource;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function config(string $key): mixed
    {
        return Arr::get($this->config, $key);
    }

    /**
     * @throws ReflectionException
     *
     * @return array
     */
    public function create(): array
    {
        $routes = $this->resource ? $this->prepareResourceName($this->route) : [$this->route];

        foreach ($routes as $route) {
            $this->createFile($route);
        }

        return $routes;
    }

    /**
     * @param string $routeName
     * @return array
     */
    protected function prepareResourceName(string $routeName): array
    {
        $routes = [];

        foreach (['index', 'show', 'store', 'update', 'destroy'] as $item) {
            $route = $routeName . '.' . $item;

            if ($this->routes->hasNamedRoute($route)) {
                $routes[] = $route;
            }
        }

        return $routes;
    }

    /**
     * @param string $routeName
     *
     * @throws ReflectionException
     *
     * @return string
     */
    protected function createFile(string $routeName): string
    {
        $path = Str::finish($this->config('path_routes'), '/');
        $route = $this->routes->getByName($routeName);
        $file = $path . $this->transformFileName($routeName) . '.json';

        if (! file_exists($file)) {
            $data = [
                'tags' => [],
                'summary' => $route->getName(),
                'description' => null,
                'operationId' => $route->getName(),
                'security' => $this->defaultSecurity(),
                'merge' => [],
                'responses' => $this->responses($route),
            ];

            if (! empty($parameters = $this->parameters($route))) {
                $data['merge']['parameters'] = $parameters;
            }

            file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return $file;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function transformFileName(string $name): string
    {
        return Str::slug(Str::replace('.', '_', $name), '_');
    }

    /**
     * @return array|mixed|string[]
     */
    protected function defaultSecurity(): mixed
    {
        $defaultSecurity = $this->config('default_security');

        if (! empty($defaultSecurity) && is_string($defaultSecurity)) {
            $defaultSecurity = [$defaultSecurity];
        }

        if (empty($defaultSecurity)) {
            $defaultSecurity = [];
        }

        return $defaultSecurity;
    }

    /**
     * @param Route $route
     * @return array
     */
    protected function responses(Route $route): array
    {
        $success = [
            'description' => 'Successful response',
            'content' => [
                'application\/json' => [
                    'example' => [],
                ],
            ],
        ];

        $data = [];

        switch ($route->getActionMethod()) {
            case 'index':
                $data['200'] = $success;
                $data['401'] = ['$ref' => '#/components/responses/unauthorized'];
                break;

            case 'show':
                $data['200'] = $success;
                $data['401'] = ['$ref' => '#/components/responses/unauthorized'];
                $data['404'] = ['$ref' => '#/components/responses/not_found_http'];
                break;

            case 'store':
                $data['201'] = $success;
                $data['401'] = ['$ref' => '#/components/responses/unauthorized'];
                $data['422'] = ['$ref' => '#/components/responses/invalidation'];
                break;

            case 'update':
                $data['200'] = $success;
                $data['401'] = ['$ref' => '#/components/responses/unauthorized'];
                $data['404'] = ['$ref' => '#/components/responses/not_found_http'];
                $data['422'] = ['$ref' => '#/components/responses/invalidation'];
                break;

            case 'destroy':
                $data['204'] = ['$ref' => '#/components/responses/no_content'];
                $data['401'] = ['$ref' => '#/components/responses/unauthorized'];
                $data['404'] = ['$ref' => '#/components/responses/not_found_http'];
                break;

            default:
                $data['200'] = $success;
                break;
        }

        return $data;
    }

    /**
     * @param Route $route
     *
     * @throws ReflectionException
     *
     * @return array
     */
    protected function parameters(Route $route): array
    {
        $paths = $this->parametersRouteProcess($route);
        $urls = $this->parametersRouteUri($route);
        $result = [];

        if (count($paths) === count($urls)) {
            $parameters = array_combine($urls, $paths);

            foreach ($parameters as $parameter => $type) {
                $result[] = [
                    'in' => 'path',
                    'name' => $parameter,
                    'required' => true,
                    'description' => $parameter,
                    'schema' => [
                        'type' => $type,
                    ],
                ];
            }
        }

        return $result;
    }

    /**
     * @param Route $route
     *
     * @throws ReflectionException
     *
     * @return array
     */
    protected function parametersRouteProcess(Route $route): array
    {
        $process = new RouteProcess($route);
        $process->process();

        $paths = [];

        /** @var ParameterProcess $parameter */
        foreach ($process->getParameters() as $parameter) {
            if (! empty($path = $parameter->getPath())) {
                $paths[] = $path['type'];
            }
        }

        return $paths;
    }

    /**
     * @param Route $route
     * @return array
     */
    protected function parametersRouteUri(Route $route): array
    {
        preg_match_all('/\{(.*?)\}/', $route->uri(), $parameters);

        return Arr::get($parameters, 1, []);
    }
}
