<?php

namespace Sciarcinski\LaravelSwagger\Generator;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionParameter;

class Parameter
{
    /** @var ReflectionParameter */
    protected ReflectionParameter $parameter;

    /** @var string */
    protected string $rulesKey;

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
        $this->rulesKey = Str::random();
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
                $root = $this->ruleRoot((new $class())->rules());

                if ($root->hasChildren()) {
                    $this->query = $root->toArray();
                }
            }
        } else {
            $this->path = [
                'position' => $this->parameter->getPosition(),
                'name' => $this->parameter->getName(),
                'type' => Rule::determineType([$this->type]),
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
     * @return Rule
     */
    protected function ruleRoot(array $rules = []): Rule
    {
        foreach ($rules as $rule => $values) {
            $rules[$rule] = [
                $this->rulesKey => $values,
            ];
        }

        ksort($rules);

        $rules = Arr::undot($rules);
        $rules = [$this->rulesKey => $rules];

        return $this->rulesConvertToTree($rules)[$this->rulesKey];
    }

    /**
     * @param array $rules
     * @param Rule|null $parent
     * @return array
     */
    protected function rulesConvertToTree(array $rules = [], Rule $parent = null): array
    {
        $items = [];

        foreach ($rules as $rule => $values) {
            $item = new Rule($rule, $parent);

            if (is_array($values)) {
                if (Arr::has($values, $this->rulesKey)) {
                    $item->setValues($values[$this->rulesKey]);
                    Arr::forget($values, $this->rulesKey);
                }

                if (! empty($values)) {
                    $item->setChildren($this->rulesConvertToTree($values, $item));
                }
            }

            $items[$rule] = $item;
        }

        return $items;
    }
}
