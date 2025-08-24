<?php

declare(strict_types=1);

namespace App\Handlers\Telegram\Commands;

use App\Helpers\Logger;
use App\Helpers\Push;
use App\Helpers\MessageStorage;
use Exception;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;

class StartCommandHandler extends AbstractCommandHandler
{

    /**
     * @param Update $update
     *
     * @return void
     * @throws Exception
     */
    public function handle(Update $update): void
    {
        $message = $update->getMessage();
        $chatId = $message->getChat()->getId();
        $firstName = $message->getChat()->getFirstName();
        $lastName = $message->getChat()->getLastName();
        $username = $message->getChat()->getUsername();
        $languageCode = $message->getFrom()->getLanguageCode();
        $isPremium = $message->getFrom()->getIsPremium() ? 1 : 0;
        $isBot = $message->getFrom()->getIsBot();
        $isPrivateChat = $message->getChat()->isPrivateChat();
        $messageText = $message->getText() ?? '';

        // Проверяем, является ли пользователь ботом
        if ($isBot) {
            return;
        }

        // Проверяем, является ли чат приватным
        if (!$isPrivateChat) {
            return;
        }

        $invitedUserId = $this->checkReferralCode($messageText);

        if ($invitedUserId === null) {
            $utmString = $this->convertUtmStringToUrlFormat($messageText) ?? '';
        } else {
            $utmString = '';
        }

        // Проверяем, существует ли пользователь в базе данных
        $stmt = $this->db->prepare("SELECT `id` FROM telegram_users WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $chatId]);
        $userExists = $stmt->fetch();

        if (!$userExists) {
            // Создаем уникальный реферальный код
            $referralCode = uniqid('REF', true);

            $stmt = $this->db->prepare(
                "INSERT INTO telegram_users (user_id, username, first_name, last_name, language_code, utm, is_premium, referral_code, invited_user_id) VALUES (:user_id, :username, :first_name, :last_name, :language_code, :utm, :is_premium, :referral_code, :invited_user_id)"
            );
            $stmt->execute([
                'user_id' => $chatId,
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'language_code' => $languageCode,
                'utm' => $utmString,
                'is_premium' => $isPremium,
                'referral_code' => $referralCode,
                'invited_user_id' => $invitedUserId,
            ]);
        } else {
            $stmt = $this->db->prepare(
                "UPDATE telegram_users SET username = :username, first_name = :first_name, last_name = :last_name, language_code = :language_code, utm = :utm, is_premium = :is_premium, is_user_banned = 0 WHERE user_id = :user_id"
            );
            $stmt->execute([
                'user_id' => $chatId,
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'language_code' => $languageCode,
                'utm' => $utmString,
                'is_premium' => $isPremium,
            ]);

            Logger::info("Пользователь уже зарегистрирован: {$chatId}");
        }

        $appUrl = $_ENV['APP_URL'];

        // Создаем инлайн-клавиатуру
        $keyboard = new InlineKeyboard(
            [
                ['text' => '🆕 Новая воронка', 'callback_data' => 'new_flow'],
            ],
            [
                ['text' => 'ℹ️ О проекте', 'callback_data' => 'about'],
            ]
        );

        $caption = MessageStorage::read('start') ?? '';

        // Отправляем стартовое сообщение с кнопкой
        Push::text($chatId, $caption, 'start', 2, [
            'reply_markup' => $keyboard,
            'link_preview_options' => ['is_disabled' => true],
        ]);

        Request::setChatMenuButton([
            'chat_id' => $chatId,
            'menu_button' => json_encode([
                'type' => 'web_app',
                'text' => 'Играть',
                'web_app' => [
                    'url' => $_ENV['WEB_APP_URL'] . '/?' . $utmString
                ]
            ], JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * Преобразование строки UTM-параметров в URL-совместимый формат
     *
     * @param string $messageText Строка UTM-параметров
     *
     * @return string|null
     */
    private function convertUtmStringToUrlFormat(string $messageText): ?string
    {
        if (preg_match('/^\/start\s+(.+)/', $messageText, $matches)) {
            $utmString = $matches[1];

            // Разбиваем строку на пары ключ-значение по разделителю "___"
            $pairs = explode('___', $utmString);
            $urlParams = [];

            foreach ($pairs as $pair) {
                [$key, $value] = explode('--', $pair) + [null, null];
                if ($key && $value) {
                    $urlParams[] = urlencode($key) . '=' . urlencode($value);
                }
            }

            // Возвращаем строку в формате URL: utm_source=yandex&utm_medium=cpc
            return implode('&', $urlParams);
        }

        return null;
    }

    /**
     * Проверяет есть ли реферальный код и находит пользователя с этим кодом
     *
     * @param string $messageText Текст сообщения
     *
     * @return int|null id пользователя или null
     */
    public function checkReferralCode(string $messageText): ?int
    {
        if (preg_match('/^\/start\s+(.+)/', $messageText, $matches)) {
            $string = trim($matches[1]);

            if (str_contains($string, 'code___')) {
                $referralCode = str_replace('code___', '', $string);

                // Проверяем, существует ли реферальный код в базе данных
                $stmt = $this->db->prepare("SELECT `user_id` FROM telegram_users WHERE referral_code = :referral_code LIMIT 1");
                $stmt->execute(['referral_code' => $referralCode]);
                $result = $stmt->fetch();

                if ($result) {
                    // Если реферальный код существует, возвращаем id пользователя
                    return $result['user_id'];
                }
            }

        }

        return null; // Если реферальный код не найден
    }
}
