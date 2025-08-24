#!/bin/sh
set -e

if [ ! -f /var/www/html/.env ]; then
  echo "=> Creating .env from .env.example"
  cp /var/www/html/.env.example /var/www/html/.env
fi

# Ensure log directories exist and writable
mkdir -p /var/www/html/storage/logs /var/www/html/runtime/logs
chown -R www-data:www-data /var/www/html/storage/logs /var/www/html/runtime/logs

echo "==> Run migrations"
php /var/www/html/run migrate

echo "==> Start php-fpm"
/usr/local/sbin/php-fpm --nodaemonize &

echo "==> Start supervisord"
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf
