# DEPLOYMENT

Два способа запуска:
- **Docker** — быстрый и воспроизводимый.
- **VPS (без Docker)** — Nginx + PHP-FPM + Systemd.

## A. Docker

### 1) Первая настройка

```bash
chmod +x scripts/init.sh scripts/deploy.sh docker/entrypoint.sh
./scripts/init.sh       # заполнит .env (интерактивно)
```

### 2) Запуск

```bash
docker compose up -d --build
docker compose logs -f
```

Проверка:

* API: `http://localhost:8080/api/health`
* Dashboard: `http://localhost:8080/dashboard`

### 3) Миграции

```bash
docker compose exec app php vendor/bin/phinx migrate
```

## B. VPS (без Docker)

### 1) Зависимости

* Nginx, PHP-FPM 8.3 (`php8.3-fpm php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-mysql`)
* Composer 2

### 2) Первая настройка

```bash
chmod +x scripts/init.sh scripts/deploy.sh
./scripts/init.sh   # заполнит .env (интерактивно)
composer install --no-dev --optimize-autoloader
php vendor/bin/phinx migrate -e production
```

### 3) Nginx

Скопируй `deploy/nginx/default.conf` в `/etc/nginx/sites-available/yourapp.conf`, обнови домен и путь, затем включи сайт:

```bash
ln -s /etc/nginx/sites-available/yourapp.conf /etc/nginx/sites-enabled/yourapp.conf
nginx -t && systemctl reload nginx
```

Убедись, что путь к сокету PHP-FPM совпадает с `fastcgi_pass`.

### 4) Обновление

```bash
git pull
composer install --no-dev --optimize-autoloader
php vendor/bin/phinx migrate -e production
# Docker: docker compose up -d --build
# VPS: systemctl reload nginx && systemctl reload php8.3-fpm
```

## Проверка

* `/api/health` возвращает `{"status":"ok"}`
* Миграции прошли без ошибок.
