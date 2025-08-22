<?php

declare(strict_types=1);

namespace App;

/**
 * Simple configuration loader providing access to values
 * from the application config array and environment variables.
 */
final class Config
{
    /**
     * Loaded configuration values.
     *
     * @var array<string, mixed>
     */
    private array $data;

    private static ?self $instance = null;

    /**
     * @phpstan-ignore-next-line
     */
    private function __construct()
    {
        /** @var array<string, mixed> $config */
        $config = require __DIR__ . '/Config/config.php';
        $this->data = $config;
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Retrieve a configuration value.
     *
     * Environment variables take precedence over values from the
     * configuration array. If the key is not found, the provided default
     * value is returned.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        return $this->data[$key] ?? $default;
    }
}
