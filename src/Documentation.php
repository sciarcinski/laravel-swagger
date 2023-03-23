<?php

namespace Sciarcinski\LaravelSwagger;

use Sciarcinski\LaravelSwagger\Processes\PathProcess;

class Documentation
{
    /** @var string */
    protected string $title = 'API';

    /** @var string */
    protected string $description;

    /** @var string */
    protected string $version = '1.0.0';

    /** @var array */
    protected array $servers = [];

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
                'title' => $this->title,
                'version' => $this->version,
            ],
            'paths' => (object) [],
        ];

        if (! empty($this->description)) {
            $doc['info']['description'] = $this->description;
        }

        if (! empty($this->servers)) {
            $doc['servers'] = $this->servers;
        }

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

        file_put_contents($pathDocJson, json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return true;
    }

    /**
     * @param string $version
     * @return $this
     */
    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param string $url
     * @param string $description
     * @return $this
     */
    public function setServer(string $url, string $description): static
    {
        $this->servers[] = [
            'url' => $url,
            'description' => $description,
        ];

        return $this;
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
