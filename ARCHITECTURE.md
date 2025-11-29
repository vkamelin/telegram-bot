# Архитектура и ключевые решения

## Обзор
- Минималистичный HTTP‑слой на Slim 4.
- Две зоны маршрутов: API (`/api/*`) и Dashboard (`/dashboard/*`).
- Без ORM: прямой PDO с подготовленными выражениями.
- Защита: JWT, CSRF, Rate‑Limit, Security Headers, ограничение размера тела запроса, RFC 7807.
- Фоновые воркеры: long polling Telegram, обработчик обновлений, планировщик рассылок, утилитарные задачи.
- Redis (опционально): хранение оффсета long polling и правил фильтрации апдейтов.

## Поток обработки запроса
1. Вход в `public/index.php`, загрузка `.env`, сборка `config`.
2. Создание Slim‑приложения и регистрация Middleware (сверху вниз):
   - `RequestIdMiddleware` — X‑Request‑Id, корреляция логов.
   - `RequestSizeLimitMiddleware` — защита от слишком больших тел.
   - `BodyParsingMiddleware` — парсинг JSON/форм‑данных.
   - `SecurityHeadersMiddleware` — CORS + базовый CSP + X‑Headers.
   - `ErrorMiddleware` — единый обработчик ошибок (RFC 7807 JSON).
3. Группы маршрутов:
   - Dashboard `/dashboard/*`: сессии + CSRF + Auth (логин/логаут, UI‑функции).
   - API `/api/*`: JWT + Rate‑Limit (REST эндпоинты).
4. Ответ сериализуется через `Helpers\Response` (json / problem+json).

## Контроллеры
- `Controllers\Dashboard\*` — страницы панели (Messages, Files, Updates, Users, Logs, и т. п.). Работают с сессией и CSRF.
- `Controllers\Api\*` — Health, Auth (login/refresh), Me, Users. Работают с JWT и возвращают JSON.

## Middleware
- `ErrorMiddleware(bool $debug)` — преобразует исключения в RFC 7807 (при debug — расширенная диагностика).
- `RequestIdMiddleware` — генерирует/передаёт `X-Request-Id`.
- `RequestSizeLimitMiddleware(int $bytes)` — 413 при превышении лимита тела.
- `SecurityHeadersMiddleware` — CORS (origins/methods/headers), CSP (script/style/font/connect), безопасные заголовки.
- `SessionMiddleware` — поддержка сессий для Dashboard.
- `CsrfMiddleware` — CSRF‑токены в формах Dashboard.
- `JwtMiddleware` — валидация JWT для защищённых API‑маршрутов.
- `RateLimitMiddleware` — лимиты на IP/пользователя.
- `TelegramInitDataMiddleware($botToken)` — проверка подписи `initData` из Telegram Mini App.

## Хелперы и сервисы
- `Database` — singleton PDO.
- `Response` — json/problem ответы.
- `Logger` — обёртка над Monolog.
- `Push` — отправка сообщений в Telegram (включая группы медиа).
- `MediaBuilder` — удобная сборка `InputMedia*` для групп медиа.
- `RedisHelper`/`RedisKeyHelper` — доступ к Redis и ключам.
- `RefreshTokenService` — управление refresh‑токенами.
- `MessageStorage`, `FileService`, `Path`, `View`, `JsonHelper` — вспомогательные утилиты.
- `Telemetry` — заглушка/точка расширения для метрик/трейсинга.

## Telegram: обработка обновлений
- `workers/longpolling.php` считывает апдейты с помощью `getUpdates`.
- `Telegram\UpdateFilter` решает, обрабатывать ли апдейт (по типам, чатам, командам). Источник правил — `.env` или Redis‑множества (см. `docs/update-filter.md`).
- Для каждого принятого апдейта форком запускается `workers/handler.php`, чтобы не блокировать при длительной обработке.
- Дополнительно есть воркеры для GPT и отложенных рассылок.

## Данные и миграции
- Миграции — Phinx (`php run migrate:create|run|rollback`).
- Минимальные таблицы: пользователи (Dashboard), рефреш‑токены, лог/журнал обновлений и служебные данные для рассылок и файлов. Детали — по миграциям в `database/`.

## Ошибки и ответы
- Единый формат ошибок — RFC 7807: `application/problem+json` с полями `type`, `title`, `status`, `detail`, `instance`.
- Успешные ответы — `application/json` с консистентными структурами (`items`, `meta`, и т. п.).

## Решения и компромиссы
- Без ORM: меньше магии, больше контроля, проще онбординг.
- Форк процессов в long polling: упрощает конкуренцию, но требует аккуратности с ресурсами.
- Redis — опционально: проект не жёстко зависит, но использует, когда доступен.
- CSP настроен консервативно для CDN‑библиотек Dashboard; при необходимости ужесточайте/переводите на self‑хостинг.
