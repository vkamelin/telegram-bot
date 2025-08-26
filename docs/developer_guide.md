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

$photo = MediaBuilder::buildInputMedia('photo', '/path/a.jpg', [
    'caption' => '<b>Привет</b>',
    'parse_mode' => 'HTML',
]);

$video = MediaBuilder::buildInputMedia('video', '/path/b.mp4', [
    'caption' => 'Клип',
    'width' => 640,
    'height' => 360,
]);

// одиночное фото
Push::photo(123, $photo);

// медиагруппа с видео
Push::mediaGroup(123, [$photo, $video]);
```

### Через Dashboard

1. Перейдите в `/dashboard/messages/create`.
2. Выберите тип сообщения и заполните соответствующие поля.
3. Укажите получателей и отправьте сообщение.

| Тип | Параметры |
| --- | --- |
| `text` | `text` |
| `photo` | `caption`, `parse_mode`, `has_spoiler` |
| `audio` | `caption`, `parse_mode`, `duration`, `performer`, `title` |
| `video` | `caption`, `parse_mode`, `width`, `height`, `duration`, `has_spoiler` |
| `document` | `caption`, `parse_mode` |
| `sticker` | — |
| `animation` | `caption`, `parse_mode`, `width`, `height`, `duration`, `has_spoiler` |
| `voice` | `caption`, `parse_mode`, `duration` |
| `video_note` | `length`, `duration` |
| `media_group` | `caption`, `parse_mode` (только для первого элемента) |

Загруженные файлы сохраняются в `storage/messages`. Размер запроса ограничен переменной `REQUEST_SIZE_LIMIT` в `.env` (по умолчанию 1 МБ); также учитывайте ограничения Telegram Bot API (например, фото/видео до 20 МБ).

