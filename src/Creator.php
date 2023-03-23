<?php

namespace Sciarcinski\LaravelSwagger;

use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
     * @return array
     */
    public function create(): array
    {
        $routes = $this->resource ? $this->prepareResourceName($this->route) : [$this->route];

        foreach ($routes as $route) {
            $this->createFiles($route);
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
     * @return string
     */
    protected function createFiles(string $routeName): string
    {
        $path = Str::finish($this->config('path_routes'), '/');
        $route = $this->routes->getByName($routeName);
        $file = $path . $this->transformFileName($routeName) . '.json';

        dd($route);

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

            dd($data);
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
     * @param string $path
     * @param array $data
     * @return string
     */
    protected function createFile(string $path, array $data = []): string
    {
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));

        return $path;
    }

    /**
     * @param string $path
     * @param Route $route
     * @return string
     */
    protected function createFileConfig(string $path, Route $route): string
    {
        $data = [
            'tags' => [],
            'summary' => $route->getName(),
            'description' => null,
            'operationId' => $route->getName(),
            'security' => config('docs-swagger.documentations.' . $this->docKey . '.default_security', []),
            'merge' => [],
        ];

        preg_match_all('/\{(.*?)\}/', $route->uri(), $parameters);
        $parameters = Arr::get($parameters, 1, []);

        $data['merge']['parameters'] = array_map(function ($parameter) {
            return [
                'in' => 'path',
                'name' => $parameter,
                'required' => true,
                'description' => $parameter,
                'schema' => [
                    'type' => 'integer',
                ],
            ];
        }, $parameters);

        return $this->createFile($path, $data);
    }
}
