# Telegram Bot Backend: API + Dashboard

Бэкенд для Telegram-бота с административной панелью (Dashboard) и REST API. Сервис построен на Slim 4 и использует PDO для работы с базой данных. Проект реализует полный цикл работы с промокодами и рассылками, а также предоставляет инструменты для интеграции с Telegram и внешними системами.

## Основные возможности
- Slim 4, PSR-7/PSR-15
- Точка входа для HTTP: `public/index.php`
- API (`/api/*`) с JWT-аутентификацией и ограничением частоты запросов
- Dashboard (`/dashboard/*`) с сессиями и защитой от CSRF
- Работа с базой через PDO (без ORM)
- Формат ошибок в стиле RFC 7807
- Набор воркеров под Supervisor для long polling, обработки апдейтов, задач GPT и др.
- Redis для хранения состояния и очередей long polling
- Готовая конфигурация Docker (app + nginx + redis + supervisor)

Проект предназначен для приватного развёртывания и адаптируется под конкретные сценарии. Репозиторий содержит минимально необходимый функционал для MVP/пилота.

## Структура каталога
```
app/
  Config/            # Конфигурация приложения, в том числе .env
  Controllers/
    Api/             # Контроллеры API (/api/*)
    Dashboard/       # Контроллеры Dashboard (/dashboard/*)
  Helpers/           # Утилиты (Response, Logger, Push, MediaBuilder, RedisHelper и др.)
  Middleware/        # Error, RequestId, SizeLimit, SecurityHeaders, Session, Jwt, Csrf, RateLimit, TelegramInitData
  Telegram/          # UpdateFilter и UpdateHelper
  Console/           # Команды консоли (run <command>)
public/
  index.php          # Bootstrap Slim и загрузка контейнера зависимостей
workers/             # Скрипты воркеров (longpolling, handler, scheduler и др.)
docker/              # Конфигурация nginx и supervisor для Docker/VPS
```

## Установка и настройка (локально)
```bash
git clone <repo>
cd <repo>
composer install
cp .env.example .env
```

Заполните переменные окружения в `.env` (см. `ENVIRONMENT.md`). Важно указать `DB_*`, `JWT_SECRET`, `BOT_TOKEN` и другие параметры интеграций.

Миграции (Phinx):
```bash
php run migrate:run
```

Создание пользователя Dashboard:
```bash
php run admin:create
```

Запуск встроенного сервера для разработки:
```bash
composer serve
```

Панель будет доступна по адресу `http://127.0.0.1:8080/dashboard`.

## Запуск в Docker
```bash
chmod +x docker/entrypoint.sh
docker compose up -d --build
```

Логи: `docker compose logs -f`.

Выполнение миграций внутри контейнера:
```bash
docker compose exec app php run migrate:run
```

Статус Supervisor в Docker:
```bash
docker compose exec supervisor supervisorctl status
```

## Безопасность и ограничения
- JWT для API и сессии + CSRF для Dashboard
- Rate Limit (ограничение по IP и кастомным ключам)
- Ограничение размера запроса (`REQUEST_SIZE_LIMIT`)
- CORS и CSP через `SecurityHeadersMiddleware`
- Формат ошибок RFC 7807
- Проверка Telegram `initData` в `TelegramInitDataMiddleware` (валидируется через `BOT_TOKEN`)

## API (основные эндпоинты)
- `GET /api/health` — проверка состояния сервиса
- `POST /api/auth/login` — логин, выдаёт JWT
- `POST /api/auth/refresh` — обновление refresh-токена
- `GET /api/me` — получение информации о текущем пользователе
- `GET /api/users` — список пользователей
- `POST /api/users` — создание пользователя

Эндпоинты, требующие расширенных прав (JWT):
- `POST /api/promo-codes/upload` — загрузка CSV (multipart/form-data, поле `file`). Если промокод уже существует, возвращается ошибка 409.
- `GET /api/promo-codes` — список промокодов (`status`, `batch_id`, `q`, `page`, `per_page`)
- `POST /api/promo-codes/issue` — выдача промокода: `{ "user_id": <telegram_user_id>, "batch_id"?: <id> }`
- `GET /api/promo-code-issues` — история выдач
- `GET /api/promo-code-batches` — список партий промокодов

