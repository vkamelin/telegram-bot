# API — Общее описание

## Назначение
Краткое описание, для чего предназначен API.

## Основные эндпоинты
- `GET /api/health` — проверка состояния сервиса
- `POST /api/auth/login` — выдача JWT-токена по email и паролю
- `GET /api/me` — данные текущего пользователя
- `GET /api/users` — список пользователей
- `POST /api/users` — создание пользователя

## Форматы данных
API использует JSON для всех запросов и ответов.

- тело запросов отправляйте в формате JSON и указывайте заголовок `Content-Type: application/json`;
- ответы возвращаются с заголовком `Content-Type: application/json`.

## Примеры запросов
### `GET /api/health`
```bash
curl http://localhost:8080/api/health \
  -H "X-Telegram-Init-Data: <initData>"
```
**Ответ:**
```json
{"status":"ok"}
```

### `POST /api/auth/login`
```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -H "X-Telegram-Init-Data: <initData>" \
  -d '{"email":"user@example.com","password":"secret"}'
```
**Ответ:**
```json
{"token":"<jwt>"}
```

### `GET /api/me`
```bash
curl http://localhost:8080/api/me \
  -H "Authorization: Bearer <jwt>" \
  -H "X-Telegram-Init-Data: <initData>"
```
**Ответ:**
```json
{"user":{"id":1,"email":"a@b.c","created_at":"2025-01-01"}}
```

### `GET /api/users`
```bash
curl http://localhost:8080/api/users \
  -H "Authorization: Bearer <jwt>" \
  -H "X-Telegram-Init-Data: <initData>"
```
**Ответ:**
```json
{"items":[{"id":1,"email":"user@example.com","created_at":"2025-01-01"}]}
```

### `POST /api/users`
```bash
curl -X POST http://localhost:8080/api/users \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <jwt>" \
  -H "X-Telegram-Init-Data: <initData>" \
  -d '{"email":"new@example.com"}'
```
**Ответ:**
```json
{"id":2}
```

## Ограничения и квоты
- RateLimit: не более 60 запросов в минуту на IP
- Максимальный размер тела запроса — 1&nbsp;МБ
- Все запросы должны содержать init data в заголовке `Authorization: tma <initData>`,
  `X-Telegram-Init-Data` или параметре `initData`
- Для защищённых эндпоинтов требуется заголовок `Authorization: Bearer <jwt>`
