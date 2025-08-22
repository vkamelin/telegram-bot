<?php

namespace App\Console\Commands;

use App\Console\Command;
use App\Console\Kernel;
use App\Support\Path;

class CacheClearCommand extends Command
{
    public string $signature = 'cache:clear';
    public string $description = 'Очистить кеш приложения';

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments, Kernel $kernel): int
    {
        $cacheDir = Path::base('storage/cache');
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*');
            if ($files !== false) {
                foreach ($files as $file) {
                    is_file($file) && unlink($file);
                }
            }
        }
        echo "Cache cleared." . PHP_EOL;
        return 0;
    }
}
