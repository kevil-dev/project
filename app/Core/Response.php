<?php
declare(strict_types=1);

namespace App\Core;

class Response
{
    private int $statusCode = 200;

    private string $body = '';

    private array $headers = [];

    public function setStatus(int $code): void
    {
        $this->statusCode = $code;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function setHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    public function json(array $data): void
    {
        $this->body = (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->headers['Content-Type'] = 'application/json; charset=utf-8';
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->body;
    }
}
