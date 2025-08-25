<?php
declare(strict_types=1);

if (!function_exists('url')) {
    /**
     * Prepends base path to given URI.
     */
    function url(string $path): string
    {
        $base = rtrim($_ENV['BASE_PATH'] ?? '', '/');
        return $base . '/' . ltrim($path, '/');
    }
}

