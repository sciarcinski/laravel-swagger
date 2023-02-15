<?php

namespace Sciarcinski\LaravelSwagger;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionParameter;

class ProcessParameter
{
    /** @var ReflectionParameter */
    protected ReflectionParameter $parameter;

    /** @var array */
    protected array $rulesRequest = [];

    /** @var array */
    protected array $required = [];

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
    public function getRulesRequest(): array
    {
        return $this->rulesRequest;
    }

    /**
     * @return array
     */
    public function getRequired(): array
    {
        return $this->required;
    }

    /**
     * @return $this
     */
    public function process(): static
    {
        $type = $this->parameter->getType()->getName();

        if (Str::startsWith($type, 'App\Http\Requests')) {
            $this->rulesRequestProcess($type);
        }

        $this->requiredProcess();

        return $this;
    }

    /**
     * @return array
     */
    protected function requiredProcess(): array
    {
        $required = [];

        if (! empty($this->rulesRequest)) {
            foreach ($this->rulesRequest as $name => $rule) {
                if (Arr::get($rule, 'required', false)) {
                    $required[] = $name;
                }
            }
        }

        return $this->required = $required;
    }

    /**
     * @param string $requestName
     * @return array
     */
    public function rulesRequestProcess(string $requestName): array
    {
        $parameters = [];

        $rules = (new $requestName())->rules();

        foreach ($rules as $parameter => $values) {
            if (! is_array($values)) {
                $values = explode('|', $values);
            }

            $name = rtrim($parameter, '.*');

            $item = [
                'type' => $this->rulesRequestType($values),
                'required' => false,
            ];

            // required
            if (in_array('required', $values)) {
                $item['required'] = true;
            }

            // nullable
            if (in_array('nullable', $values)) {
                $item['nullable'] = true;
            }

            // array
            if (in_array('array', $values)) {
                $item['items']['type'] = 'string';
            }

            // single: array
            if (Str::endsWith($parameter, '.*')) {
                [$name, $item] = $this->rulesRequestProcessArraySingle($parameters, $name, $values);
            }

            // multi: array, object
            if (Str::contains($name, '.')) {
                [$name, $item] = $this->rulesRequestProcessArrayMulti($parameters, $name, $values);
            }

            $parameters[$name] = $item;
        }

        return $this->rulesRequest = $parameters;
    }

    /**
     * @param array $values
     * @return string
     */
    protected function rulesRequestType(array $values): string
    {
        $types = ['string', 'integer', 'boolean', 'array'];

        if (in_array('numeric', $values)) {
            return 'number';
        }

        foreach ($types as $type) {
            if (in_array($type, $values)) {
                return $type;
            }
        }

        return 'string';
    }

    /**
     * @param array $parameters
     * @param string $name
     * @param array $values
     * @return array
     */
    protected function rulesRequestProcessArraySingle(array $parameters, string $name, array $values): array
    {
        if (! Arr::has($parameters, $name)) {
            $parameters[$name] = [];
        }

        $item = $parameters[$name];
        $item['items']['type'] = 'string';

        return [$name, $item];
    }

    /**
     * @param array $parameters
     * @param string $name
     * @param array $values
     * @return array
     */
    protected function rulesRequestProcessArrayMulti(array $parameters, string $name, array $values): array
    {
        $names = explode('.', $name);

        $type = $this->getRulesParameterType($values);
        $item = $parameters[$names[0]];

        // is array
        if ('*' === $names[1]) {
            if ('object' !== $item['items']['type']) {
                $item['items']['type'] = 'object';
                $item['items']['properties'] = [];
            }

            $item['items']['properties'][$names[2]]['type'] = $type;

            if ('array' === $type) {
                $item['items']['properties'][$names[2]]['items']['type'] = 'string';
            }
        }
        // is object
        else {
            if ('object' !== $item['type']) {
                $item['type'] = 'object';
                unset($item['items']);
            }

            if (! isset($item['properties'][$names[1]])) {
                $item['properties'][$names[1]]['type'] = 'object';
            }

            if (! isset($names[2])) {
                $item['properties'][$names[1]]['type'] = $type;
            } else {
                $item['properties'][$names[1]]['properties'][$names[2]]['type'] = $type;
            }
        }

        return [$names[0], $item];
    }
}
