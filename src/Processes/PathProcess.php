<?php

namespace Sciarcinski\LaravelSwagger\Processes;

use Illuminate\Support\Arr;
use Sciarcinski\LaravelSwagger\Storage;

class PathProcess
{
    /** @var Storage */
    protected Storage $storage;

    /** @var RouteProcess */
    protected RouteProcess $route;

    /**
     * @param RouteProcess $route
     */
    public function __construct(RouteProcess $route)
    {
        $this->route = $route;
    }

    /**
     * @return Storage
     */
    public function getStorage(): Storage
    {
        return $this->storage;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return '/' . $this->route->getRoute()->uri();
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return strtolower($this->route->getRoute()->methods()[0]);
    }

    /**
     * @return $this
     */
    public function process(): static
    {
        $data = [
            'tags' => $this->route->getTags(),
            'summary' => $this->route->getSummary(),
            'description' => $this->route->getDescription(),
            'operationId' => $this->route->getOperationId(),
            'security' => [$this->transformSecurity($this->route->getSecurity())],
            'responses' => $this->processResponses(),
        ];

        if ($this->route->isDeprecated()) {
            $data['deprecated'] = true;
        }

        if (in_array($this->getMethod(), ['post', 'put']) && ! empty($requestBody = $this->processRequestBody())) {
            $data['requestBody'] = [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => $requestBody,
                        ],
                    ],
                ],
            ];
        }

        if ($this->route->isMerge()) {
            $data = $this->transformMerge($data, $this->route->getMerge());
        }

        $this->storage = new Storage($data, $this->getMethod(), $this->getUrl());

        return $this;
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
     * @return array
     */
    protected function processResponses(): array
    {
        $responses = [];

        /** @var ResponseProcess $response */
        foreach ($this->route->getResponses() as $response) {
            $responses[$response->getCode()] = $response->getResponse();
        }

        return $responses;
    }

    /**
     * @return array
     */
    protected function processRequestBody(): array
    {
        $parameters = $this->route->getParameters();

        $query = [];

        /** @var ParameterProcess $parameter */
        foreach ($parameters as $parameter) {
            $query = array_merge($query, $parameter->getQuery());
        }

        return $query;
    }
}
