# Руководство пользователя

## Установка
1. Зарегистрируйте пользователя, отправив запрос:
   ```bash
   curl -X POST /api/auth/register \
        -H 'Content-Type: application/json' \
        -d '{"email":"user@example.com","password":"secret"}'
   ```
2. Получите токен через `POST /api/auth/login`:
   ```bash
   curl -X POST /api/auth/login \
        -H 'Content-Type: application/json' \
        -d '{"email":"user@example.com","password":"secret"}'
   ```
   В ответе вернётся поле `token`, которое необходимо передавать в заголовке `Authorization: Bearer <token>` при дальнейших запросах.

## Основные функции
1. **Получение профиля**
   ```bash
   curl -H 'Authorization: Bearer <token>' /api/me
   ```

## Частые вопросы
- Как восстановить пароль?
- Как изменить email?
