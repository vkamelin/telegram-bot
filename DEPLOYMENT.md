# 1) DEPLOYMENT.md (добавьте в репозиторий)

# DEPLOYMENT

Два способа запуска:
- **Docker** — быстрый и воспроизводимый.
- **VPS (без Docker)** — Nginx + PHP-FPM + Systemd.

## Предусловия

- PHP 8.3, Composer 2 (если без Docker).
- MySQL/MariaDB/PostgreSQL (любой поддерживаемый PDO DSN).
- `cp .env.example .env` — будет выполнено через `scripts/init.sh`.

---

## A. Docker

### 1) Файлы
- `docker-compose.yml` — web + php-fpm (или phpslim) + (опц.) redis
- `Dockerfile` — prod-образ
- `docker/entrypoint.sh` — миграции и запуск

### 2) Первая настройка
```bash
chmod +x scripts/init.sh scripts/deploy.sh docker/entrypoint.sh
./scripts/init.sh       # заполнит .env (интерактивно)
````

### 3) Запуск

```bash
docker compose up -d --build
docker compose logs -f
```

Проверка:

* API: `http://localhost:8080/api/health`
* Dashboard: `http://localhost:8080/admin`

### 4) Миграции

```bash
docker compose exec app vendor/bin/phinx migrate
```

---

## B. VPS (без Docker)

### 1) Установка зависимостей (пример для Debian/Ubuntu)

* Nginx, PHP-FPM 8.3 (`php8.3-fpm php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-mysql`)
* Composer 2

### 2) Первая настройка

```bash
chmod +x scripts/init.sh scripts/deploy.sh
./scripts/init.sh   # заполнит .env (интерактивно)
composer install --no-dev --optimize-autoloader
vendor/bin/phinx migrate -e production
```

### 3) Виртуальный хост Nginx

* Скопируй `deploy/nginx.conf` в `/etc/nginx/sites-available/yourapp.conf`
* Обнови пути/домен в конфиге
* Включи сайт:

```bash
ln -s /etc/nginx/sites-available/yourapp.conf /etc/nginx/sites-enabled/yourapp.conf
nginx -t && systemctl reload nginx
```

### 4) PHP-FPM

* Проверь путь сокета из `deploy/nginx.conf` (обычно `/run/php/php8.3-fpm.sock`).
* Убедись, что `php8.3-fpm` запущен.

### 5) Systemd сервисы (опционально)

* Приложение обслуживает Nginx+FPM (службы уже есть).
* Для воркеров создайте юниты на основе `deploy/systemd/telegram-worker.service` (обновите `ExecStart`).

---

## Обновление версии (оба варианта)

```bash
git pull
composer install --no-dev --optimize-autoloader
vendor/bin/phinx migrate -e production
php artisan-like:cache:clear  # если будет
# Docker: docker compose up -d --build
# VPS: systemctl reload nginx && systemctl reload php8.3-fpm
```

## Точки контроля

* `/api/health` возвращает `200 {"status":"ok"}`.
* Логи ошибок пустые / без критичных записей.
* Миграции прошли без ошибок.

---

# 2) Docker файлы

## 2.1 `docker-compose.yml`
```yaml
version: "3.9"

services:
  app:
    container_name: app
    build:
      context: .
      dockerfile: Dockerfile
    env_file: .env
    volumes:
      - .:/var/www/html
    ports:
      - "8080:8080"
    depends_on:
      - db
    command: ["/bin/sh", "-c", "docker/entrypoint.sh"]
    restart: unless-stopped

  db:
    image: mysql:8.4
    container_name: db
    environment:
      MYSQL_DATABASE: "${DB_NAME:-app}"
      MYSQL_USER: "${DB_USER:-app}"
      MYSQL_PASSWORD: "${DB_PASS:-secret}"
      MYSQL_ROOT_PASSWORD: "${DB_ROOT_PASS:-root}"
    ports:
      - "3306:3306"
    volumes:
      - dbdata:/var/lib/mysql
    restart: unless-stopped

  redis:
    image: redis:7
    container_name: redis
    ports:
      - "6379:6379"
    restart: unless-stopped

volumes:
  dbdata:
```

## 2.2 `Dockerfile`

```dockerfile
# Stage 1: deps
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction

# Stage 2: runtime
FROM php:8.3-cli
WORKDIR /var/www/html

# extensions
RUN docker-php-ext-install pdo pdo_mysql opcache

# user
RUN adduser --disabled-password --gecos "" app && chown -R app:app /var/www/html
USER app

# app
COPY --chown=app:app . .
COPY --from=vendor /app/vendor ./vendor

EXPOSE 8080

ENV PHP_CLI_SERVER_WORKERS=4

# health endpoint expected at /api/health
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
```

## 2.3 `docker/entrypoint.sh`

```bash
#!/usr/bin/env sh
set -e

# ждём БД (простой ретрай)
if [ -n "$DB_DSN" ]; then
  echo "Waiting DB (simple sleep) ..."
  sleep 3
fi

# миграции, если phinx есть
if [ -f vendor/bin/phinx ]; then
  echo "Running migrations..."
  php vendor/bin/phinx migrate || true
fi

exec php -S 0.0.0.0:8080 -t public
```

> Сделайте исполняемым: `chmod +x docker/entrypoint.sh`.

---

# 3) VPS конфиги

## 3.1 `deploy/nginx.conf`

```nginx
server {
    listen 80;
    server_name your.domain.tld;
    root /var/www/app/public;
    index index.php;

    access_log /var/log/nginx/yourapp_access.log;
    error_log  /var/log/nginx/yourapp_error.log;

    location / {
        try_files $uri /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff2?)$ {
        expires 30d;
        access_log off;
    }
}
```

