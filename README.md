# Telegram Bot Backend: API + Dashboard

Подготовленный бэкенд для Telegram‑бота c административной панелью (Dashboard) и REST API. Основан на Slim 4 и PDO, без тяжёлых фреймворков. Подходит как стартовый шаблон и как база для MVP/пет‑проекта.

## Стек и возможности
- Slim 4, PSR‑7/PSR‑15
- Точка входа: `public/index.php`
- API (`/api/*`) с JWT и rate‑limit
- Dashboard (`/dashboard/*`) с сессиями и CSRF
- Чистый PDO (без ORM)
- Единообразные ответы и ошибки (RFC 7807)
- Workers + Supervisor для фоновых задач (long polling, планировщик, GPT)
- Redis (опционально) для фильтра обновлений и оффсета long polling
- Docker‑окружение (app + nginx + redis + supervisor)
- **Webhook поддержка** (`public/webhook.php`) для получения обновлений от Telegram
- **Воркер отправки сообщений** (`workers/telegram-db.php`) с очередью задач в БД

Проект минималистичный, но расширяемый. Цель — понятная структура, безопасные дефолты, чёткая документация.

## Структура проекта
```
app/
  Config/            # конфигурация, чтение .env
  Controllers/
    Api/             # API контроллеры (/api/*)
    Dashboard/       # контроллеры Dashboard (/dashboard/*)
  Helpers/           # утилиты (Response, Logger, Push, MediaBuilder, RedisHelper и др.)
  Middleware/        # Error, RequestId, SizeLimit, SecurityHeaders, Session, Jwt, Csrf, RateLimit, TelegramInitData
  Telegram/          # UpdateFilter и UpdateHelper
  Console/           # мини‑консоль и команды
public/
  index.php          # bootstrap Slim + маршруты
  webhook.php        # точка входа для Telegram webhooks
workers/             # фоновые воркеры (longpolling, handler, scheduler и др.)
  telegram-db.php    # воркер отправки сообщений через БД очередь
docker/              # конфиги nginx и supervisor для Docker/VPS
```

## Быстрый старт (локально)
```bash
git clone <repo>
cd <repo>
composer install
cp .env.example .env
```
Далее заполните `.env` (см. `ENVIRONMENT.md`). Минимум: `DB_*`, `JWT_SECRET`, `BOT_TOKEN`.

Миграции (Phinx):
```bash
php run migrate:run
```

Создание администратора Dashboard:
```bash
php run admin:create
```

Запуск встроенного сервера для быстрой проверки:
```bash
composer serve
```
Откройте `http://127.0.0.1:8080/dashboard`.

## Запуск в Docker
```bash
chmod +x docker/entrypoint.sh
docker compose up -d --build
```
Полезно: `docker compose logs -f`.

Миграции внутри контейнера:
```bash
docker compose exec app php run migrate:run
```

Supervisor в Docker:
```bash
docker compose exec supervisor supervisorctl status
```

## Безопасность по умолчанию
- JWT для API и сессии+CSRF для Dashboard
- Rate‑limit (по IP или пользователю)
- Ограничение размера запроса (`REQUEST_SIZE_LIMIT`)
- CORS и базовый CSP в `SecurityHeadersMiddleware`
- Единый обработчик ошибок (RFC 7807)
- Проверка Telegram `initData` в `TelegramInitDataMiddleware` (подпись на основе `BOT_TOKEN`)

## API (кратко)
- `GET /api/health` — проверка состояния
- `POST /api/auth/login` — вход, выдаёт JWT
- `POST /api/auth/refresh` — обновление токена по refresh‑токену
- `GET /api/me` — профиль текущего пользователя
- `GET /api/users` — список пользователей
- `POST /api/users` — создать пользователя

Раздел промокодов (JWT):
- `POST /api/promo-codes/upload` — загрузка CSV (multipart/form-data, поле `file`). Первая строка — заголовок с колонкой `code` (опц. `expires_at`, `meta`). При дублях возвращает 409.
- `GET /api/promo-codes` — список кодов, фильтры: `status`, `batch_id`, `q`, `page`, `per_page`.
- `POST /api/promo-codes/issue` — выдача кода пользователю: `{ "user_id": <telegram_user_id>, "batch_id"?: <id> }`.
- `GET /api/promo-code-issues` — последние выдачи (limit).
- `GET /api/promo-code-batches` — список батчей.

