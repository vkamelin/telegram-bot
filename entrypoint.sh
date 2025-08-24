#!/bin/sh
set -e

# Ensure log directories exist and writable
mkdir -p /var/www/html/storage/logs /var/www/html/runtime/logs
chown -R www-data:www-data /var/www/html/storage/logs /var/www/html/runtime/logs

echo "==> Initialize application"
/var/www/html/scripts/init.sh </dev/null

# Read DB connection info from .env
DB_HOST=$(grep '^DB_HOST=' /var/www/html/.env | cut -d= -f2 | tr -d '"')
DB_PORT=$(grep '^DB_PORT=' /var/www/html/.env | cut -d= -f2 | tr -d '"')

echo "==> Waiting for database at ${DB_HOST}:${DB_PORT}"
until nc -z "$DB_HOST" "$DB_PORT"; do
  echo '... database is unavailable - waiting ...'
  sleep 2
done

echo "==> Run migrations"
php /var/www/html/run migrate:run

echo "==> Start php-fpm"
/usr/local/sbin/php-fpm --nodaemonize &

echo "==> Start supervisord"
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf
