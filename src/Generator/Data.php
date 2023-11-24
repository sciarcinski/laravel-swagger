<?php

namespace Sciarcinski\LaravelSwagger\Generator;

class Data
{
    public function __construct(
        public array $items,
        public ?string $method = null,
        public ?string $url = null,
        public array $config = []
    ) {
    }
}
