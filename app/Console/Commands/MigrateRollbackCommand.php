<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;
use App\Console\Kernel;
use Phinx\Console\Command\Rollback as PhinxRollback;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class MigrateRollbackCommand extends Command
{
    public string $signature = 'migrate:rollback';
    public string $description = 'Откатить миграции';

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments, Kernel $kernel): int
    {
        $application = new PhinxApplication();
        $command = new PhinxRollback();
        $command->setApplication($application);

        $config = dirname(__DIR__, 3) . '/phinx.php';
        $env = $_ENV['APP_ENV'] ?? 'development';

        $input = new ArrayInput([
            '--configuration' => $config,
            '--environment' => $env,
        ]);
        $output = new ConsoleOutput();

        return $command->run($input, $output);
    }
}
