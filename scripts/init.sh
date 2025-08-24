#!/usr/bin/env bash
set -euo pipefail

ENV_FILE=".env"

# Копируем пример, если .env отсутствует
cp -n .env.example "$ENV_FILE" 2>/dev/null || true

# Добавляет переменную с значением по умолчанию, если её нет
ensure_var() {
    local key="$1"
    local value="$2"
    grep -q "^${key}=" "$ENV_FILE" || echo "${key}=${value}" >> "$ENV_FILE"
}

# Гарантируем наличие всех переменных
ensure_var "APP_ENV" "dev"
ensure_var "APP_DEBUG" "true"
ensure_var "DB_HOST" '"db"'
ensure_var "DB_PORT" "3306"
ensure_var "DB_CHARSET" '"utf8mb4"'
ensure_var "DB_NAME" '"app"'
ensure_var "DB_USER" '"app"'
ensure_var "DB_PASS" '"secret"'
ensure_var "JWT_SECRET" '""'
ensure_var "CORS_ORIGINS" '"*"'
ensure_var "RATE_LIMIT_BUCKET" "ip"
ensure_var "RATE_LIMIT" "60"
ensure_var "REQUEST_SIZE_LIMIT" "1048576"
ensure_var "BOT_TOKEN" '""'
ensure_var "TG_ALLOW_TYPES" '""'
ensure_var "TG_DENY_TYPES" '""'
ensure_var "TG_ALLOW_CHATS" '""'
ensure_var "TG_DENY_CHATS" '""'
ensure_var "TG_ALLOW_COMMANDS" '""'
ensure_var "TG_DENY_COMMANDS" '""'
ensure_var "TG_FILTERS_FROM_REDIS" "false"
ensure_var "TG_FILTERS_REDIS_PREFIX" '"tg_filters:"'
ensure_var "TELEMETRY_ENABLED" "false"
ensure_var "WORKERS_GPT_PROCS" "1"
ensure_var "WORKERS_TELEGRAM_PROCS" "1"

# Читает значение по умолчанию из .env
get_default() {
    local key="$1"
    local val
    val=$(grep -E "^${key}=" "$ENV_FILE" | cut -d= -f2-)
    val="${val%\"}"
    val="${val#\"}"
    echo "$val"
}

# Считываем значения от пользователя
read -rp "Среда приложения APP_ENV (dev/prod) [$(get_default APP_ENV)]: " APP_ENV || true
read -rp "Режим отладки APP_DEBUG (true/false) [$(get_default APP_DEBUG)]: " APP_DEBUG || true
read -rp "Хост БД [$(get_default DB_HOST)]: " DB_HOST || true
read -rp "Порт БД [$(get_default DB_PORT)]: " DB_PORT || true
read -rp "Кодировка БД [$(get_default DB_CHARSET)]: " DB_CHARSET || true
read -rp "Имя БД [$(get_default DB_NAME)]: " DB_NAME || true
read -rp "Пользователь БД [$(get_default DB_USER)]: " DB_USER || true
read -rp "Пароль БД [$(get_default DB_PASS)]: " DB_PASS || true
read -rp "Секрет JWT (оставь пустым для генерации) [$(get_default JWT_SECRET)]: " JWT_SECRET || true
if [ -z "${JWT_SECRET:-$(get_default JWT_SECRET)}" ]; then
    JWT_SECRET="$(php -r 'echo bin2hex(random_bytes(32));')"
    echo "JWT_SECRET сгенерирован автоматически."
fi
read -rp "Разрешённые CORS origin (через запятую) [$(get_default CORS_ORIGINS)]: " CORS_ORIGINS || true
read -rp "Тип лимита (ip/user) [$(get_default RATE_LIMIT_BUCKET)]: " RATE_LIMIT_BUCKET || true
read -rp "Запросов в минуту [$(get_default RATE_LIMIT)]: " RATE_LIMIT || true
read -rp "Макс размер тела запроса в байтах [$(get_default REQUEST_SIZE_LIMIT)]: " REQUEST_SIZE_LIMIT || true
read -rp "Токен Telegram-бота [$(get_default BOT_TOKEN)]: " BOT_TOKEN || true
read -rp "Разрешённые типы обновлений (через запятую) [$(get_default TG_ALLOW_TYPES)]: " TG_ALLOW_TYPES || true
read -rp "Запрещённые типы обновлений (через запятую) [$(get_default TG_DENY_TYPES)]: " TG_DENY_TYPES || true
read -rp "Разрешённые чаты (ID через запятую) [$(get_default TG_ALLOW_CHATS)]: " TG_ALLOW_CHATS || true
read -rp "Запрещённые чаты (ID через запятую) [$(get_default TG_DENY_CHATS)]: " TG_DENY_CHATS || true
read -rp "Разрешённые команды (через запятую) [$(get_default TG_ALLOW_COMMANDS)]: " TG_ALLOW_COMMANDS || true
read -rp "Запрещённые команды (через запятую) [$(get_default TG_DENY_COMMANDS)]: " TG_DENY_COMMANDS || true
read -rp "Брать фильтры из Redis вместо ENV (true/false) [$(get_default TG_FILTERS_FROM_REDIS)]: " TG_FILTERS_FROM_REDIS || true
read -rp "Префикс ключей Redis для фильтров [$(get_default TG_FILTERS_REDIS_PREFIX)]: " TG_FILTERS_REDIS_PREFIX || true
read -rp "Включить телеметрию (true/false) [$(get_default TELEMETRY_ENABLED)]: " TELEMETRY_ENABLED || true
read -rp "Число процессов GPT [$(get_default WORKERS_GPT_PROCS)]: " WORKERS_GPT_PROCS || true
read -rp "Число процессов Telegram [$(get_default WORKERS_TELEGRAM_PROCS)]: " WORKERS_TELEGRAM_PROCS || true

