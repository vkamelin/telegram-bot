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

        if (isset($options['caption']) && $options['caption'] !== '') {
            $data['caption'] = $options['caption'];
            $data['parse_mode'] = $options['parse_mode'] ?? 'html';
            unset($options['caption'], $options['parse_mode']);
        }

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

        if ($caption !== '' && !isset($payload['caption'])) {
            $payload['caption'] = $caption;
        }

        if (isset($payload['caption']) && !isset($payload['parse_mode'])) {
            $payload['parse_mode'] = 'html';
        }

        return array_merge($data, $payload, $options);
    }
}
