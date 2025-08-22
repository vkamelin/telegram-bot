# Minimal Telegram Bot Backend (API + Dashboard)

## 🚀 Описание
Простой и быстрый каркас для Telegram-бота и Dashboard:
- Slim 4
- Единый вход (`public/index.php`)
- API (`/api/*`) с JWT и rate-limit
- Dashboard (`/dashboard/*`) с CSRF
- PDO напрямую (без ORM)
- Воркеры — отдельно, без изменений

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
BOT_TOKEN="0000000000:AA..."                            # токен Telegram-бота
```

BOT_TOKEN — токен бота для проверки `initData` из Telegram WebApp.

---

## ▶️ Запуск

```bash
composer serve
```

Доступно:

* API: [http://localhost:8080/api/](http://localhost:8080/api/)\*
* Dashboard: [http://localhost:8080/dashboard/](http://localhost:8080/dashboard/)\*
* Health: [http://localhost:8080/api/health](http://localhost:8080/api/health)

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