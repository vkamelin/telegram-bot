# API: краткий справочник

## Аутентификация и контекст Telegram
- Для защищённых эндпоинтов используйте `Authorization: Bearer <jwt>`.
- Для привязки контекста Mini App добавляйте `X-Telegram-Init-Data: <initData>` (или `Authorization: tma <initData>`/параметр `initData`).

## Эндпоинты
- `GET /api/health` — проверка состояния.
- `POST /api/auth/login` — вход по email/паролю, выдаёт JWT и refresh (если реализовано в контроллере).
- `POST /api/auth/refresh` — обновление JWT по refresh‑токену.
- `GET /api/me` — профиль текущего пользователя (JWT).
- `GET /api/users` — список пользователей (JWT).
- `POST /api/users` — создать пользователя (JWT).

## Примеры
### Health
```bash
curl http://localhost:8080/api/health \
  -H "X-Telegram-Init-Data: <initData>"
```
Ответ:
```json
{"status":"ok"}
```

### Вход
```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -H "X-Telegram-Init-Data: <initData>" \
  -d '{"email":"user@example.com","password":"secret"}'
```
Ответ:
```json
{"token":"<jwt>"}
```

### Профиль
```bash
curl http://localhost:8080/api/me \
  -H "Authorization: Bearer <jwt>" \
  -H "X-Telegram-Init-Data: <initData>"
```
Пример ответа:
```json
{"user":{"id":1,"email":"a@b.c","created_at":"2025-01-01"}}
```

### Пользователи: список
```bash
curl http://localhost:8080/api/users \
  -H "Authorization: Bearer <jwt>" \
  -H "X-Telegram-Init-Data: <initData>"
```
Ответ:
```json
{"items":[{"id":1,"email":"user@example.com","created_at":"2025-01-01"}]}
```

### Пользователи: создание
```bash
curl -X POST http://localhost:8080/api/users \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <jwt>" \
  -H "X-Telegram-Init-Data: <initData>" \
  -d '{"email":"new@example.com"}'
```
Ответ:
```json
{"id":2}
```

## Лимиты и защита
- Rate‑limit по IP/пользователю (по умолчанию ~60 запросов/мин).
- Лимит размера тела запроса `REQUEST_SIZE_LIMIT` (по умолчанию 1 МБ).
- Все ошибки в формате RFC 7807 (`application/problem+json`).

