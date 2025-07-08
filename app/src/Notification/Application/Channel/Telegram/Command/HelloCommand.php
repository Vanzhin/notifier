<?php

declare(strict_types=1);

namespace App\Notification\Application\Channel\Telegram\Command;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;

class HelloCommand extends UserCommand
{
    protected $name = 'hello';
    protected $description = 'Hello command';
    protected $usage = '/hello';
    protected $version = '1.0';

    public function execute(): ServerResponse
    {
        // Получаем данные сообщения
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();
        $first_name = $message->getFrom()->getFirstName();

        // Основное приветственное сообщение
        $text = "👋 Привет, {$first_name}!\n\n";
        $text .= "Я бот для управления уведомлениями. это хеллоу!!!!!!!\n";
        $text .= "Вот что я могу:\n\n";
        $text .= "🔹 /subscribe - Подписаться на уведомления\n";
        $text .= "🔹 /unsubscribe - Отписаться от уведомлений\n";
        $text .= "🔹 /settings - Настройки уведомлений\n";
        $text .= "🔹 /help - Помощь по командам";

        return $this->replyToChat($text);
    }
}
