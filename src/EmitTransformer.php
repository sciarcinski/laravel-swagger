<?php

namespace Sciarcinski\LaravelSwagger;

trait EmitTransformer
{
    /**
     * @param Storage $path
     * @param array $transformers
     * @return void
     */
    public function emitTransformers(Storage $path, array $transformers = []): void
    {
        array_map(function ($transformer) use ($path) {
            if (method_exists($transformer, 'handle')) {
                $transformer = new $transformer($path);
                $transformer->handle();
            }
        }, $transformers);
    }
}
