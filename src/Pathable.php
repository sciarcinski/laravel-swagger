<?php

namespace Sciarcinski\LaravelSwagger;

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
}
