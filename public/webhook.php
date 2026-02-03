<?php

declare(strict_types=1);

use App\Helpers\Database;
use App\Helpers\Logger;
use App\Telegram\UpdateFilter;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

require __DIR__ . '/../vendor/autoload.php';

// Загрузка переменных окружения (если еще не загружены)
if (empty($_ENV)) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
}

// Загрузка конфигурации
$config = require __DIR__ . '/../app/Config/config.php';

// Получение недостающих переменных напрямую из окружения
$botToken = $_ENV['BOT_TOKEN'] ?? null;
$botName = $_ENV['BOT_NAME'] ?? null;
$botApiServer = $_ENV['BOT_API_SERVER'] ?? 'remote';
$botLocalApiHost = $_ENV['BOT_LOCAL_API_HOST'] ?? 'localhost';
$botLocalApiPort = $_ENV['BOT_LOCAL_API_PORT'] ?? '8080';

try {
    // Инициализация Telegram
    if (!empty($botToken) && !empty($botName)) {
        $telegram = new Telegram($botToken, $botName);
    } else {
        throw new Exception('Bot token or bot name not configured');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Telegram initialization failed']);
    Logger::error('Telegram initialization failed: ' . $e->getMessage());
    exit(1);
}

// Получение данных обновления
$input = file_get_contents('php://input');
$updateData = json_decode($input, true);

// Проверка валидности JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    Logger::warning('Invalid JSON in webhook request');
    exit(1);
}

// Проверка токена бота (если передан в URL)
if (!empty($botToken) && !empty($_GET['token']) && $_GET['token'] !== $botToken) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid token']);
    Logger::warning('Invalid bot token in webhook request');
    exit(1);
}

// Проверка наличия данных обновления
if (empty($updateData)) {
    http_response_code(400);
    echo json_encode(['error' => 'Empty update data']);
    Logger::warning('Empty update data in webhook request');
    exit(1);
}

// Логирование входящего обновления
Logger::info('Webhook received', ['update' => $updateData]);

// Создание объекта обновления
$update = new Update($updateData, $botName);

// Фильтрация обновлений
$updateFilter = new UpdateFilter();
if (!$updateFilter->shouldProcess($update, $reason)) {
    Logger::info('Update filtered out', [
        'update_id' => $update->getUpdateId(),
        'reason' => $reason,
    ]);
    echo json_encode(['status' => 'filtered']);
    exit(0);
}

try {
    // Инициализация PDO
    $db = Database::getInstance();

    // Обработка обновления через worker handler
    $payload = base64_encode(json_encode($updateData));
    $workerScript = __DIR__ . '/../workers/handler.php';

    if (file_exists($workerScript)) {
        // Запуск обработчика обновлений
        include $workerScript;
        echo json_encode(['status' => 'ok']);
    } else {
        throw new Exception('Worker handler not found');
    }
} catch (Exception $e) {
    Logger::error('Error handling update', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'update' => $updateData,
    ]);

    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    exit(1);
}
