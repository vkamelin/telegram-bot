# Руководство пользователя

Все запросы к API должны включать `initData`. В примерах ниже он передаётся в заголовке `X-Telegram-Init-Data: <initData>`.

## Установка
1. Получите токен через `POST /api/auth/login`:
   ```bash
   curl -X POST /api/auth/login \
        -H 'Content-Type: application/json' \
        -H 'X-Telegram-Init-Data: <initData>' \
        -d '{"email":"user@example.com","password":"secret"}'
   ```
   В ответе вернётся поле `token`, которое необходимо передавать в заголовке `Authorization: Bearer <token>` при дальнейших запросах.

## Основные функции
1. **Получение профиля**
   ```bash
   curl -H 'Authorization: Bearer <token>' \
        -H 'X-Telegram-Init-Data: <initData>' \
        /api/me
   ```

## Администрирование
### Создание администратора

```bash
php bin/console admin:create
```

Команда запросит email и пароль и добавит пользователя в таблицу `users`.

## Частые вопросы
- Как восстановить пароль?
- Как изменить email?
