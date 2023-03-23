<?php

namespace Sciarcinski\LaravelSwagger;

use Evenement\EventEmitter;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionException;
use Sciarcinski\LaravelSwagger\Processes\ComponentsProcess;
use Sciarcinski\LaravelSwagger\Processes\PathProcess;
use Sciarcinski\LaravelSwagger\Processes\RouteProcess;

class Generator extends EventEmitter
{
    /** @var array */
    protected array $config = [
        'key' => 'api',
        'title' => 'API',
        'default_security' => [],
        'names' => [],
        'path_doc_json' => null,
        'path_components' => null,
        'path_routes' => null,
    ];

    /** @var RouteCollection */
    protected RouteCollection $routes;

    /** @var Documentation */
    protected Documentation $doc;

    /**
     * @param array $config
     * @param RouteCollection $routes
     * @param Documentation $doc
     */
    public function __construct(
        array $config,
        RouteCollection $routes,
        Documentation $doc
    ) {
        $this->config = array_merge($this->config, $config);
        $this->routes = $routes;
        $this->doc = $doc;
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
     * @return void
     */
    public function process(): void
    {
        $this->emit('start', [$this]);

        $this->components();
        $this->routes();

        $this->doc->save($this->config('path_doc_json'));

        $this->emit('finish', [$this]);
    }

    /**
     * @return void
     */
    public function components(): void
    {
        $components = [];
        $path = $this->config('path_components');

        if ($path && is_dir($path)) {
            $components = (new ComponentsProcess())->process($path);
            $this->emit('components', [&$components]);
        }

        if (! empty($components)) {
            $this->doc->setComponents($components);
        }
    }

    /**
     * @throws ReflectionException
     *
     * @return void
     */
    public function routes(): void
    {
        $path = $this->config('path_routes');
        $names = $this->config('names');

        foreach ($names as $name) {
            $route = new RouteProcess($this->routes->getByName($name), $path . $this->transformFileName($name) . '.json');
            $route->process();

            $routePath = new PathProcess($route);
            $routePath->process();

            $this->emit('progress', [$name, $route]);

            $this->doc->setTags($route->getTags());
            $this->doc->setPath($routePath);
        }
    }

    /**
     * @param string $name
     * @return string
     */
    protected function transformFileName(string $name): string
    {
        return Str::slug(Str::replace('.', '_', $name), '_');
    }
}
