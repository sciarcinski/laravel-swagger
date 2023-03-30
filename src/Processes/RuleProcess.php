<?php

namespace Sciarcinski\LaravelSwagger\Processes;

use Illuminate\Support\Arr;

class RuleProcess
{
    /** @var string */
    protected string $rule;

    /** @var RuleProcess|null */
    protected ?RuleProcess $parent;

    /** @var array */
    protected array $values = [];

    /** @var string */
    protected string $type;

    /** @var array|null */
    protected ?array $required;

    /** @var array|null */
    protected ?array $children;

    /** @var bool */
    protected bool $nullable = false;

    /** @var string */
    protected string $itemsType = 'string';

    /**
     * @param string $rule
     * @param RuleProcess|null $parent
     */
    public function __construct(string $rule, RuleProcess $parent = null)
    {
        $this->rule = $rule;
        $this->parent = $parent;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param array $values
     * @return $this
     */
    public function setValues(array $values): static
    {
        if (in_array('required', $values)) {
            $values = array_values(array_diff($values, ['required']));
            $this->parent?->setRequired($this->rule);
        }

        if (in_array('nullable', $values)) {
            $this->nullable = true;
        }

        $this->values = $values;
        $this->type = static::determineType($values);

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
        if ($rule !== '*') {
            $this->required[] = $rule;
            $this->required = array_unique($this->required);
        }

        $this->parent?->setRequired($this->rule);

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
}
