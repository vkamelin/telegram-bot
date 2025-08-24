<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Config;

final class RedisKeyHelper
{
    private function __construct()
    {
    }

    public static function key(string $context, string $entity, ?string $id = null): string
    {
        $config = Config::getInstance();
        $project = (string)$config->get('APP_NAME', 'telegram-bot');
        $env = (string)$config->get('APP_ENV', 'dev');
        $parts = [$project, $env, $context, $entity];
        if ($id !== null && $id !== '') {
            $parts[] = $id;
        }
        return implode(':', $parts);
    }
}

