<?php

namespace Sciarcinski\LaravelSwagger;

class Storage
{
    /** @var array */
    public array $data;

    /** @var string|null */
    public ?string $method;

    /** @var string|null */
    public ?string $url;

    /**
     * @param array $data
     * @param string|null $method
     * @param string|null $url
     */
    public function __construct(array $data, string $method = null, string $url = null)
    {
        $this->data = $data;
        $this->method = $method;
        $this->url = $url;
    }
}
