<?php

namespace App\Console\Commands;

use App\Console\Command;
use App\Console\Kernel;

class HelpCommand extends Command
{
    public string $signature = 'help';

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments, Kernel $kernel): int
    {
        $name = $arguments[0] ?? null;
        if (!$name) {
            echo "Usage: help <command>" . PHP_EOL;
            return 0;
        }

        foreach ($kernel->commands() as $cmd) {
            if ($cmd->signature === $name) {
                echo "{$cmd->signature} - {$cmd->description}" . PHP_EOL;
                return 0;
            }
        }

        echo "Command \"{$name}\" not found." . PHP_EOL;
        return 1;
    }
}
