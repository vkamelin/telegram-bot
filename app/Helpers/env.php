<?php

declare(strict_types=1);

if (!function_exists('env')) {
    /**
     * Retrieves an environment variable or returns default value.
     *
     * @param string $key     The environment variable name
     * @param mixed  $default The default value if key not found
     *
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        $lower = strtolower((string)$value);
        return match ($lower) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $value,
        };
    }
}
