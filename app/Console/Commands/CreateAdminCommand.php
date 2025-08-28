<?php

namespace App\Console\Commands;

use App\Console\Command;
use App\Console\Kernel;
use App\Helpers\Database;

class CreateAdminCommand extends Command
{
    public string $signature = 'admin:create';
    public string $description = 'Create dashboard administrator';

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments, Kernel $kernel): int
    {
        $email = $arguments[0] ?? trim(readline('Email: '));
        $password = $arguments[1] ?? trim(readline('Password: '));

        if ($email === '' || $password === '') {
            echo 'Email and password are required.' . PHP_EOL;
            return 1;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('INSERT INTO users (email, password) VALUES (:email, :password)');
        $stmt->execute(['email' => $email, 'password' => $hash]);

        echo 'Administrator created.' . PHP_EOL;
        return 0;
    }
}