Пример аутентификации:
```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -H "X-Telegram-Init-Data: <initData>" \
  -d '{"email":"user@example.com","password":"secret"}'
```
Ответ содержит `token` (JWT). Далее добавляйте заголовок `Authorization: Bearer <token>` и передавайте `initData` тем же способом.

Подробности — в `docs/api.md` и `docs/openapi.yaml`.

## Dashboard: рассылка сообщений
Раздел Messages → Send позволяет отправлять сообщения в Telegram (одному, группе или всем выбранным). Поддерживаются типы: `text`, `photo`, `audio`, `video`, `document`, `sticker`, `animation`, `voice`, `video_note`, `media_group`.

Пример отправки Media Group через хелперы:
```php
use App\Helpers\Push;
use App\Helpers\MediaBuilder;

$media = [
    MediaBuilder::buildInputMedia('photo', 'https://example.com/a.jpg', ['caption' => 'Пример 1']),
    MediaBuilder::buildInputMedia('video', 'https://example.com/b.mp4', ['caption' => 'Пример 2']),
];

Push::mediaGroup(123456789, $media);
```

Для одиночных медиа есть `Push::photo()`, `Push::video()`, `Push::audio()`, `Push::document()` и т. д.

Ограничения Telegram: общий запрос с медиа должен укладываться в лимиты API; контролируйте размер через `REQUEST_SIZE_LIMIT` и учитывайте лимиты Telegram Bot API.

## Webhook поддержка
Проект поддерживает получение обновлений от Telegram через webhook в дополнение к long polling:

### Настройка webhook
```bash
# Установить webhook на URL
curl -X POST "https://api.telegram.org/bot<BOT_TOKEN>/setWebhook" \
  -H "Content-Type: application/json" \
  -d '{"url": "https://your-domain.com/webhook.php"}'

# Проверить статус webhook
curl "https://api.telegram.org/bot<BOT_TOKEN>/getWebhookInfo"
```

### Обработка webhook
- **Точка входа:** `public/webhook.php`
- **Функции:** 
  - Валидация входящих данных от Telegram
  - Проверка токена бота (опционально через URL параметр)
  - Фильтрация обновлений через `UpdateFilter`
  - Передача обработки в `workers/handler.php`
- **Логирование:** Все входящие обновления логируются с информацией о фильтрации
- **Безопасность:** Проверка JSON, токена бота и валидности данных

### Переключение между webhook и long polling
- **Webhook:** Установите переменную `USE_WEBHOOK=true` в `.env`
- **Long polling:** Используйте стандартный воркер `workers/longpolling.php`
- При использовании локального Telegram Bot API настройте `BOT_API_SERVER=local`

## Dashboard: промокоды
- Маршруты:
  - `GET /dashboard/promo-codes` — список с фильтрами и выдачей.
  - `GET /dashboard/promo-codes/upload` — форма загрузки CSV.
  - `POST /dashboard/promo-codes/upload` — обработчик загрузки CSV.
  - `POST /dashboard/promo-codes/{id}/issue` — выдача конкретного кода пользователю.
  - `GET /dashboard/promo-codes/issues` — отчёт по выдачам (фильтры по дате и пользователю).
  - `GET /dashboard/promo-codes/issues/export` — экспорт отчёта в CSV.
  - `GET /dashboard/promo-codes/batches` — список батчей с агрегатами.

Особенности и ограничения:
- Все формы защищены CSRF, файл проверяется по размеру (<= min(`REQUEST_SIZE_LIMIT`, 5MB)) и типу (`text/csv`, `text/plain`, `application/vnd.ms-excel`).
- CSV: первая строка — заголовок. Обязательная колонка: `code`. Допустимы `expires_at`, `meta`.
- Импорт выполняется в новый батч; дубликаты `code` пропускаются (INSERT IGNORE), показываются счётчики «импортировано/пропущено».
- Выдача выполняется в транзакции с `SELECT ... FOR UPDATE`, проверяются статус и срок действия. Лог пишется в `promo_code_issues` с `issued_by` из сессии.

