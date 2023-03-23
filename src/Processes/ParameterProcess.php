<?php

namespace Sciarcinski\LaravelSwagger\Processes;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionParameter;

class ParameterProcess
{
    /** @var ReflectionParameter */
    protected ReflectionParameter $parameter;

    /** @var string */
    protected string $type;

    /** @var array */
    protected array $path = [];

    /** @var array */
    protected array $query = [];

    /**
     * @param ReflectionParameter $parameter
     */
    public function __construct(ReflectionParameter $parameter)
    {
        $this->parameter = $parameter;
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @return $this
     */
    public function process(): static
    {
        $this->type = $this->determineType();

        if ($this->type === 'class') {
            $class = $this->parameter->getType()->getName();

            if (method_exists($class, 'rules')) {
                $this->query = $this->query((new $class())->rules());
            }
        } else {
            $this->path = [
                'position' => $this->parameter->getPosition(),
                'name' => $this->parameter->getName(),
                'type' => $this->queryType([$this->type]),
            ];
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function determineType(): string
    {
        $type = $this->parameter->getType();

        if (is_null($type)) {
            return 'int';
        }

        $name = $type->getName();

        if (class_exists($name)) {
            return 'class';
        }

        return $name;
    }

    /**
     * @param array $rules
     * @return array
     */
    public function query(array $rules = []): array
    {
        $query = [];

        foreach ($rules as $rule => $values) {
            [$name, $item] = $this->queryRule($query, $rule, $values);
            $query[$name] = $item;
        }

        return $query;
    }

    /**
     * @param array $query
     * @param string $rule
     * @param mixed $values
     * @return array
     */
    protected function queryRule(array $query, string $rule, mixed $values): array
    {
        if (! is_array($values)) {
            $values = explode('|', $values);
        }

        $name = rtrim($rule, '.*');
        $name = $rule;

        // TODO required
        $item = [
            'type' => $this->queryType($values),
            //'required' => false,
        ];

        // required
        if (in_array('required', $values)) {
            //$item['required'] = true;
        }

        // nullable
        if (in_array('nullable', $values)) {
            $item['nullable'] = true;
        }

        // array, object
        if (Str::contains($name, '.')) {
            [$name, $item] = $this->queryArray($query, $name, $values);
        }

        return [$name, $item];
    }

    /**
     * - type: string
     * - type: number
     * - type: integer
     * - type: boolean
     * - type: array
     * - type: object
     *
     * @param array $values
     * @return string
     */
    protected function queryType(array $values): string
    {
        if (in_array('numeric', $values)) {
            return 'number';
        }

        if (in_array('int', $values)) {
            return 'integer';
        }

        $types = ['string', 'integer', 'boolean', 'array'];

        foreach ($types as $type) {
            if (in_array($type, $values)) {
                return $type;
            }
        }

        return 'string';
    }

    /**
     * @param array $query
     * @param string $name
     * @return array
     */
    protected function queryFindOrNew(array $query, string $name): array
    {
        if (! Arr::has($query, $name)) {
            $query[$name] = [];
            $query[$name]['type'] = 'object';
            $query[$name]['properties'] = [];
        }

        return $query[$name];
    }

    /**
     * @param array $query
     * @param string $name
     * @param array $values
     * @return array
     */
    protected function queryArray(array $query, string $name, array $values = []): array
    {
        $names = explode('.', $name);
        $parent = array_shift($names);
        $element = array_pop($names);

        if ($element === '*') {
            $item = $this->queryArrayProperties([], $names, $element, $values);
            $item = array_shift($item);
        } else {
            $item = $this->queryFindOrNew($query, $parent);

            if (! empty($names)) {
                if (! Arr::has($item, 'properties')) {
                    $item['type'] = 'object';
                    $item['properties'] = [];
                }

                $item['properties'] = $this->queryArrayProperties($item['properties'], $names, $element, $values);
            }
        }

        return [$parent, $item];
    }

    /**
     * @param array $query
     * @param array $names
     * @param string $element
     * @param array $values
     * @return array
     */
    protected function queryArrayProperties(array $query, array $names, string $element, array $values = []): array
    {
        $name = array_shift($names);

        if (empty($names)) {
            if (! Arr::has($query, $name)) {
                $query[$name] = [];
            }

            $rule = $this->queryRule([], $element, $values)[1];

            if ($element === '*') {
                $query[$name] = array_merge($query[$name], $rule);
                $query[$name]['type'] = 'array';
                $query[$name]['items']['type'] = $rule['type'];
            } else {
                $query[$name]['type'] = 'object';
                $query[$name]['properties'][$element] = $rule;
            }

            return $query;
        }

        $query[$name] = $this->queryFindOrNew($query, $name);
        $query[$name]['properties'] = $this->queryArrayProperties($query[$name]['properties'], $names, $element, $values);

        return $query;
    }
}
