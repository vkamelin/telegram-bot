# Руководство разработчика

## Подготовка окружения
- Установить PHP 8.3
- Создать файл `.env` на основе `.env.example`
- Запустить `composer install`
- Выполнить `php bin/console migrate:run`
- Docker up

## Запуск
- `composer serve`
- Запуск воркеров из каталога `workers/` (`php workers/<worker>.php`)
- `composer tests`

## Структура каталогов
Смотрите [project_structure.md](project_structure.md) и [README.md](../README.md) для дополнительных деталей.

## Полезные команды
- `vendor/bin/phinx migrate` — выполнить миграции
- `vendor/bin/phinx rollback` — откатить миграции
- `rm -f var/log/*.log` — очистка логов
- `php workers/<worker>.php` — запуск конкретного воркера
- `composer dump-autoload`

## Отправка медиа

```php
use App\Helpers\Push;
use App\Helpers\MediaBuilder;

// одиночное изображение
$photo = MediaBuilder::buildInputMedia('photo', 'https://example.com/a.jpg', ['caption' => 'Привет']);
Push::photo(123, $photo);

// медиагруппа
$media = [
    MediaBuilder::buildInputMedia('photo', 'https://example.com/a.jpg', ['caption' => 'Первая']),
    MediaBuilder::buildInputMedia('photo', 'https://example.com/b.jpg'),
];
Push::mediaGroup(123, $media);
```