Пример запроса:
```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -H "X-Telegram-Init-Data: <initData>" \
  -d '{"email":"user@example.com","password":"secret"}'
```

В ответе приходит поле `token` (JWT). Используйте заголовок `Authorization: Bearer <token>` и тот же `initData` для последующих запросов.

Подробности в `docs/api.md` и `docs/openapi.yaml`.

## Dashboard: рассылки и сообщения
Разделы Messages и Send позволяют отправлять пользователям Telegram сообщения различных типов: `text`, `photo`, `audio`, `video`, `document`, `sticker`, `animation`, `voice`, `video_note`, `media_group`.

Пример отправки Media Group через helper:
```php
use App\Helpers\Push;
use App\Helpers\MediaBuilder;

$media = [
    MediaBuilder::buildInputMedia('photo', 'https://example.com/a.jpg', ['caption' => 'Файл 1']),
    MediaBuilder::buildInputMedia('video', 'https://example.com/b.mp4', ['caption' => 'Файл 2']),
];

Push::mediaGroup(123456789, $media);
```

Также доступны методы `Push::photo()`, `Push::video()`, `Push::audio()`, `Push::document()` и др.

## Dashboard: промокоды
- `GET /dashboard/promo-codes` — список и фильтры
- `GET /dashboard/promo-codes/upload` — форма загрузки CSV
- `POST /dashboard/promo-codes/upload` — загрузка CSV
- `POST /dashboard/promo-codes/{id}/issue` — вручную выдать промокод пользователю
- `GET /dashboard/promo-codes/issues` — история выдач
- `GET /dashboard/promo-codes/issues/export` — экспорт CSV
- `GET /dashboard/promo-codes/batches` — список партий

Особенности:
- Формы защищены CSRF, ограничение размера файлов — `min(REQUEST_SIZE_LIMIT, 5MB)`; поддерживаемые MIME-типы: `text/csv`, `text/plain`, `application/vnd.ms-excel`.
- CSV должен содержать колонку `code`; опционально `expires_at`, `meta`.
- При повторной загрузке дубликаты `code` игнорируются (INSERT IGNORE); ошибки по строкам собираются и отображаются.
- Выдача промокодов работает в транзакции (`SELECT ... FOR UPDATE`), в таблице `promo_code_issues` фиксируется `issued_by`.

## Воркеры и Supervisor
- `workers/longpolling.php` — получение обновлений через `getUpdates` (без webhooks)
- `workers/handler.php` — обработка обновлений и команд
- `workers/scheduled_dispatcher.php` — планировщик отправки сообщений
- `workers/purge_refresh_tokens.php` — очистка просроченных refresh-токенов

## CLI-команды (`php run <command>`)
- `help <command>` — справка по команде
- `migrate:run|create|rollback` — управление миграциями (Phinx)
- `admin:create` — создать администратора Dashboard
- `push:send <chatId> <type> [payload]` — отправка сообщения из CLI
- `refresh:purge` — очистка refresh-токенов
- `scheduled:dispatch` — запуск планировщика
- `filter:update` — обновление фильтров и списков (Redis/.env)

## Переменные окружения
См. `ENVIRONMENT.md` для полного перечня. Основные группы: `APP_*`, `DB_*`, `REDIS_*`, `JWT_*`, `CORS_ORIGINS`, `CSP_*`, `RATE_LIMIT_*`, `REQUEST_SIZE_LIMIT`, `BOT_*`, `TG_*`, `TELEMETRY_ENABLED`, `WORKERS_*`, `AITUNNEL_API_KEY`.

## Деплой
- Docker: `docker compose up -d --build`, затем `docker compose exec app php run migrate:run`
- VPS (без Docker): настройте Nginx + PHP-FPM, Supervisor, cron/таймеры; см. `DEPLOYMENT.md`

## UTM отчёт (Dashboard)
- Маршрут: `GET/POST /dashboard/utm`
- Фильтры: поля `from` и `to` (HTML `datetime-local`), фильтрация по `tg_pre_checkout.received_at`
- Источник данных: колонка `telegram_users.utm` (для пустых значений отображается `(no utm)`)
- Метрики: агрегированный `SUM(tg_pre_checkout.total_amount)` по каждой UTM-метке
- Ограничения: учитываются только оплаченные платежи (Telegram Payments)

