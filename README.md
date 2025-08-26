
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
JWT_SECRET="secret"                                      # секретный ключ JWT (генерируется автоматически, если пусто)
CORS_ORIGINS="*"                                         # разрешённые origin через запятую
RATE_LIMIT_BUCKET=ip                                     # тип лимита: ip или user
RATE_LIMIT=60                                            # запросов в минуту
REQUEST_SIZE_LIMIT=1048576                               # максимальный размер тела запроса в байтах
BOT_TOKEN="0000000000:AA..."                            # токен Telegram-бота
TELEMETRY_ENABLED=false                                  # включить метрики и трассировку
```

Если оставить `JWT_SECRET` пустым, `scripts/init.sh` сгенерирует его автоматически.

BOT_TOKEN — токен бота для проверки `initData` из Telegram WebApp.

`TELEMETRY_ENABLED=true` включает отправку метрик и трассировку (требуются соответствующие библиотеки). При `false` или отсутствии библиотек вызовы `Telemetry` становятся no-op.

## 🐳 Docker

```bash
chmod +x scripts/init.sh scripts/deploy.sh docker/entrypoint.sh
./scripts/init.sh
docker compose up -d --build
```
В Windows используйте `scripts\deploy.bat`.

Миграции:

```bash
docker compose exec app php vendor/bin/phinx migrate
```

## 👷 Supervisor

Воркеры запускаются через Supervisor. Конфиги лежат в `docker/supervisor`. На VPS скопируй их в `/etc/supervisor/` и включи службу `supervisor`.

Управление воркерами:

```bash
supervisorctl status workers:*
supervisorctl restart workers:longpolling
```

В Docker:

```bash
docker compose exec supervisor supervisorctl status
```

## 📊 Телеметрия

Отправка метрик и трассировки опциональна. Управляется переменной `TELEMETRY_ENABLED` и требует дополнительных библиотек.

```bash
# .env
TELEMETRY_ENABLED=true   # включить
TELEMETRY_ENABLED=false  # отключить

# временно при запуске
TELEMETRY_ENABLED=true php workers/longpolling.php
```

При `false` или отсутствии зависимостей вызовы `App\\Telemetry` ничего не делают.

---

## ▶️ Запуск

```bash
composer serve
```

Доступно:

* API: [http://localhost:8080/api/](http://localhost:8080/api/)\*
* Dashboard: [http://localhost:8080/dashboard/](http://localhost:8080/dashboard/)\*
  * System: [http://localhost:8080/dashboard/system](http://localhost:8080/dashboard/system) — просмотр env-переменных и команд воркеров
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
php bin/console push:send "Hello" --all
```

`admin:create` — создаёт администратора панели управления, запрашивая email и пароль и добавляя запись в таблицу `users`.

`push:send` — отправляет push-сообщение пользователям Telegram. Получатели задаются параметрами:

* `--all` — всем пользователям;
* `--user=1,2,3` — по идентификаторам;
* `--username=alice,bob` — по username;
* `--group=support` — по группам.

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

## 🖼️ Работа с медиа-группами

`Push::buildInputMedia()` помогает собрать массив [`InputMedia`](https://core.telegram.org/bots/api#inputmedia) для разных типов файлов.
Укажите тип (`photo`, `video`, `audio` или `document`), ссылку, `fileId` или путь к файлу и при необходимости подпись.

```php
use App\Helpers\Push;

$media = [
    Push::buildInputMedia('photo', 'https://example.com/a.jpg', ['caption' => 'Фото']),
    Push::buildInputMedia('video', 'https://example.com/b.mp4', ['caption' => 'Видео']),
    Push::buildInputMedia('audio', 'https://example.com/c.mp3'),
    Push::buildInputMedia('document', 'https://example.com/d.pdf', ['caption' => 'Документ']),
];

Push::mediaGroup(123456789, $media);
```

Полученный массив можно передавать и в одиночные методы `Push::photo()`, `Push::video()`, `Push::audio()` и `Push::document()`.

---

## 📖 Документация

* [ARCHITECTURE.md](ARCHITECTURE.md) — архитектура
* [CONTRIBUTING.md](CONTRIBUTING.md) — правила разработки
* [CHANGELOG.md](CHANGELOG.md) — история изменений
* [ENVIRONMENT.md](ENVIRONMENT.md) — как организовать `.env` файл.
* [CODESTYLE.md](CODESTYLE.md) — как комментировать код (классы, методы, свойства и т.д.).
