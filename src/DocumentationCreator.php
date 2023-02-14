<?php

namespace Sciarcinski\LaravelSwagger;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DocumentationCreator
{
    /** @var Router */
    protected Router $router;

    /** @var string */
    protected string $doc = 'api';

    /** @var string */
    protected string $routeName;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $documentation
     * @return $this
     */
    public function setDoc(string $documentation): static
    {
        $this->doc = $documentation;

        return $this;
    }

    /**
     * @param string $routeName
     * @return $this
     */
    public function setRouteName(string $routeName): static
    {
        $this->routeName = $routeName;

        return $this;
    }

    /**
     * @param bool $routeResource
     * @return array
     */
    public function create(bool $routeResource = false): array
    {
        $routes = $routeResource ? $this->prepareResourceName() : [$this->routeName];

        foreach ($routes as $route) {
            $this->createFiles($route);
        }

        return $routes;
    }

    /**
     * @param string $routeName
     * @return array
     */
    protected function createFiles(string $routeName): array
    {
        /** @var Route $route */
        $route = $this->router->getRoutes()->getByName($routeName);

        $fileName = Str::replace('.', '_', $routeName);
        $fileName = Str::slug($fileName, '_');

        $filePathConfig = config('documentation.documentations.' . $this->doc . '.paths.configs') . $fileName . '.json';
        $filePathResponses = config('documentation.documentations.' . $this->doc . '.paths.responses') . $fileName . '.json';

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
     * @return array
     */
    protected function prepareResourceName(): array
    {
        $routes = [];

        foreach (['index', 'show', 'store', 'update', 'destroy'] as $item) {
            $route = $this->routeName . '.' . $item;

            if ($this->router->getRoutes()->hasNamedRoute($route)) {
                $routes[] = $route;
            }
        }

        return $routes;
    }
}
