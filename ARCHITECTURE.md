# Архитектура проекта

## Цели

- Минимализм: простая структура, быстрая разработка.
- Прозрачность: новый разработчик понимает за 15 минут.
- Контроль: никакого усложнения без необходимости.
- Безопасность: JWT, CSRF, rate-limit, RFC7807.

## Структура каталогов

```
app/
Controllers/
Api/          # API эндпойнты (/api/*)
Dashboard/    # Dashboard (/admin/*)
Middleware/   # Jwt, Csrf, RateLimit, Error
Services/     # Доп. классы (опционально)
Helpers/      # Утилиты (Response, Arr и др.)
Config/       # config.php (DB, JWT, CORS и т.д.)
public/
index.php     # единая точка входа (bootstrap + маршруты)
vendor/
```

## Принципы

1. **Единый вход** — `public/index.php`.
2. **Контроллеры** — простые классы, работа напрямую с PDO.
3. **PDO напрямую** — `$pdo->query()`, `$pdo->prepare()->execute()`.
4. **Middleware** — только Jwt, Csrf, RateLimit, Error.
5. **Helpers\Response** — единый способ ответов (json/problem).
6. **Dashboard и API** — разделены префиксами (`/admin`, `/api`).
7. **Воркеры** — не изменяются.

## Чек-лист Code Review

- [ ] Новая фича = контроллер + маршрут (макс. 2–3 файла).
- [ ] Ответы только через Helpers\Response.
- [ ] Без новых слоёв (Services — только если реально нужна переиспользуемая логика).
- [ ] PDO используется напрямую.
- [ ] Dashboard и API маршруты разделены.
- [ ] Middleware не плодятся (только базовые).