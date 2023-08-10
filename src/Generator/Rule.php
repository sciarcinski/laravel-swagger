<?php

namespace Sciarcinski\LaravelSwagger\Generator;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Rule
{
    /** @var string */
    protected string $key;

    /** @var Rule|null */
    protected ?Rule $parent;

    /** @var array */
    protected array $values = [];

    /** @var string */
    protected string $type;

    /** @var array */
    protected array $required = [];

    /** @var array|null */
    protected ?array $children;

    /** @var bool */
    protected bool $nullable = false;

    /** @var string */
    protected string $itemsType = 'string';

    /** @var array */
    protected array $itemsEnum = [];

    /**
     * @param string $key
     * @param Rule|null $parent
     */
    public function __construct(string $key, Rule $parent = null)
    {
        $this->key = $key;
        $this->parent = $parent;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return bool
     */
    public function getNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getRequired(): array
    {
        return $this->required;
    }

    /**
     * @return bool
     */
    public function hasChildren(): bool
    {
        return ! empty($this->children);
    }

    /**
     * @param array|string $values
     * @return $this
     */
    public function setValues(array|string $values): static
    {
        if (! is_array($values)) {
            $values = explode('|', $values);
        }

        if (in_array('required', $values)) {
            $values = array_values(array_diff($values, ['required']));
            $this->parent?->setRequired($this->key);
        }

        if (in_array('nullable', $values)) {
            $this->nullable = true;
        }

        $this->values = $values;
        $this->type = static::determineType($values);
        $this->itemsEnum = $this->resolveItemsEnum($values);

        return $this;
    }

    /**
     * @param array $children
     * @return $this
     */
    public function setChildren(array $children): static
    {
        if (Arr::has($children, '*')) {
            $children = $children['*'];

            $this->type = 'array';
            $this->itemsType = $children->getType();
        } else {
            $this->children = $children;
            $this->type = 'object';
        }

        return $this;
    }

    /**
     * @param string $rule
     * @return $this
     */
    public function setRequired(string $rule): static
    {
        $this->required[] = $rule === '*' ? $this->getKey() : $rule;
        $this->required = array_unique($this->required);

        $this->parent?->setRequired($this->key);

        return $this;
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
    public static function determineType(array $values): string
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
     * @param array $values
     * @return array
     */
    protected function resolveItemsEnum(array $values): array
    {
        foreach ($values as $value) {
            if (! is_string($value)) {
                $value = (string) $value;
            }

            if (Str::startsWith($value, 'in:')) {
                $value = explode(',', substr($value, 3));
                $value = array_map(fn ($val) => trim($val, '"'), $value);

                return $value;
            }
        }

        return [];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->getType(),
            'nullable' => $this->getNullable(),
        ];

        if ($data['type'] === 'array') {
            $data['items'] = [
                'type' => $this->itemsType,
            ];
        }

        if (! empty($this->itemsEnum)) {
            $data['enum'] = $this->itemsEnum;
        }

        if (! empty($this->children)) {
            $data['properties'] = [];

            /** @var Rule $child */
            foreach ($this->children as $child) {
                $data['properties'][$child->getKey()] = $child->toArray();
            }

            if (! empty($this->getRequired())) {
                $data['required'] = $this->getRequired();
            }
        }
        //dd($this);
        return $data;
    }
}
