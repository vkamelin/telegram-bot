#!/usr/bin/env bash
set -euo pipefail

MODE=${1:-docker}   # docker|vps

case "$MODE" in
  docker)
    echo "[Docker] build & up"
    docker compose up -d --build
    docker compose exec app php vendor/bin/phinx migrate || true
    ;;
  vps)
    echo "[VPS] composer + migrate"
    composer install --no-dev --optimize-autoloader
    vendor/bin/phinx migrate -e production || true
    ;;
  *)
    echo "usage: $0 [docker|vps]"
    exit 1
    ;;
esac

echo "Done."
