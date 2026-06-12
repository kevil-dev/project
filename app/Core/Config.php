<?php
declare(strict_types=1);

namespace App\Core;

class Config
{
    private static ?Config $instance = null;

    private array $data = [];

    private function __construct()
    {
        $this->data = require dirname(__DIR__, 2) . '/config/config.php';
    }

    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new Config();
        }

        return self::$instance;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $value = $this->data;

        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }

        return $value;
    }
}