## Воркеры и Supervisor
- `workers/longpolling.php` — читает обновления через getUpdates (при желании можно перейти на webhooks)
- `workers/handler.php` — обрабатывает конкретные апдейты (запускается форком из longpolling или webhook)
- `workers/scheduled_dispatcher.php` — планировщик рассылок
- `workers/purge_refresh_tokens.php` — чистит протухшие refresh‑токены
- `workers/gpt.php` — пример интеграции с GPT
- **`workers/telegram-db.php`** — **воркер отправки сообщений через очередь в БД**

### Воркер отправки сообщений (telegram-db.php)
Специализированный воркер для отправки сообщений через очередь задач в базе данных:

**Основные возможности:**
- Извлекает задачи из таблицы `telegram_messages` по приоритетам (2, 1, 0)
- Соблюдает лимиты RPS (максимум 25 сообщений в секунду)
- Поддерживает все типы сообщений: text, photo, video, document, audio, animation, voice, video_note, sticker, media_group
- Автоматически кодирует медиа-файлы для отправки
- Экспоненциальная задержка при ошибках (до 5 попыток)
- Обновляет счетчики для запланированных рассылок
- Логирование статуса отправки и ошибок

**Конфигурация:**
```bash
# Переменные окружения
BOT_MAX_RPS=25                    # Максимум запросов в секунду
BOT_API_SERVER=local|remote       # Локальный или удаленный API
BOT_LOCAL_API_HOST=localhost      # Хост локального API
BOT_LOCAL_API_PORT=8080           # Порт локального API
```

**Запуск воркера:**
```bash
# Напрямую
php workers/telegram-db.php

# Через Supervisor (рекомендуется)
supervisorctl start telegram-db-worker

# В Docker
docker compose exec supervisor supervisorctl start telegram-db-worker
```

**Структура очереди сообщений:**
```sql
CREATE TABLE `telegram_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chat_id` bigint(20) NOT NULL,
  `method` varchar(50) NOT NULL,
  `data` json NOT NULL,
  `priority` tinyint(1) DEFAULT 0,
  `status` enum('pending','processing','success','failed') DEFAULT 'pending',
  `message_id` int(11) DEFAULT NULL,
  `error` text DEFAULT NULL,
  `code` int(11) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL,
  `scheduled_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status_priority` (`status`,`priority`,`sent_at`)
);
```

Примеры конфигов для Supervisor — в `docker/supervisor`. На VPS скопируйте их в `/etc/supervisor/`, выполните `supervisorctl reread && supervisorctl update`.

## Консоль
Все команды запускаются через `php run`:
- `list` — список команд
- `help <command>` — помощь по команде
- `migrate:run|create|rollback` — миграции (Phinx)
- `admin:create` — создать администратора Dashboard
- `push:send <chatId> <type> [payload]` — отправка сообщений из CLI
- `refresh:purge` — очистка refresh‑токенов
- `scheduled:dispatch` — отправка отложенных сообщений
- `filter:update` — управление списками фильтров обновлений (Redis/.env)

## Переменные окружения
См. подробный список и примеры в `ENVIRONMENT.md`. Кратко: `APP_*`, `DB_*`, `REDIS_*`, `JWT_*`, `CORS_ORIGINS`, `CSP_*`, `RATE_LIMIT_*`, `REQUEST_SIZE_LIMIT`, `BOT_*`, `TG_*` (фильтры), `TELEMETRY_ENABLED`, `WORKERS_*`, `AITUNNEL_API_KEY`, `USE_WEBHOOK`.

## Развёртывание
- Docker: `docker compose up -d --build`, миграции: `docker compose exec app php run migrate:run`
- VPS (без Docker): настройте Nginx + PHP‑FPM, скопируйте конфиги Supervisor, выполните миграции и включите сервисы. Подробности — в `DEPLOYMENT.md`.