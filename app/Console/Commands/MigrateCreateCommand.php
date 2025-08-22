<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;
use App\Console\Kernel;
use Phinx\Console\Command\Create as PhinxCreate;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class MigrateCreateCommand extends Command
{
    public string $signature = 'migrate:create';
    public string $description = 'Создать новую миграцию';

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments, Kernel $kernel): int
    {
        $name = $arguments[0] ?? null;
        if ($name === null || $name === '') {
            echo 'Migration name required.' . PHP_EOL;
            return 1;
        }

        $application = new PhinxApplication();
        $command = new PhinxCreate();
        $command->setApplication($application);

        $config = dirname(__DIR__, 3) . '/phinx.php';

        $input = new ArrayInput([
            'name' => $name,
            '--configuration' => $config,
        ]);
        $output = new ConsoleOutput();

        return $command->run($input, $output);
    }
}
