<?php

declare(strict_types=1);

namespace App\Helpers;

use RuntimeException;
use Throwable;

/**
 * Класс для отправки пушей в Телеграм-ботом
 *
 * @package   App\Classes
 * @author    Vitaliy Kamelin <v.kamelin@gmail.com>
 * @version   1.0
 * @copyright Copyright (c) 2024, Vitaliy Kamelin
 * @license   proprietary
 */
class Push
{
    
    /**
     * Метод добавляет в очередь текстовое сообщение в Телеграм
     *
     * @param int    $chatId   Id чата
     * @param string $text     Текст сообщения
     * @param string $type     Тип сообщения. Возможные значения: 'push', 'message'
     * @param int    $priority Приоритет сообщения. 2 - самый низкий, 0 - самый высокий. По-умолчанию 2
     * @param array  $options  Дополнительные параметры из API https://core.telegram.org/bots/api#sendmessage
     *
     * @return bool Удалось ли добавить в очередь на отправку. True - удалось, False - не удалось
     */
    public static function text(
        int $chatId,
        string $text,
        string $type = 'message',
        int $priority = 2,
        array $options = [],
        ?string $sendAfter = null
    ): bool {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'html'
        ];
        
        $data = array_merge($data, $options);
        
        return self::push($chatId, 'sendMessage', $data, $type, $priority, $sendAfter);
    }
    
    /**
     * Метод добавляет в очередь изображение по ссылке в Телеграм
     *
     * @param int         $chatId    Id чата
     * @param string      $photo     URL изображения, или fileId изображения, или относительный путь к локальному файлу
     * @param string      $caption   Текст подписи
     * @param string      $type      Тип сообщения. Возможные значения: 'push', 'message'
     * @param int         $priority  Приоритет сообщения. 2 - самый низкий, 0 - самый высокий. По-умолчанию 2
     * @param array       $options   Дополнительные параметры из API https://core.telegram.org/bots/api#sendphoto
     * @param string|null $sendAfter Время после которого отправлять сообщение в Телеграм
     *
     * @return bool Удалось ли добавить в очередь на отправку. True - удалось, False - не удалось
     */
    public static function photo(
        int $chatId,
        string $photo,
        string $caption = '',
        string $type = 'photo',
        int $priority = 2,
        array $options = [],
        ?string $sendAfter = null
    ): bool {
        $data = [
            'chat_id' => $chatId,
            'photo' => $photo,
            'parse_mode' => 'html'
        ];
        
        if (!empty($caption)) {
            $data['caption'] = $caption;
        }
        
        $data = array_merge($data, $options);
        
        return self::push($chatId, 'sendPhoto', $data, $type, $priority, $sendAfter);
    }
    
    /**
     * Метод добавляет в очередь аудио в Телеграм
     *
     * @param int    $chatId   Id чата
     * @param string $audio    URL аудио, или fileId аудио, или относительный путь к локальному файлу.
     * @param string $caption  Текст подписи
     * @param string $type     Тип сообщения. Возможные значения: 'push', 'message'
     * @param int    $priority Приоритет сообщения. 2 - самый низкий, 0 - самый высокий. По-умолчанию 2
     * @param array  $options  Дополнительные параметры из API https://core.telegram.org/bots/api#sendaudio
     *
     * @return bool Удалось ли добавить в очередь на отправку. True - удалось, False - не удалось
     */
    public static function audio(
        int $chatId,
        string $audio,
        string $caption = '',
        string $type = 'audio',
        int $priority = 2,
        array $options = [],
        ?string $sendAfter = null
    ): bool {
        $data = [
            'chat_id' => $chatId,
            'audio' => $audio,
            'parse_mode' => 'html'
        ];
        
        if (!empty($caption)) {
            $data['caption'] = $caption;
        }
        
        $data = array_merge($data, $options);
        
        return self::push($chatId, 'sendAudio', $data, $type, $priority, $sendAfter);
    }
    
    /**
     * Метод добавляет в очередь документа в Телеграм
     *
     * @param int    $chatId   Id чата
     * @param string $document URL документа, или fileId документа, или относительный путь к локальному файлу
     * @param string $caption  Текст подписи
     * @param string $type     Тип сообщения. Возможные значения: 'push', 'message'
     * @param int    $priority Приоритет сообщения. 2 - самый низкий, 0 - самый высокий. По-умолчанию 2
     * @param array  $options  Дополнительные параметры из API https://core.telegram.org/bots/api#senddocument
     *
     * @return bool Удалось ли добавить в очередь на отправку. True - удалось, False - не удалось
     */
    public static function document(
        int $chatId,
        string $document,
        string $caption = '',
        string $type = 'document',
        int $priority = 2,
        array $options = [],
        ?string $sendAfter = null
    ): bool {
        $data = [
            'chat_id' => $chatId,
            'document' => $document,
            'parse_mode' => 'html',
            'caption' => $caption,
            'disable_content_type_detection' => true,
        ];
        
        $data = array_merge($data, $options);
        
        return self::push($chatId, 'sendDocument', $data, $type, $priority, $sendAfter);
    }
    
    /**
     * Метод добавляет в очередь видео в Телеграм
     *
     * @param int    $chatId   Id чата
     * @param string $video    URL видео, или fileId видео, или относительный путь к локальному файлу
     * @param string $caption  Текст подписи
     * @param string $type     Тип сообщения. Возможные значения: 'push', 'message'
     * @param int    $priority Приоритет сообщения. 2 - самый низкий, 0 - самый высокий. По-умолчанию 2
     * @param array  $options  Дополнительные параметры из API https://core.telegram.org/bots/api#sendvideo
     *
     * @return bool Удалось ли добавить в очередь на отправку. True - удалось, False - не удалось
     */
    public static function video(
        int $chatId,
        string $video,
        string $caption = '',
        string $type = 'video',
        int $priority = 2,
        array $options = [],
        ?string $sendAfter = null
    ): bool {
        $data = [
            'chat_id' => $chatId,
            'video' => $video,
            'parse_mode' => 'html',
            'caption' => $caption,
        ];
        
        $data = array_merge($data, $options);
        
        return self::push($chatId, 'sendVideo', $data, $type, $priority, $sendAfter);
    }
    
    /**
     * Метод добавляет в очередь стикер в Телеграм
     *
     * @param int         $chatId    Id чата
     * @param string      $sticker   Стикер
     * @param string      $type      Тип сообщения. Возможные значения: 'push', 'message'
     * @param int         $priority  Приоритет сообщения. 2 - самый низкий, 0 - самый высокий. По-умолчанию 2
     * @param array       $options   Дополнительные параметры из API https://core.telegram.org/bots/api#sendsticker
     * @param string|null $sendAfter Дата и время, после которой отправляется запрос
     *
     * @return bool Удалось ли добавить в очередь на отправку. True - удалось, False - не удалось
     */
    public static function sticker(
        int $chatId,
        string $sticker,
        string $type = 'sticker',
        int $priority = 2,
        array $options = [],
        ?string $sendAfter = null
    ): bool {
        $data = [
            'chat_id' => $chatId,
            'sticker' => $sticker,
        ];
        
        $data = array_merge($data, $options);
        
        return self::push($chatId, 'sendSticker', $data, $type, $priority, $sendAfter);
    }
    
    /**
     * Метод добавляет в очередь анимацию в Телеграм
     *
     * @param int         $chatId    Id чата
     * @param string      $animation Анимация в формате GIF или H.264/MPEG-4 AVC размером до 50Мб
     * @param string      $caption   Краткое описание анимации до 1024 символов
     * @param string      $type      Тип сообщения. Возможные значения: 'push', 'message'
     * @param int         $priority  Приоритет сообщения. 2 - самый низкий, 0 - самый высокий. По-умолчанию 2
     * @param array       $options   Дополнительные параметры из API https://core.telegram.org/bots/api#sendanimation
     * @param string|null $sendAfter Дата и время, после которой отправляется запрос
     *
     * @return bool Удалось ли добавить в очередь на отправку. True - удалось, False - не удалось
     */
    public static function animation(
        int $chatId,
        string $animation,
        string $caption = '',
        string $type = 'animation',
        int $priority = 2,
        array $options = [],
        ?string $sendAfter = null
    ): bool {
        $data = [
            'chat_id' => $chatId,
            'animation' => $animation,
        ];
        
        if (!empty($caption)) {
            $data['caption'] = $caption;
        }
        
        $data = array_merge($data, $options);
        
        return self::push($chatId, 'sendAnimation', $data, $type, $priority, $sendAfter);
    }
    
    /**
     * Метод добавляет в очередь голосовое сообщение в Телеграм
     *
     * @param int         $chatId    Id чата
     * @param string      $voice     Звуковой файл .OGG кодированный в OPUS, или .MP3 format, или .M4A format
     * @param string      $type      Тип сообщения. Возможные значения: 'push', 'message'
     * @param int         $priority  Приоритет сообщения. 2 - самый низкий, 0 - самый высокий. По-умолчанию 2
     * @param array       $options   Дополнительные параметры из API https://core.telegram.org/bots/api#sendvoice
     * @param string|null $sendAfter Дата и время, после которой отправляется запрос
     *
     * @return bool Удалось ли добавить в очередь на отправку. True - удалось, False - не удалось
     */
    public static function voice(
        int $chatId,
        string $voice,
        string $type = 'voice',
        int $priority = 2,
        array $options = [],
        ?string $sendAfter = null
    ): bool {
        $data = [
            'chat_id' => $chatId,
            'voice' => $voice,
        ];
        
        $data = array_merge($data, $options);
        
        return self::push($chatId, 'sendVoice', $data, $type, $priority, $sendAfter);
    }
    
    /**
     * Метод добавляет в очередь видео кружочек в Телеграм
     *
     * @param int         $chatId    Id чата
     * @param string      $videoNote Квадратный видеофайл в формате .MP4 до 1Мб
     * @param string      $type      Тип сообщения. Возможные значения: 'push', 'message'
     * @param int         $priority  Приоритет сообщения. 2 - самый низкий, 0 - самый высокий. По-умолчанию 2
     * @param array       $options   Дополнительные параметры из API https://core.telegram.org/bots/api#sendvideonote
     * @param string|null $sendAfter Дата и время, после которой отправляется запрос
     *
     * @return bool Удалось ли добавить в очередь на отправку. True - удалось, False - не удалось
     */
    public static function videoNote(
        int $chatId,
        string $videoNote,
        string $type = 'video-note',
        int $priority = 2,
        array $options = [],
        ?string $sendAfter = null
    ): bool {
        $data = [
            'chat_id' => $chatId,
            'video_note' => $videoNote,
        ];
        
        $data = array_merge($data, $options);
        
        return self::push($chatId, 'sendVideoNote', $data, $type, $priority, $sendAfter);
    }
    
    /**
     * Метод добавляет в очередь произвольный запрос в Телеграм из документации
     * https://core.telegram.org/bots/api#available-methods
     *
     * @param string      $method    Метод отправки запроса
     * @param array       $data      Данные запроса
     * @param int|null    $chatId    Id чата
     * @param string      $type      Тип сообщения. Возможные значения: 'push', 'message'
     * @param int         $priority  Приоритет сообщения. 2 - самый низкий, 0 - самый высокий. По-умолчанию 2
     * @param string|null $sendAfter Дата и время, после которой отправляется запрос
     *
     * @return bool Удалось ли добавить в очередь на отправку. True - удалось, False - не удалось
     */
    public static function custom(
        string $method,
        array $data,
        ?int $chatId = null,
        string $type = 'push',
        int $priority = 2,
        ?string $sendAfter = null
    ): bool {
        return self::push($chatId, $method, $data, $type, $priority, $sendAfter);
    }
    
    /**
     * @param int|null    $chatId    ID чата
     * @param string      $method    Метод пуша
     * @param array       $data      Данные для пуша
     * @param string      $type      Тип пуша для различения отдельных пушей и рассылок
     * @param int         $priority  Приоритет сообщения. 2 - самый низкий, 0 - самый высокий. По-умолчанию 2
     * @param string|null $sendAfter Дата и время, после которой пуш будет выполнен
     *
     * @return bool True, если пуш прошел успешно, иначе False
     */
    private static function push(
        ?int $chatId,
        string $method,
        array $data,
        string $type = 'push',
        int $priority = 2,
        ?string $sendAfter = null
    ): bool {
        try {
            $redis = RedisHelper::getInstance();
            $db = Database::getInstance();
            
            if ($redis !== null && $db !== null) {
                // Данные для вставки в БД
                $insertData = [
                    'user_id' => $chatId,
                    'method' => $method,
                    'data' => json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                    'type' => $type,
                    'priority' => $priority,
                ];
                
                $useSchedule = false; // Использовать расписание
                
                // Если указано время пуша
                if (!empty($sendAfter)) {
                    $sendAfterTime = strtotime($sendAfter);
                    $delayTime = $sendAfterTime - time(); // Разница времени пуша с текущим временем
                    
                    if ($delayTime > 3600) {
                        // Если разница больше часа, то используем расписание в БД
                        $useSchedule = true;
                    }
                }
                
                // Если используется расписание
                if ($useSchedule === true) {
                    /*$sql = "INSERT INTO `schedule` (`handle_at`, `type`, `handler`, `data`, `active`) VALUES
                    (:handle_at, :type, :handler, :data, :active)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        'handle_at' => $sendAfter,
                        'type' => $type,
                        'handler' => 'TelegramPush',
                        'data' => json_encode($insertData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                        'active' => 1
                    ]);*/
                } else {
                    try {
                        $stmt = $db->prepare(
                            "INSERT INTO `telegram_messages` (`user_id`, `method`, `type`, `data`, `priority`) VALUES (:user_id, :method, :type, :data, :priority)"
                        );
                        $stmt->execute($insertData);
                        
                        // ID записанного сообщения
                        $id = $db->lastInsertId();
                    } catch (Throwable $e) {
                        Logger::error(
                            "Не удалось добавить в очередь пуш в Телеграм. {$e->getMessage()}.",
                            ['exception' => $e]
                        );
                        return false;
                    }
                    
                    $messageKey = RedisHelper::REDIS_MESSAGE_KEY . ':' . $id; // Ключ сообщения
                    $queueKey = RedisHelper::REDIS_MESSAGES_QUEUE_KEY . ':' . $priority; // Ключ очереди сообщений
                    
                    // Добавляем сообщение в Redis
                    $addMessageResult = $redis->set($messageKey, json_encode($insertData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
                    
                    if (empty($addMessageResult)) {
                        $errorMessage = "Не удалось добавить сообщение в Redis.";
                        
                        $stmt = $db->prepare(
                            "UPDATE `telegram_messages` SET `status` = :status, `error` = :error WHERE `id` = :id"
                        );
                        $stmt->execute(['status' => 'error', 'error' => $errorMessage, 'id' => $id]);
                        
                        throw new RuntimeException($errorMessage);
                    }
                    
                    // Добавляем сообщение в очередь с учетом приоритета
                    $messageData = [
                        'id' => $id,
                        'send_after' => $sendAfter ? strtotime($sendAfter) : null
                    ];
                    
                    $addQueueResult = $redis->rPush($queueKey, json_encode($messageData));
                    
                    if ($addQueueResult === false) {
                        $redis->del($messageKey);
                        
                        $errorMessage = "Не удалось добавить сообщение в очередь в Redis.";
                        
                        $stmt = $db->prepare(
                            "UPDATE `telegram_messages` SET `status` = :status, `error` = :error WHERE `id` = :id"
                        );
                        $stmt->execute(['status' => 'error', 'error' => $errorMessage, 'id' => $id]);
                        
                        throw new RuntimeException($errorMessage);
                    }
                }
                
                return true;
            }
        } catch (Throwable $e) {
            Logger::error(
                "Не удалось добавить в очередь пуш в Телеграм.",
                ['exception' => $e]
            );
        }
        
        return false;
    }
}