<?php

namespace Sciarcinski\LaravelSwagger\Processes;

use Illuminate\Support\Arr;

class ComponentsProcess
{
    /**
     * @param string $path
     * @return array
     */
    public function process(string $path): array
    {
        $files = glob($path . '**/*.json');

        $components = [];

        foreach ($files as $file) {
            $component = $this->loadFile($file);

            $names = substr($file, strlen($path), -5);
            $names = explode('/', $names);

            if (! Arr::has($components, $names[0])) {
                $components[$names[0]] = [];
            }

            $components[$names[0]][$names[1]] = $component;
        }

        return $components;
    }

    /**
     * @param string $path
     * @return array
     */
    protected function loadFile(string $path): array
    {
        $data = file_get_contents($path);
        $data = json_decode($data, true);

        return $data;
    }
}
