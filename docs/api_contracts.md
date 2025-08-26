# API Контракты

## Общие положения
### Версионирование API
Текущая версия API — `v1`, все конечные точки располагаются по пути `/api`.

### Обязательные заголовки
- `X-Request-Id` — уникальный идентификатор запроса. Если не указан, сервер сгенерирует его автоматически.
- `Authorization: Bearer <token>` — обязателен для всех защищённых эндпоинтов.
- `initData` — данные авторизации из Telegram. Передаются одним из следующих заголовков:
  - `X-Telegram-Init-Data: <initData>`
  - `Authorization: tma <initData>`

## POST /api/auth/login
### Заголовки
- `X-Request-Id: <uuid>`
- `Authorization: tma <initData>`
### Запрос
```json
{
  "email": "user@example.com",
  "password": "string"
}
```
### Ответ
```json
{
  "token": "string"
}
```

## GET /api/me
### Заголовки
- `Authorization: Bearer <token>`
- `X-Telegram-Init-Data: <initData>`
- `X-Request-Id: <uuid>`
### Ответ
```json
{
  "user": {
    "id": 1,
    "email": "user@example.com",
    "created_at": "2024-01-01T00:00:00+00:00"
  }
}
```

## GET /api/users
### Заголовки
- `Authorization: Bearer <token>`
- `X-Telegram-Init-Data: <initData>`
- `X-Request-Id: <uuid>`
### Ответ
```json
{
  "items": [
    {
      "id": 1,
      "email": "user@example.com",
      "created_at": "2024-01-01T00:00:00+00:00"
    }
  ]
}
```

## POST /api/users
### Заголовки
- `Authorization: Bearer <token>`
- `X-Telegram-Init-Data: <initData>`
- `X-Request-Id: <uuid>`
### Запрос
```json
{
  "email": "new@example.com"
}
```
### Ответ
```json
{
  "id": 2
}
```
