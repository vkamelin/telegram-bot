# Конфигурация окружения (.env)

Проект использует файл `.env` для хранения чувствительных параметров и конфигурации.  
Все переменные окружения читаются в `app/Config/config.php`.

## Правила
- `.env` всегда в `.gitignore` — не коммитить секреты.
- Для новых переменных добавляй их в `.env.example` с комментарием.
- В коде использовать только `$_ENV` или обёртку в `config.php`.
- Значения хранятся в **строковом формате**, при необходимости кастуются в PHP.

## Пример `.env.example` с комментариями
```ini
# === Общие настройки ===
APP_ENV=dev            # окружение: dev, prod
APP_DEBUG=true         # включить подробные ошибки (true/false)

# === База данных ===
DB_DSN="mysql:host=127.0.0.1;dbname=app;charset=utf8mb4"
DB_USER="root"
DB_PASS="secret"

# === JWT токены ===
JWT_SECRET="change_me" # секретный ключ (заменить в проде)
JWT_TTL=3600           # срок жизни токена в секундах
JWT_ALG=HS256          # алгоритм (обычно HS256)

# === CORS ===
CORS_ORIGINS="*"       # список разрешённых origin через запятую

# === Telegram ===
BOT_TOKEN="0000000000:AA..." # токен Telegram-бота для проверки initData

# === Rate limit ===
RATE_LIMIT_BUCKET=ip   # тип лимита: ip или user
RATE_LIMIT=60          # запросов в минуту

# === Redis (опционально) ===
REDIS_DSN="tcp://127.0.0.1:6379"
````

## Использование

В `app/Config/config.php`:

```php
'bot_token' => getenv('BOT_TOKEN'),

'db' => [
    'dsn'  => getenv('DB_DSN'),
    'user' => getenv('DB_USER'),
    'pass' => getenv('DB_PASS'),
],
```

## Проверка initData

`TelegramInitDataMiddleware` валидирует подпись `initData` с помощью `BOT_TOKEN` из `.env`. Передавать данные можно тремя способами:

1. Заголовок `Authorization: tma <initData>`
2. Заголовок `X-Telegram-Init-Data: <initData>`
3. Параметр `initData` в query или body

Примеры:

```bash
curl http://localhost:8080/api/health -H "Authorization: tma <initData>"
curl http://localhost:8080/api/health -H "X-Telegram-Init-Data: <initData>"
curl "http://localhost:8080/api/health?initData=<initData>"
```
