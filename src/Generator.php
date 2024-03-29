<?php

namespace Sciarcinski\LaravelSwagger;

use Evenement\EventEmitter;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionException;
use Sciarcinski\LaravelSwagger\Generator\Components;
use Sciarcinski\LaravelSwagger\Generator\Name;
use Sciarcinski\LaravelSwagger\Generator\Path;

class Generator extends EventEmitter
{
    use Module;

    /** @var array */
    protected array $config = [
        'key' => 'api',
        'title' => 'API',
        'description' => '',
        'version' => '1.0.0',
        'default_security' => [],
        'names' => [],
        'path_doc_json' => null,
        'path_components' => null,
        'path_routes' => null,
        'generators' => [],
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

        $this->doc->setTitle($this->config('title'));
        $this->doc->setDescription($this->config('description'));
        $this->doc->setVersion($this->config('version'));
        $this->doc->save($this->config('path_doc_json'));

        $this->emit('finish', [$this]);
    }

    /**
     * @return void
     */
    protected function components(): void
    {
        $components = [];
        $path = Str::finish($this->config('path_components'), '/');

        if ($path && is_dir($path)) {
            $components = (new Components())->process($path);
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
    protected function routes(): void
    {
        $path = $this->config('path_routes');
        $names = $this->config('names');

        foreach ($names as $name) {
            $name = new Name($this->routes->getByName($name), $path . $this->transformFileName($name) . '.json');
            $name->process();

            $data = (new Path($name))
                ->process()
                ->getData();

            $this->module($data, $this->config('generators'));
            $this->emit('progress', [$name, $name]);

            $this->doc->setTags($name->getTags());
            $this->doc->setPath($data);
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
