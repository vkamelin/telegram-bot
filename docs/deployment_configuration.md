# Конфигурация деплоя

## Переменные окружения
- `DB_DSN` — строка подключения к базе данных
- `DB_USER` — пользователь БД
- `DB_PASS` — пароль БД
- `JWT_SECRET` — секретный ключ для выпуска JWT (init.sh генерирует автоматически, если не указан)
- `CORS_ORIGINS` — список разрешённых origin
- `RATE_LIMIT_BUCKET` — тип лимита (`ip` или `user`)
- `RATE_LIMIT` — количество запросов в минуту
- `REQUEST_SIZE_LIMIT` — максимальный размер тела запроса
- `BOT_TOKEN` — токен Telegram‑бота
- `TELEMETRY_ENABLED` — включить метрики и трассировку (`true`/`false`)

## Конфиги
Готовые конфиги Nginx и Supervisor лежат в каталоге `docker/`.

## Среды

### Dev
- запуск: Docker Compose (`docker compose up -d`)
- пример значений:
  - `DB_DSN`: `mysql:host=db;dbname=app_dev;charset=utf8mb4`
  - `CORS_ORIGINS`: `*`
  - `RATE_LIMIT`: `0` (без ограничений)

### Staging
- запуск: Kubernetes (`kubectl apply -f k8s/staging/`)
- пример значений:
  - `DB_DSN`: `mysql:host=db;dbname=app_staging;charset=utf8mb4`
  - `CORS_ORIGINS`: `https://staging.example.com`
  - `RATE_LIMIT`: `60`

### Prod
- запуск: Kubernetes (`kubectl apply -f k8s/prod/`)
- пример значений:
  - `DB_DSN`: `mysql:host=db;dbname=app;charset=utf8mb4`
  - `CORS_ORIGINS`: `https://example.com`
  - `RATE_LIMIT`: `60`

## Деплой
1. `composer install --no-dev`
2. `vendor/bin/phinx migrate` — запуск миграций
3. перезапустить воркеры (например, `supervisorctl restart workers:*` или `kubectl rollout restart deployment/worker`)
