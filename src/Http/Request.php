<?php

namespace App\Http;

class Request
{
    private string $method;
    private string $path;
    private array $query;
    private array $body;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $this->query = $_GET;
        $this->body = $_POST;
    }

    public function method(): string
    {
        return strtoupper($this->method);
    }

    public function path(): string
    {
        return rtrim($this->path, '/') === '' ? '/' : rtrim($this->path, '/');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }
}
