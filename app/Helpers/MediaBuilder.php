<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Подготовка структур InputMedia для Telegram API.
 */
class MediaBuilder
{
    /**
     * Фабричный метод формирования структуры InputMedia.
     *
     * @param string $type    Тип медиа (photo, audio, document, video и т.д.)
     * @param string $media   URL, fileId или путь к файлу
     * @param array  $options Дополнительные параметры InputMedia
     *
     * @return array
     */
    public static function buildInputMedia(string $type, string $media, array $options = []): array
    {
        $data = [
            'type' => $type,
            'media' => $media,
        ];

        // Обработка подписи и режима парсинга. Нужно учитывать случаи,
        // когда параметр передан со значением null - такой параметр не
        // должен попадать в итоговый массив, однако его наличие не должно
        // активировать значение по-умолчанию.
        if (array_key_exists('caption', $options)) {
            $caption = $options['caption'];
            unset($options['caption']);

            if ($caption !== null && $caption !== '') {
                $data['caption'] = $caption;

                if (array_key_exists('parse_mode', $options)) {
                    $parseMode = $options['parse_mode'];
                    unset($options['parse_mode']);

                    if ($parseMode !== null && $parseMode !== '') {
                        $data['parse_mode'] = $parseMode;
                    }
                } else {
                    // parse_mode не передан явно - используем значение по-умолчанию
                    $data['parse_mode'] = 'html';
                }
            } elseif (array_key_exists('parse_mode', $options)) {
                // Подпись пустая, но parse_mode передан - нужно корректно
                // обработать значение parse_mode и исключить null.
                $parseMode = $options['parse_mode'];
                unset($options['parse_mode']);

                if ($parseMode !== null && $parseMode !== '') {
                    $data['parse_mode'] = $parseMode;
                }
            }
        } elseif (array_key_exists('parse_mode', $options)) {
            $parseMode = $options['parse_mode'];
            unset($options['parse_mode']);

            if ($parseMode !== null && $parseMode !== '') {
                $data['parse_mode'] = $parseMode;
            }
        }

        // Удаляем из options параметры со значением null
        $options = array_filter($options, static fn ($value) => $value !== null);

        return array_merge($data, $options);
    }

    /**
     * Подготавливает данные для медиа-запросов Telegram API.
     *
     * @param int          $chatId    Id чата
     * @param string       $mediaType Тип медиа (photo, audio, document, video и т.д.)
     * @param array|string $media     URL, fileId или структура InputMedia
     * @param string       $caption   Текст подписи
     * @param array        $options   Дополнительные параметры метода
     *
     * @return array
     */
    public static function prepareMediaData(
        int $chatId,
        string $mediaType,
        array|string $media,
        string $caption = '',
        array $options = []
    ): array {
        $payload = is_array($media)
            ? $media
            : self::buildInputMedia($mediaType, $media, ['caption' => $caption]);

        $data = [
            'chat_id' => $chatId,
            $mediaType => $payload['media'],
        ];

        unset($payload['type'], $payload['media']);

        if ($caption !== '' && !array_key_exists('caption', $payload)) {
            $payload['caption'] = $caption;

            if (!array_key_exists('parse_mode', $payload)) {
                $payload['parse_mode'] = 'html';
            }
        }

        // Удаляем параметры со значением null как из payload, так и из options
        $payload = array_filter($payload, static fn ($value) => $value !== null);
        $options = array_filter($options, static fn ($value) => $value !== null);

        return array_merge($data, $payload, $options);
    }
}
