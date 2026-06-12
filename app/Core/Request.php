<?php
declare(strict_types=1);

namespace App\Core;

class Request
{
    public function __construct() {}

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        $path = is_string($path) ? $path : '/';

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        return $path !== '' ? $path : '/';
    }

    public function get(string $key, string $default = ''): string
    {
        $value = filter_input(INPUT_GET, $key, FILTER_UNSAFE_RAW);

        if ($value === null || $value === false) {
            return $default;
        }

        return (string) $value;
    }

    public function post(string $key, string $default = ''): string
    {
        $value = filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW);

        if ($value === null || $value === false) {
            return $default;
        }

        return (string) $value;
    }

    public function server(string $key, string $default = ''): string
    {
        return (string) ($_SERVER[$key] ?? $default);
    }

    public function json(): array
    {
        $raw = (string) file_get_contents('php://input');

        if ($raw === '') {
            return [];
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }
}
