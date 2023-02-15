<?php

namespace Sciarcinski\LaravelSwagger;

use Evenement\EventEmitter;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DocumentationGenerator extends EventEmitter
{
    use Pathable;

    public function generate(array $config)
    {
        $paths = [];
        $tags = [];

        foreach ($config['routes'] as $routeName) {
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
            ];

            if (Arr::has($config, 'tags')) {
                $tags = array_merge($this->transformTags($config['tags']), $tags);
                $path['tags'] = $tags;
            }

            if (Arr::has($config, 'deprecated')) {
                $path['deprecated'] = $config['deprecated'];
            }

            $rules = $router->getRulesRequest();

            if (! empty($rules) && in_array($router->getMethod(), ['post', 'put'])) {
                $path['requestBody'] = [
                    'required' => ! empty($router->getRequired()),
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => array_map(function ($rule) {
                                    unset($rule['required']);

                                    return $rule;
                                }, $rules),
                            ],
                        ],
                    ],
                ];
            }

            if (Arr::has($config, 'merge')) {
                foreach ($config['merge'] as $key => $merge) {
                    if (! Arr::has($path, $key)) {
                        $path[$key] = [];
                    }

                    if (is_array($path[$key])) {
                        $path[$key] = array_merge($path[$key], $config['merge'][$key]);
                    }
                }
            }

            $paths[$router->getUrl()][$router->getMethod()] = $path;
        }

        $doc = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'REST API v1',
                'version' => '1.0.0',
            ],
            'tags' => $tags,
            'paths' => $paths,
        ];

        file_put_contents('/var/www/f24-api/docs/api/api.json', json_encode($doc, JSON_PRETTY_PRINT));
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
            if (is_string($response) && Str::startsWith($response, '#components')) {
                $response = substr($response, 12);
                $response = $this->getPathComponents() . $response;
                $response = $this->loadFile($response);
            }

            $code = strval($code);

            $responses[$code] = [
                'description' => Arr::get($response, 'description') ?? '',
                'content' => [],
            ];

            $contents = Arr::get($response, 'content', []);

            foreach ($contents as $content) {
                $responses[$code]['content'][$content['type']] = [];

                if (Arr::has($content, 'example')) {
                    $responses[$code]['content'][$content['type']]['example'] = $content['example'];

                    if (Arr::get($content, 'example.errors') === 'required') {
                        $responses[$code]['content'][$content['type']]['example']['errors'] = $this->transformRequired(
                            $router->getRequired()
                        );
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
    protected function transformTags(array|string $tags): array
    {
        if (is_array($tags)) {
            return $tags;
        }

        return [$tags];
    }

    /**
     * @param array $required
     * @return array
     */
    protected function transformRequired(array $required = []): array
    {
        $errors = [];

        foreach ($required as $key) {
            $errors[$key] = [
                'The ' . $key . ' field is required.',
            ];
        }

        return $errors;
    }
}