## 3.2 Пример пула PHP-FPM (обычно уже есть)

* `/etc/php/8.3/fpm/pool.d/www.conf` — убедитесь, что `listen = /run/php/php8.3-fpm.sock`.
* Перезапуск: `systemctl reload php8.3-fpm`.

## 3.3 Systemd юнит воркера (пример) `deploy/systemd/telegram-worker.service`

```ini
[Unit]
Description=Telegram Worker
After=network.target

[Service]
Type=simple
WorkingDirectory=/var/www/app
ExecStart=/usr/bin/php app/workers/telegram.php
Restart=always
RestartSec=3
User=www-data
Group=www-data
Environment=APP_ENV=prod

[Install]
WantedBy=multi-user.target
```

```bash
# активация
cp deploy/systemd/telegram-worker.service /etc/systemd/system/
systemctl daemon-reload
systemctl enable --now telegram-worker
```

---

# 4) Скрипты

## 4.1 `scripts/init.sh` — первичная настройка `.env` (интерактивно)

```bash
#!/usr/bin/env bash
set -euo pipefail

ENV_FILE=".env"
EXAMPLE=".env.example"

if [[ ! -f "$EXAMPLE" ]]; then
cat > "$EXAMPLE" <<'EOF'
APP_ENV=dev
APP_DEBUG=true

DB_DSN="mysql:host=127.0.0.1;dbname=app;charset=utf8mb4"
DB_USER="app"
DB_PASS="secret"
DB_NAME="app"
DB_ROOT_PASS="root"

JWT_SECRET="change_me"
JWT_TTL=3600
JWT_ALG=HS256

CORS_ORIGINS="*"

RATE_LIMIT_BUCKET=ip
RATE_LIMIT=60
EOF
fi

cp -n "$EXAMPLE" "$ENV_FILE" || true

echo "=== Инициализация .env ==="
read -rp "APP_ENV (dev/prod) [$(grep -E '^APP_ENV=' $ENV_FILE | cut -d= -f2)]: " APP_ENV || true
read -rp "APP_DEBUG (true/false) [$(grep -E '^APP_DEBUG=' $ENV_FILE | cut -d= -f2)]: " APP_DEBUG || true

read -rp "DB host [127.0.0.1]: " DB_HOST || true
DB_HOST=${DB_HOST:-127.0.0.1}
read -rp "DB name [app]: " DB_NAME || true
DB_NAME=${DB_NAME:-app}
read -rp "DB user [app]: " DB_USER || true
DB_USER=${DB_USER:-app}
read -rp "DB pass [secret]: " DB_PASS || true
DB_PASS=${DB_PASS:-secret}

read -rp "JWT secret [change_me]: " JWT_SECRET || true
JWT_SECRET=${JWT_SECRET:-change_me}

# write
sed -i "s#^APP_ENV=.*#APP_ENV=${APP_ENV:-dev}#g" $ENV_FILE
sed -i "s#^APP_DEBUG=.*#APP_DEBUG=${APP_DEBUG:-true}#g" $ENV_FILE
sed -i "s#^DB_DSN=.*#DB_DSN=\"mysql:host=${DB_HOST};dbname=${DB_NAME};charset=utf8mb4\"#g" $ENV_FILE
sed -i "s#^DB_USER=.*#DB_USER=\"${DB_USER}\"#g" $ENV_FILE
sed -i "s#^DB_PASS=.*#DB_PASS=\"${DB_PASS}\"#g" $ENV_FILE
sed -i "s#^DB_NAME=.*#DB_NAME=\"${DB_NAME}\"#g" $ENV_FILE
sed -i "s#^JWT_SECRET=.*#JWT_SECRET=\"${JWT_SECRET}\"#g" $ENV_FILE

echo "OK: .env обновлён."
```

## 4.2 `scripts/deploy.sh` — быстрый старт (Docker или VPS)

```bash
#!/usr/bin/env bash
set -euo pipefail

MODE=${1:-docker}   # docker|vps

case "$MODE" in
  docker)
    echo "[Docker] build & up"
    docker compose up -d --build
    docker compose exec app php vendor/bin/phinx migrate || true
    ;;
  vps)
    echo "[VPS] composer + migrate"
    composer install --no-dev --optimize-autoloader
    vendor/bin/phinx migrate -e production || true
    ;;
  *)
    echo "usage: $0 [docker|vps]"
    exit 1
    ;;
esac

echo "Done."
```

---

# 5) Health-endpoint (добавьте для проверок)

**`app/Controllers/Api/HealthController.php`**

```php
<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use PDO;
use Psr\Http\Message\ResponseInterface as Res;
use Psr\Http\Message\ServerRequestInterface as Req;
use App\Helpers\Response;

final class HealthController
{
    public function __construct(private PDO $pdo) {}

    public function __invoke(Req $req, Res $res): Res
    {
        try {
            $this->pdo->query('SELECT 1');
            return Response::json($res, 200, ['status' => 'ok']);
        } catch (\Throwable $e) {
            return Response::problem($res, 500, 'DB unavailable');
        }
    }
}
```

В `public/index.php` добавьте:

```php
$api->get('/health', \App\Controllers\Api\HealthController::class);
```

---

# 6) Подсказки по безопасности/продакшену

* Docker: включите OPcache (`docker-php-ext-enable opcache`, ini-настройки) — можно расширить Dockerfile.
* VPS: включите OPcache в `php.ini`, запретите `display_errors` в prod.
* Обновите `JWT_SECRET` на уникальный, храните `.env` вне репозитория.
* Ограничьте доступ к `/admin` по IP/базовой авторизации (Nginx), если админка приватная.
