<?php

namespace Sciarcinski\LaravelSwagger\Generator;

class Data
{
    /** @var array */
    public array $items;

    /** @var string|null */
    public ?string $method;

    /** @var string|null */
    public ?string $url;

    /**
     * @param array $items
     * @param string|null $method
     * @param string|null $url
     */
    public function __construct(array $items, string $method = null, string $url = null)
    {
        $this->items = $items;
        $this->method = $method;
        $this->url = $url;
    }
}
