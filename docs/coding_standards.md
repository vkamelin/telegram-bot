# Стандарты кодирования

## PSR-12 и строгая типизация
- Соблюдай PSR-12.
- В начале каждого PHP-файла указывай `declare(strict_types=1)`.

## PHPDoc и документирование
- Документируй все публичные классы, методы и свойства.
- PHPDoc используй только там, где типы не очевидны из кода.
- Пример оформления:

```php
/**
 * Контроллер для работы с пользователями.
 *
 * Обрабатывает маршруты /api/users.
 */
final class UsersController
{
    /** PDO-подключение к базе данных */
    private PDO $pdo;

    /** Конфиг JWT (secret, alg, ttl) */
    private array $jwtCfg;

    /** Максимальное количество пользователей в выдаче */
    private const MAX_USERS = 100;

    /**
     * Возвращает список пользователей.
     *
     * @param Req $req HTTP-запрос
     * @param Res $res HTTP-ответ
     * @return Res JSON с пользователями
     */
    public function list(Req $req, Res $res): Res
    {
        // нормализуем email перед проверкой
        $email = strtolower(trim($data['email'] ?? ''));

        return $res;
    }
}
```

## Примеры комментариев к SQL
```php
// Получаем последних 100 пользователей по дате создания
$stmt = $pdo->query('SELECT id, email, created_at FROM users ORDER BY created_at DESC LIMIT 100');
```

## TODO / FIXME
- `// TODO:` для отложенных задач.
- `// FIXME:` для временного или некорректного решения.

## JS/TS
- eslint
- prettier

## Git
- Сообщения коммитов в формате `<type>: <summary>` (`feat`, `fix`, `docs`, `refactor`, `test`, `chore`).
- Перед коммитом запускай `composer cs` и `composer tests`; коммит должен содержать только отформатированный и проверенный код.
