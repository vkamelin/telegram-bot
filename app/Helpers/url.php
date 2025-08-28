<?php

declare(strict_types=1);

if (!function_exists('url')) {
    /**
     * Prepends base path to given URI.
     * Accepts null and returns base path in that case.
     */
    function url(?string $path): string
    {
        $base = rtrim($_ENV['BASE_PATH'] ?? '', '/');
        $normalized = $path === null ? '' : ltrim($path, '/');
        return $base . '/' . $normalized;
    }
}
