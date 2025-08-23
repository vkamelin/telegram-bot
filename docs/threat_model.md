# Модель угроз

## Методология
STRIDE

## Угрозы
- Spoofing
- Tampering
- DoS
- SQL-инъекции
- CSRF (для Dashboard)
- Утечки токенов
- Перебор JWT

## Контрмеры
- 2FA
- HMAC
- `RateLimitMiddleware`
- Подготовленные выражения PDO
- `CsrfMiddleware`
- `SecurityHeadersMiddleware`
