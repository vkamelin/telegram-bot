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

### Промокоды (JWT)
- `POST /api/promo-codes/upload` — загрузка CSV (multipart/form-data, поле `file`). Первая строка — заголовок, обязательная колонка `code` (опц. `expires_at`, `meta`). При наличии дублей — 409 Conflict.
- `GET /api/promo-codes` — список кодов. Параметры: `status`, `batch_id`, `q`, `page`, `per_page`.
- `POST /api/promo-codes/issue` — выдача промокода пользователю: JSON `{ "user_id": <telegram_user_id>, "batch_id"?: <id> }`.
- `GET /api/promo-code-issues` — последние выдачи (параметр `limit`).
- `GET /api/promo-code-batches` — список батчей.

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

### Промокоды: список
```bash
curl "http://localhost:8080/api/promo-codes?status=available&per_page=20" \
  -H "Authorization: Bearer <jwt>"
```
Ответ:
```json
{"items":[{"id":1,"batch_id":1,"code":"ABC","status":"available","expires_at":null,"issued_at":null}],"total":1,"page":1,"per_page":20}
```

### Промокоды: выдача
```bash
curl -X POST http://localhost:8080/api/promo-codes/issue \
  -H "Authorization: Bearer <jwt>" \
  -H "Content-Type: application/json" \
  -d '{"user_id":123456789}'
```
Ответ:
```json
{"code_id":10,"code":"ABCD-1234"}
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
