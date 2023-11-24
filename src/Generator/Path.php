<?php

namespace Sciarcinski\LaravelSwagger\Generator;

use Illuminate\Support\Arr;

class Path
{
    /** @var Data */
    protected Data $data;

    /** @var Name */
    protected Name $name;

    /**
     * @param Name $name
     */
    public function __construct(Name $name)
    {
        $this->name = $name;
    }

    /**
     * @return Data
     */
    public function getData(): Data
    {
        return $this->data;
    }

    /**
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getConfig(string $key = null, mixed $default = null): mixed
    {
        return $this->name->config($key, $default);
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return '/' . $this->name->getRoute()->uri();
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return strtolower($this->name->getRoute()->methods()[0]);
    }

    /**
     * @return $this
     */
    public function process(): static
    {
        $data = [
            'tags' => $this->name->getTags(),
            'summary' => $this->name->getSummary(),
            'description' => $this->name->getDescription(),
            'operationId' => $this->name->getOperationId(),
            'security' => [$this->transformSecurity($this->name->getSecurity())],
            'responses' => $this->processResponses(),
        ];

        if ($this->name->isDeprecated()) {
            $data['deprecated'] = true;
        }

        if (in_array($this->getMethod(), ['post', 'put']) && ! empty($requestBody = $this->processRequestBody())) {
            $data['requestBody'] = [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => $requestBody,
                    ],
                ],
            ];
        }

        if ($this->name->isMerge()) {
            $data = $this->transformMerge($data, $this->name->getMerge());
        }

        $this->data = new Data($data, $this->getMethod(), $this->getUrl(), $this->getConfig());

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

        /** @var Response $response */
        foreach ($this->name->getResponses() as $response) {
            $responses[$response->getCode()] = $response->getResponse();
        }

        return $responses;
    }

    /**
     * @return array
     */
    protected function processRequestBody(): array
    {
        $parameters = $this->name->getParameters();

        $query = [];

        /** @var Parameter $parameter */
        foreach ($parameters as $parameter) {
            $query = array_merge($query, $parameter->getQuery());
        }

        return $query;
    }
}
