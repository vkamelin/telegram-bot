# Руководство по вкладу

## Общие правила

- Соблюдай PSR-12.
- Включай `declare(strict_types=1)` во всех файлах.
- Все ответы от API возвращаются через Helpers\Response.
- PDO используется напрямую (никаких ORM/репозиториев).
- Middleware не размножаем: только Error, Jwt, Csrf, RateLimit, TelegramInitData.

## Добавление новой фичи

1. Определи маршрут в `public/index.php` в нужной группе (`/api` или `/dashboard`).
2. Создай контроллер в `app/Controllers/Api/` или `Dashboard/`.
3. В контроллере пиши SQL-запросы напрямую через PDO.
4. Валидируй входные данные (минимум: `filter_var`, `trim`).
5. Ответ возвращай через `Helpers\Response::json` или `problem`.

## Что запрещено

- Создавать новые слои (Service, Repository и т.п.) без необходимости.
- Подключать ORM или дополнительные фреймворки.
- Возвращать json напрямую через `echo/json_encode`.

## Тесты

- Unit-тестируй утилиты и middleware.
- Smoke-тесты для ключевых эндпойнтов (200, 400, 401, 403, 429).

## Pull Requests

- Изменилась архитектура? Обнови `ARCHITECTURE.md`.
- Добавил сущность? Опиши её в `CHANGELOG.md`.
- Изменения группируй по `Added`, `Changed`, `Removed` и при релизе обновляй версию и дату.
- Все PR должны проходить `composer cs` и `composer tests`.
