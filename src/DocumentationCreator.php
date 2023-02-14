<?php

namespace Sciarcinski\LaravelSwagger;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DocumentationCreator
{
    use Pathable;

    /** @var Router */
    protected Router $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param bool $routeResource
     * @return array
     */
    public function create(string $routeName, bool $routeResource = false): array
    {
        $routes = $routeResource ? $this->prepareResourceName($routeName) : [$routeName];

        foreach ($routes as $route) {
            $this->createFiles($route);
        }

        return $routes;
    }

    /**
     * @param string $docName
     * @param string $routeName
     * @return array
     */
    protected function createFiles(string $routeName): array
    {
        /** @var \Illuminate\Routing\Route $route */
        $route = $this->router->getRoutes()->getByName($routeName);

        $fileName = Str::replace('.', '_', $routeName);
        $fileName = Str::slug($fileName, '_');

        $filePathConfig = $this->getPathConfig() . $fileName . '.json';
        $filePathResponses = $this->getPathResponses() . $fileName . '.json';

        $files = [];

        if (! file_exists($filePathConfig)) {
            $files[] = $this->createFileConfig($filePathConfig, $route);
        }

        if (! file_exists($filePathResponses)) {
            $files[] = $this->createFileResponses($filePathResponses, $route);
        }

        return $files;
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
        $data = ['merge' => []];

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

    /**
     * @param string $path
     * @param Route $route
     * @return string
     */
    protected function createFileResponses(string $path, Route $route): string
    {
        $data = [];

        switch ($route->getActionMethod()) {
            case 'index':
                $data['200'] = [];
                $data['401'] = 'components/response_unauthorized.json';
                break;

            case 'show':
                $data['200'] = [];
                $data['401'] = 'components/response_unauthorized.json';
                $data['404'] = 'components/response_model_not_found.json';
                break;

            case 'store':
                $data['201'] = [];
                $data['401'] = 'components/response_unauthorized.json';
                $data['422'] = 'components/response_invalidation.json';
                break;

            case 'update':
                $data['200'] = [];
                $data['401'] = 'components/response_unauthorized.json';
                $data['404'] = 'components/response_model_not_found.json';
                $data['422'] = 'components/response_invalidation.json';
                break;

            case 'destroy':
                $data['204'] = [];
                $data['401'] = 'components/response_unauthorized.json';
                $data['404'] = 'components/response_model_not_found.json';
                break;
        }

        return $this->createFile($path, $data);
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

            if ($this->router->getRoutes()->hasNamedRoute($route)) {
                $routes[] = $route;
            }
        }

        return $routes;
    }
}
