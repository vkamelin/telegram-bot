<?php

namespace App\Console\Commands;

use App\Console\Command;
use App\Console\Kernel;

class ListCommand extends Command
{
    public string $signature = 'list';
    public string $description = 'Список доступных команд';

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments, Kernel $kernel): int
    {
        echo "Доступные команды:" . PHP_EOL;
        foreach ($kernel->commands() as $cmd) {
            echo "  {$cmd->signature}\t{$cmd->description}" . PHP_EOL;
        }
        return 0;
    }
}
