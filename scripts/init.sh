#!/usr/bin/env bash
set -euo pipefail

ENV_FILE=".env"

cp -n .env.example $ENV_FILE 2>/dev/null || true

read -rp "Среда приложения APP_ENV (dev/prod) [$(grep -E '^APP_ENV=' $ENV_FILE | cut -d= -f2)]: " APP_ENV || true
read -rp "Режим отладки APP_DEBUG (true/false) [$(grep -E '^APP_DEBUG=' $ENV_FILE | cut -d= -f2)]: " APP_DEBUG || true

read -rp "Хост БД [db]: " DB_HOST || true
DB_HOST=${DB_HOST:-db}
read -rp "Имя БД [app]: " DB_NAME || true
DB_NAME=${DB_NAME:-app}
read -rp "Пользователь БД [app]: " DB_USER || true
DB_USER=${DB_USER:-app}
read -rp "Пароль БД [secret]: " DB_PASS || true
DB_PASS=${DB_PASS:-secret}

read -rp "Секрет JWT (оставь пустым для генерации): " JWT_SECRET || true
if [ -z "${JWT_SECRET:-}" ]; then
    JWT_SECRET="$(php -r 'echo bin2hex(random_bytes(32));')"
    echo "JWT_SECRET сгенерирован автоматически."
fi

sed -i "s#^APP_ENV=.*#APP_ENV=${APP_ENV:-dev}#g" $ENV_FILE
sed -i "s#^APP_DEBUG=.*#APP_DEBUG=${APP_DEBUG:-true}#g" $ENV_FILE
sed -i "s#^DB_DSN=.*#DB_DSN=\\\"mysql:host=${DB_HOST};dbname=${DB_NAME};charset=utf8mb4\\\"#g" $ENV_FILE
sed -i "s#^DB_USER=.*#DB_USER=\\\"${DB_USER}\\\"#g" $ENV_FILE
sed -i "s#^DB_PASS=.*#DB_PASS=\\\"${DB_PASS}\\\"#g" $ENV_FILE
sed -i "s#^DB_NAME=.*#DB_NAME=\\\"${DB_NAME}\\\"#g" $ENV_FILE
sed -i "s#^JWT_SECRET=.*#JWT_SECRET=\\\"${JWT_SECRET}\\\"#g" $ENV_FILE

echo "ОК: .env обновлён."
