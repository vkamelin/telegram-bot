# Наблюдаемость

Телеметрия управляется переменной окружения `TELEMETRY_ENABLED`. Если она не установлена или имеет значение `false`, а также при отсутствии необходимых библиотек, вызовы `App\Telemetry` ничего не делают.

## Логирование
- Используется обёртка `App\\Helpers\\Logger` поверх Monolog.
- Записи формируются в формате JSON.
- Перед сохранением редактируются PII (пароли, токены и т.д.).
- Файлы хранятся в `storage/logs` и ротируются ежедневно.

## Метрики
- `workers/telegram.php`: `Telemetry::incrementTelegramSent`, `Telemetry::recordTelegramSendFailure`, `Telemetry::setTelegramQueueSize`, `Telemetry::setDlqSize`.
- `App\\Helpers\\GPTService`: `Telemetry::setGptBreakerState`, `Telemetry::observeGptResponseTime`.

## Трассировка
- Интеграция с OpenTelemetry через зависимость Composer `open-telemetry/opentelemetry`.

## Алертинг
- Slack — уведомления при высоком уровне ошибок или росте DLQ.
- Email — уведомления о длительной недоступности сервисов и замедлении ответов.
