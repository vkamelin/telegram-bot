<?php

namespace App\Console;

use Dotenv\Dotenv;

abstract class Command
{
    public string $signature = '';
    public string $description = '';

    /**
     * @param array<int, string> $arguments
     */
    abstract public function handle(array $arguments, Kernel $kernel): int;

    /**
     * @param array<int, string> $arguments
     */
    public function run(array $arguments, Kernel $kernel): int
    {
        Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();

        return $this->handle($arguments, $kernel);
    }
}
