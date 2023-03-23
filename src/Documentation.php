<?php

namespace Sciarcinski\LaravelSwagger;

use Sciarcinski\LaravelSwagger\Processes\PathProcess;

class Documentation
{
    /** @var array */
    protected array $tags = [];

    /** @var array */
    protected array $components = [];

    /** @var array */
    protected array $paths = [];

    /**
     * @param string $pathDocJson
     * @return true
     */
    public function save(string $pathDocJson): bool
    {
        $doc = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'REST API',
                'version' => '1.0.0',
            ],
            'paths' => (object) [],
        ];

        if (! empty($this->tags)) {
            $doc['tags'] = [];

            foreach ($this->tags as $tag) {
                $doc['tags'][] = ['name' => $tag];
            }
        }

        if (! empty($this->paths)) {
            $doc['paths'] = [];

            /** @var PathProcess $path */
            foreach ($this->paths as $path) {
                $doc['paths'][$path->getUrl()][$path->getMethod()] = $path->all();
            }
        }

        if (! empty($this->components)) {
            $doc['components'] = $this->components;
        }

        file_put_contents($pathDocJson, json_encode($doc, JSON_PRETTY_PRINT));

        return true;
    }

    /**
     * @param array $tags
     * @return $this
     */
    public function setTags(array $tags): static
    {
        $this->tags = array_unique(array_merge($this->tags, $tags));

        return $this;
    }

    /**
     * @param array $components
     * @return $this
     */
    public function setComponents(array $components): static
    {
        $this->components = $components;

        return $this;
    }

    /**
     * @param PathProcess $path
     * @return $this
     */
    public function setPath(PathProcess $path): static
    {
        $this->paths[] = $path;

        return $this;
    }
}
