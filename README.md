# Minimal Telegram Bot Backend (API + Dashboard)

## 🚀 Описание
Простой и быстрый каркас для Telegram-бота и Dashboard:
- Slim 4
- Единый вход (`public/index.php`)
- API (`/api/*`) с JWT и rate-limit
- Dashboard (`/dashboard/*`) с CSRF
- PDO напрямую (без ORM)
- Воркеры — отдельно, без изменений
- Supervisor для управления воркерами
- Опциональная телеметрия (метрики и трассировка)

Подходит для быстрой разработки и развёртывания MVP (до 1 недели).

---

## 📂 Структура проекта

```
app/
Controllers/Api/...
Controllers/Dashboard/...
Middleware/...
Helpers/Response.php
Config/config.php
public/index.php
````

---

## ⚙️ Установка

```bash
git clone <repo>
cd <repo>
composer install
cp .env.example .env
````

Укажи в `.env`:

```
DB_DSN="mysql:host=127.0.0.1;dbname=app;charset=utf8mb4" # строка подключения к БД
DB_USER="user"                                           # пользователь БД
DB_PASS="pass"                                           # пароль БД
JWT_SECRET="secret"                                      # секретный ключ JWT
CORS_ORIGINS="*"                                         # разрешённые origin через запятую
RATE_LIMIT_BUCKET=ip                                     # тип лимита: ip или user
RATE_LIMIT=60                                            # запросов в минуту
REQUEST_SIZE_LIMIT=1048576                               # максимальный размер тела запроса в байтах
BOT_TOKEN="0000000000:AA..."                            # токен Telegram-бота
TELEMETRY_ENABLED=false                                  # включить метрики и трассировку
```

BOT_TOKEN — токен бота для проверки `initData` из Telegram WebApp.

`TELEMETRY_ENABLED=true` включает отправку метрик и трассировку (требуются соответствующие библиотеки). При `false` или отсутствии библиотек вызовы `Telemetry` становятся no-op.

## 🐳 Docker

```bash
chmod +x scripts/init.sh scripts/deploy.sh docker/entrypoint.sh
./scripts/init.sh
docker compose up -d --build
```

Миграции:

```bash
docker compose exec app php vendor/bin/phinx migrate
```

## 👷 Supervisor

Воркеры запускаются через Supervisor. Конфиги лежат в `docker/supervisor`. На VPS скопируй их в `/etc/supervisor/` и включи службу `supervisor`.

## 📊 Телеметрия

Отправка метрик и трассировки опциональна. Включается переменной `TELEMETRY_ENABLED=true` и требует дополнительных библиотек. При `false` или отсутствии зависимостей вызовы `App\\Telemetry` ничего не делают.

---

## ▶️ Запуск

```bash
composer serve
```

Доступно:

* API: [http://localhost:8080/api/](http://localhost:8080/api/)\*
* Dashboard: [http://localhost:8080/dashboard/](http://localhost:8080/dashboard/)\*
* Health: [http://localhost:8080/api/health](http://localhost:8080/api/health)

## 🗄️ Миграции

```bash
php bin/console migrate:create AddUsersTable
php bin/console migrate:run
php bin/console migrate:rollback
```

## 🖥️ Console

```bash
php bin/console admin:create
```

Создаёт администратора панели управления, запрашивая email и пароль и добавляя запись в таблицу `users`.

---

## 🛡️ Middleware

* `ErrorMiddleware` — ошибки в RFC7807
* `JwtMiddleware` — защита API
* `CsrfMiddleware` — защита Dashboard
* `RateLimitMiddleware` — ограничение запросов
* `TelegramInitDataMiddleware` — проверка `initData` Telegram WebApp

---

## 📱 Telegram Mini App

Для запросов из Telegram WebApp передай `initData`, который проверяется через `TelegramInitDataMiddleware` и `BOT_TOKEN`.

```bash
curl http://localhost:8080/api/health -H "Authorization: tma <initData>"
curl http://localhost:8080/api/health -H "X-Telegram-Init-Data: <initData>"
curl "http://localhost:8080/api/health?initData=<initData>"
```

---

## 📖 Документация

* [ARCHITECTURE.md](ARCHITECTURE.md) — архитектура
* [CONTRIBUTING.md](CONTRIBUTING.md) — правила разработки
* [CHANGELOG.md](CHANGELOG.md) — история изменений
* [ENVIRONMENT.md](ENVIRONMENT.md) — как организовать `.env` файл.
* [CODESTYLE.md](CODESTYLE.md) — как комментировать код (классы, методы, свойства и т.д.).