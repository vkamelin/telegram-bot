<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;
use App\Console\Kernel;
use Phinx\Console\Command\Migrate as PhinxMigrate;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class MigrateRunCommand extends Command
{
    public string $signature = 'migrate:run';
    public string $description = 'Запустить миграции';

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments, Kernel $kernel): int
    {
        $application = new PhinxApplication();
        $command = new PhinxMigrate();
        $command->setApplication($application);

        $config = dirname(__DIR__, 3) . '/phinx.php';

        $input = new ArrayInput([
            '--configuration' => $config,
            '--environment' => 'default',
        ]);
        $output = new ConsoleOutput();

        return $command->run($input, $output);
    }
}
