#!/bin/sh
set -e

# run migrations if phinx is available
if [ -f vendor/bin/phinx ]; then
    if [ -n "$DB_DSN" ]; then
        php vendor/bin/phinx migrate || true
    else
        echo "Warning: DB_DSN is not set; skipping migrations"
    fi
fi

exec "$@"
