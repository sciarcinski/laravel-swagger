<?php

namespace Sciarcinski\LaravelSwagger\Processes;

use Illuminate\Support\Arr;

class ResponseProcess
{
    /** @var string */
    protected string $code;

    /** @var mixed */
    protected mixed $data;

    /** @var array */
    protected array $response = [];

    /**
     * @param string $code
     * @param array $data
     */
    public function __construct(string $code, mixed $data)
    {
        $this->code = $code;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @return void
     */
    public function process(): void
    {
        if (is_string($this->data)) {
            $this->response = [
                '$ref' => $this->data,
            ];
        } else {
            $this->response['description'] = Arr::get($this->data, 'description', '');
            $this->response['content'] = $this->processContent(Arr::get($this->data, 'content', []));
        }
    }

    /**
     * @param array $items
     * @return array
     */
    protected function processContent(array $items): array
    {
        $content = [];

        foreach ($items as $key => $item) {
            if (! is_string($key)) {
                $key = 'application/json';
            }

            $content[$key] = $item;
        }

        return $content;
    }
}
