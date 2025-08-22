<?php

namespace App\Console;

class Kernel
{
    /**
     * @var array<class-string<Command>>
     */
    protected array $commands = [
        Commands\ListCommand::class,
        Commands\HelpCommand::class,
        Commands\CacheClearCommand::class,
        Commands\RefreshTokenPurgeCommand::class,
    ];

    /**
     * Instantiate commands.
     *
     * @return array<Command>
     */
    public function commands(): array
    {
        return array_map(static fn (string $class): Command => new $class(), $this->commands);
    }

    /**
     * @param array<int, string> $argv
     */
    public function handle(array $argv): int
    {
        $name = $argv[1] ?? 'list';
        foreach ($this->commands as $class) {
            $command = new $class();
            if ($command->signature === $name) {
                return $command->run(array_slice($argv, 2), $this);
            }
        }

        echo "Command \"{$name}\" not found." . PHP_EOL;
        return 1;
    }
}
