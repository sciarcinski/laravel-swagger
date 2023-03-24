<?php

namespace Sciarcinski\LaravelSwagger;

class Path
{
    /** @var array */
    public array $data;

    /** @var string */
    public string $method;

    /** @var string */
    public string $url;

    /**
     * @param array $data
     * @param string $method
     * @param string $url
     */
    public function __construct(array $data, string $method, string $url)
    {
        $this->data = $data;
        $this->method = $method;
        $this->url = $url;
    }
}
