<?php

namespace Sciarcinski\LaravelSwagger;

use Evenement\EventEmitter;
use Illuminate\Support\Arr;
use ReflectionException;

class DocumentationGenerator extends EventEmitter
{
    use Pathable;

    /** @var array */
    protected array $tags = [];

    /**
     * @param array $tags
     * @return $this
     */
    public function setTags(array $tags): static
    {
        $this->tags = array_merge($this->tags, $tags);

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function generate(array $config)
    {
        $components = $this->prepareComponents();
        $paths = $this->preparePath($config['routes']);
        $tags = $this->prepareTagsGlobal($this->tags);

        $doc = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'REST API v1',
                'version' => '1.0.0',
            ],
            'tags' => $tags,
            'paths' => $paths,
            'components' => $components,
        ];

        file_put_contents($this->getPathDocJson(), json_encode($doc, JSON_PRETTY_PRINT));
    }

    /**
     * @return array
     */
    protected function prepareComponents(): array
    {
        $files = glob($this->getPathComponents() . '**/*.json');

        $components = [];

        foreach ($files as $file) {
            $component = $this->loadFile($file);

            $names = substr($file, strlen($this->getPathComponents()), -5);
            $names = explode('/', $names);

            if (! Arr::has($components, $names[0])) {
                $components[$names[0]] = [];
            }

            $components[$names[0]][$names[1]] = $component;
        }

        return $components;
    }

    /**
     * @param array $routes
     *
     * @throws ReflectionException
     *
     * @return array
     */
    protected function preparePath(array $routes): array
    {
        $paths = [];

        foreach ($routes as $routeName) {
            $fileName = $this->transformFileName($routeName);
            $filePathConfig = $this->getPathConfig() . $fileName . '.json';
            $filePathResponses = $this->getPathResponses() . $fileName . '.json';

            $router = new ProcessRouter($routeName);
            $router->process();

            $responses = $this->loadFile($filePathResponses);
            $responses = $this->transformRresponses($responses, $router);

            $config = $this->loadFile($filePathConfig);
            $description = Arr::get($config, 'description');

            $path = [
                'tags' => [],
                'summary' => Arr::get($config, 'summary', $routeName),
                'operationId' => Arr::get($config, 'operationId', $routeName),
                'description' => $description ?? '',
                'responses' => $responses,
                'parameters' => [],
                'security' => [],
            ];

            if (Arr::has($config, 'tags')) {
                $this->setTags($tags = $this->transformTagsPath($config['tags']));
                $path['tags'] = $tags;
            }

            if (Arr::has($config, 'security') && ! empty($config['security'])) {
                $path['security'] = [$this->transformSecurity($config['security'])];
            }

            if (Arr::has($config, 'deprecated')) {
                $path['deprecated'] = $config['deprecated'];
            }

            if (! empty($router->getRulesRequest()) && in_array($router->getMethod(), ['post', 'put'])) {
                $path['requestBody'] = $this->transformRequestBody($router->getRequired(), $router->getRulesRequest());
            }

            if (Arr::has($config, 'merge')) {
                $path = $this->transformMerge($path, $config['merge']);
            }

            $paths[$router->getUrl()][$router->getMethod()] = $path;
        }

        return $paths;
    }

    /**
     * @param array $tags
     * @return array
     */
    protected function prepareTagsGlobal(array $tags): array
    {
        $tags = array_unique($tags);

        $items = [];

        foreach ($tags as $tag) {
            $items[] = [
                'name' => $tag,
            ];
        }

        return $items;
    }

    /**
     * @param string $path
     * @return array
     */
    protected function loadFile(string $path): array
    {
        $data = file_get_contents($path);
        $data = json_decode($data, true);

        return $data;
    }

    /**
     * @param array $responses
     * @param ProcessRouter $router
     * @return array
     */
    protected function transformRresponses(array $responses, ProcessRouter $router): array
    {
        foreach ($responses as $code => $response) {
            if (is_string($response)) {
                $responses[$code] = [
                    '$ref' => $response,
                ];
            } else {
                $code = strval($code);

                $responses[$code] = [
                    'description' => Arr::get($response, 'description') ?? '',
                ];

                $contents = Arr::get($response, 'content', []);

                foreach ($contents as $content) {
                    $responses[$code]['content'][$content['type']] = [];

                    if (Arr::has($content, 'example')) {
                        $responses[$code]['content'][$content['type']]['example'] = $content['example'];
                    }
                }
            }
        }

        return $responses;
    }

    /**
     * @param array|string $tags
     * @return array
     */
    protected function transformTagsPath(array|string $tags): array
    {
        if (is_array($tags)) {
            return $tags;
        }

        return [$tags];
    }

    /**
     * @param array $required
     * @param array $rules
     * @return array
     */
    protected function transformRequestBody(array $required = [], array $rules = []): array
    {
        return [
            'required' => ! empty($required),
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => array_map(function ($rule) {
                            return Arr::except($rule, 'required');
                        }, $rules),
                        'required' => $required,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $path
     * @param array $merges
     * @return array
     */
    protected function transformMerge(array $path, array $merges): array
    {
        foreach ($merges as $key => $merge) {
            if (! Arr::has($path, $key)) {
                $path[$key] = [];
            }

            if (is_array($path[$key])) {
                $path[$key] = array_merge($path[$key], $merges[$key]);
            }
        }

        return $path;
    }

    /**
     * @param array $security
     * @return array
     */
    protected function transformSecurity(array $security = []): array
    {
        $items = [];

        foreach ($security as $key => $item) {
            if (is_numeric($key)) {
                $items[$item] = [];
            } else {
                $items[$key] = [$item];
            }
        }

        return $items;
    }
}
