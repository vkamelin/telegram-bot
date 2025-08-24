#!/usr/bin/env pwsh
param(
    [string]$Mode = "docker"
)

switch ($Mode) {
    "docker" {
        Write-Host "[Docker] сборка и запуск"
        docker compose up -d --build
        docker compose exec app php vendor/bin/phinx migrate | Out-Null
    }
    "vps" {
        Write-Host "[VPS] composer и миграции"
        composer install --no-dev --optimize-autoloader
        vendor/bin/phinx migrate -e production | Out-Null
    }
    default {
        Write-Host "использование: .\\deploy.ps1 [docker|vps]"
        exit 1
    }
}

Write-Host "Готово."
