#!/usr/bin/env bash
set -euo pipefail

ENV_FILE=".env"

cp -n .env.example $ENV_FILE 2>/dev/null || true

read -rp "APP_ENV (dev/prod) [$(grep -E '^APP_ENV=' $ENV_FILE | cut -d= -f2)]: " APP_ENV || true
read -rp "APP_DEBUG (true/false) [$(grep -E '^APP_DEBUG=' $ENV_FILE | cut -d= -f2)]: " APP_DEBUG || true

read -rp "DB host [db]: " DB_HOST || true
DB_HOST=${DB_HOST:-db}
read -rp "DB name [app]: " DB_NAME || true
DB_NAME=${DB_NAME:-app}
read -rp "DB user [app]: " DB_USER || true
DB_USER=${DB_USER:-app}
read -rp "DB pass [secret]: " DB_PASS || true
DB_PASS=${DB_PASS:-secret}

read -rp "JWT secret [change_me]: " JWT_SECRET || true
JWT_SECRET=${JWT_SECRET:-change_me}

sed -i "s#^APP_ENV=.*#APP_ENV=${APP_ENV:-dev}#g" $ENV_FILE
sed -i "s#^APP_DEBUG=.*#APP_DEBUG=${APP_DEBUG:-true}#g" $ENV_FILE
sed -i "s#^DB_DSN=.*#DB_DSN=\\\"mysql:host=${DB_HOST};dbname=${DB_NAME};charset=utf8mb4\\\"#g" $ENV_FILE
sed -i "s#^DB_USER=.*#DB_USER=\\\"${DB_USER}\\\"#g" $ENV_FILE
sed -i "s#^DB_PASS=.*#DB_PASS=\\\"${DB_PASS}\\\"#g" $ENV_FILE
sed -i "s#^DB_NAME=.*#DB_NAME=\\\"${DB_NAME}\\\"#g" $ENV_FILE
sed -i "s#^JWT_SECRET=.*#JWT_SECRET=\\\"${JWT_SECRET}\\\"#g" $ENV_FILE

echo "OK: .env updated."
