# Фоновые задачи

## Назначение
Почему нужны фоновые задачи. Воркеры запускаются и перезапускаются через Supervisor; конфиги лежат в `docker/supervisor`.

## Список задач
- **longpolling.php** — получает обновления от Telegram через long polling, хранит offset в Redis и передаёт данные в обработчики. Постоянный процесс под supervisor.
- **handler.php** — обрабатывает отдельные обновления в новом процессе, записывает их в таблицу `telegram_updates` и использует Redis для дедупликации. Запускается longpolling-воркером по мере поступления событий.
- **telegram.php** — отправляет сообщения пользователям, читая очереди Redis `telegram:message:queue:*`. Работает постоянно, возможно несколько экземпляров.
- **scheduled_dispatcher.php** — переносит готовые к отправке отложенные сообщения из БД (`telegram_scheduled_messages`) в очередь через `Push`. Постоянный воркер под supervisor.
- **gpt.php** — обрабатывает задания для GPT из очереди Redis `gpt:queue` и сохраняет результаты. Постоянный воркер.
- **purge_refresh_tokens.php** — удаляет просроченные refresh-токены из базы данных. Запускать по cron, например раз в сутки.

## Телеметрия
Отправка метрик и трассировки управляется переменной окружения `TELEMETRY_ENABLED`.

```bash
# .env
TELEMETRY_ENABLED=true   # включить
TELEMETRY_ENABLED=false  # выключить
```

При значении `false` или отсутствии зависимостей вызовы `App\\Telemetry` игнорируются.

## Supervisor
Все воркеры управляются через Supervisor. Готовые конфиги лежат в `docker/supervisor`.

```bash
supervisorctl status workers:*
supervisorctl restart workers:longpolling

# новый воркер
supervisorctl restart workers:scheduled
```

После изменения конфигов выполните `supervisorctl reread && supervisorctl update`.

## Мониторинг
- **Метрики**:
  - `Telemetry::incrementTelegramSent` и `Telemetry::recordTelegramSendFailure` — счётчики успешных и неуспешных отправок сообщений.
  - `Telemetry::setTelegramQueueSize` и `Telemetry::setDlqSize` — размер очереди и DLQ.
  - `Telemetry::setGptBreakerState` и `Telemetry::observeGptResponseTime` — состояние цепочки GPT и время ответа.
- **Логи**: все процессы пишут JSON‑логи через `App\Helpers\Logger` в `storage/logs/app.log`.
- **Алёрты**: на превышение размеров очередей, ошибки отправки и открытый GPT breaker.

## Ручной запуск (CLI)
- `php run scheduled:dispatch [--limit=100]` — разовая постановка в очередь всех отложенных сообщений, у которых наступил срок. Полезно, если воркер не запущен/упал.