# Подставляем значения по умолчанию, если пользователь ничего не ввёл
APP_ENV=${APP_ENV:-$(get_default APP_ENV)}
APP_DEBUG=${APP_DEBUG:-$(get_default APP_DEBUG)}
DB_HOST=${DB_HOST:-$(get_default DB_HOST)}
DB_PORT=${DB_PORT:-$(get_default DB_PORT)}
DB_CHARSET=${DB_CHARSET:-$(get_default DB_CHARSET)}
DB_NAME=${DB_NAME:-$(get_default DB_NAME)}
DB_USER=${DB_USER:-$(get_default DB_USER)}
DB_PASS=${DB_PASS:-$(get_default DB_PASS)}
CORS_ORIGINS=${CORS_ORIGINS:-$(get_default CORS_ORIGINS)}
RATE_LIMIT_BUCKET=${RATE_LIMIT_BUCKET:-$(get_default RATE_LIMIT_BUCKET)}
RATE_LIMIT=${RATE_LIMIT:-$(get_default RATE_LIMIT)}
REQUEST_SIZE_LIMIT=${REQUEST_SIZE_LIMIT:-$(get_default REQUEST_SIZE_LIMIT)}
BOT_TOKEN=${BOT_TOKEN:-$(get_default BOT_TOKEN)}
TG_ALLOW_TYPES=${TG_ALLOW_TYPES:-$(get_default TG_ALLOW_TYPES)}
TG_DENY_TYPES=${TG_DENY_TYPES:-$(get_default TG_DENY_TYPES)}
TG_ALLOW_CHATS=${TG_ALLOW_CHATS:-$(get_default TG_ALLOW_CHATS)}
TG_DENY_CHATS=${TG_DENY_CHATS:-$(get_default TG_DENY_CHATS)}
TG_ALLOW_COMMANDS=${TG_ALLOW_COMMANDS:-$(get_default TG_ALLOW_COMMANDS)}
TG_DENY_COMMANDS=${TG_DENY_COMMANDS:-$(get_default TG_DENY_COMMANDS)}
TG_FILTERS_FROM_REDIS=${TG_FILTERS_FROM_REDIS:-$(get_default TG_FILTERS_FROM_REDIS)}
TG_FILTERS_REDIS_PREFIX=${TG_FILTERS_REDIS_PREFIX:-$(get_default TG_FILTERS_REDIS_PREFIX)}
TELEMETRY_ENABLED=${TELEMETRY_ENABLED:-$(get_default TELEMETRY_ENABLED)}
WORKERS_GPT_PROCS=${WORKERS_GPT_PROCS:-$(get_default WORKERS_GPT_PROCS)}
WORKERS_TELEGRAM_PROCS=${WORKERS_TELEGRAM_PROCS:-$(get_default WORKERS_TELEGRAM_PROCS)}

# Собираем строку подключения к БД
DB_DSN="mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_NAME};charset=${DB_CHARSET}"

# Записываем в .env
update_env() {
    local key="$1"
    local val="$2"
    val=$(printf '%s' "$val" | sed 's/[&/]/\\&/g')
    if [[ "$val" =~ ^[0-9]+$ || "$val" =~ ^(true|false)$ ]]; then
        sed -i "s#^$key=.*#$key=$val#g" "$ENV_FILE"
    else
        sed -i "s#^$key=.*#$key=\"$val\"#g" "$ENV_FILE"
    fi
}

update_env APP_ENV "$APP_ENV"
update_env APP_DEBUG "$APP_DEBUG"
update_env DB_HOST "$DB_HOST"
update_env DB_PORT "$DB_PORT"
update_env DB_CHARSET "$DB_CHARSET"
update_env DB_NAME "$DB_NAME"
update_env DB_USER "$DB_USER"
update_env DB_PASS "$DB_PASS"
update_env DB_DSN "$DB_DSN"
update_env JWT_SECRET "$JWT_SECRET"
update_env CORS_ORIGINS "$CORS_ORIGINS"
update_env RATE_LIMIT_BUCKET "$RATE_LIMIT_BUCKET"
update_env RATE_LIMIT "$RATE_LIMIT"
update_env REQUEST_SIZE_LIMIT "$REQUEST_SIZE_LIMIT"
update_env BOT_TOKEN "$BOT_TOKEN"
update_env TG_ALLOW_TYPES "$TG_ALLOW_TYPES"
update_env TG_DENY_TYPES "$TG_DENY_TYPES"
update_env TG_ALLOW_CHATS "$TG_ALLOW_CHATS"
update_env TG_DENY_CHATS "$TG_DENY_CHATS"
update_env TG_ALLOW_COMMANDS "$TG_ALLOW_COMMANDS"
update_env TG_DENY_COMMANDS "$TG_DENY_COMMANDS"
update_env TG_FILTERS_FROM_REDIS "$TG_FILTERS_FROM_REDIS"
update_env TG_FILTERS_REDIS_PREFIX "$TG_FILTERS_REDIS_PREFIX"
update_env TELEMETRY_ENABLED "$TELEMETRY_ENABLED"
update_env WORKERS_GPT_PROCS "$WORKERS_GPT_PROCS"
update_env WORKERS_TELEGRAM_PROCS "$WORKERS_TELEGRAM_PROCS"

# Готово
echo "ОК: .env обновлён."
