#!/bin/sh
set -e

# run migrations if phinx is available
if [ -f vendor/bin/phinx ]; then
    php vendor/bin/phinx migrate || true
fi

exec "$@"
