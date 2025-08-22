# Фоновые задачи

## Назначение
Почему нужны фоновые задачи. Описание воркеров в supervisor

## Список задач
- **longpolling.php** — получает обновления от Telegram через long polling, хранит offset в Redis и передаёт данные в обработчики. Постоянный процесс под supervisor.
- **handler.php** — обрабатывает отдельные обновления в новом процессе, записывает их в таблицу `telegram_updates` и использует Redis для дедупликации. Запускается longpolling-воркером по мере поступления событий.
- **telegram.php** — отправляет сообщения пользователям. Перемещает данные из `telegram_scheduled_messages` в `telegram_messages` и дальше в очереди Redis `telegram:queue:*`. Работает постоянно, возможно несколько экземпляров.
- **gpt.php** — обрабатывает задания для GPT из очереди Redis `gpt:queue` и сохраняет результаты. Постоянный воркер.
- **purge_refresh_tokens.php** — удаляет просроченные refresh-токены из базы данных. Запускать по cron, например раз в сутки.

## Мониторинг
- **Метрики**:
  - `Telemetry::incrementTelegramSent` и `Telemetry::recordTelegramSendFailure` — счётчики успешных и неуспешных отправок сообщений.
  - `Telemetry::setTelegramQueueSize` и `Telemetry::setDlqSize` — размер очереди и DLQ.
  - `Telemetry::setGptBreakerState` и `Telemetry::observeGptResponseTime` — состояние цепочки GPT и время ответа.
- **Логи**: все процессы пишут JSON‑логи через `App\Helpers\Logger` в `storage/logs/app.log`.
- **Перезапуск**: при росте очередей или ошибках перезапускайте воркеры через supervisor (`supervisorctl restart workers:*`), предварительно убедившись в корректном завершении.
- **Алёрты**: на превышение размеров очередей, ошибки отправки и открытый GPT breaker.
