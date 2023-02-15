<?php

namespace Sciarcinski\LaravelSwagger;

use Illuminate\Support\Str;

trait Pathable
{
    /** @var string */
    protected string $docKey = 'api';

    /**
     * @param string $documentation
     * @return $this
     */
    public function setDocKey(string $docKey): static
    {
        $this->docKey = $docKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getPathConfig(): string
    {
        return config('documentation.documentations.' . $this->docKey . '.paths.configs');
    }

    /**
     * @return string
     */
    public function getPathResponses(): string
    {
        return config('documentation.documentations.' . $this->docKey . '.paths.responses');
    }

    /**
     * @return string
     */
    public function getPathComponents(): string
    {
        return config('documentation.documentations.' . $this->docKey . '.paths.components');
    }

    /**
     * @param string $name
     * @return string
     */
    public function transformFileName(string $name): string
    {
        $name = Str::replace('.', '_', $name);
        $name = Str::slug($name, '_');

        return $name;
    }
}
