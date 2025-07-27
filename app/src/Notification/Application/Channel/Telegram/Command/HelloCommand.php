<?php

declare(strict_types=1);

namespace App\Notification\Application\Channel\Telegram\Command;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Psr\Log\LoggerInterface;

class HelloCommand extends UserCommand
{
    protected $name = 'hello';
    protected $description = 'Hello command';
    protected $usage = '/hello';
    protected $version = '1.0';

    public function execute(): ServerResponse
    {
        $this->initConfig();
        // Получаем данные сообщения
        $message = $this->getMessage();
        $first_name = $message->getFrom()->getFirstName();

        // Основное приветственное сообщение
        $text = "👋 Привет, {$first_name}!\n\n";
        $text .= "Я бот для управления уведомлениями.\n";
        $text .= "Вот что я могу:\n\n";
        $text .= "🔹 /hello - Приветствие\n";
        $text .= "🔹 /get_channel_subscriptions - Посмотреть подписки канала\n";
//        $text .= "🔹 /unsubscribe - Отписаться от уведомлений\n";

        return $this->replyToChat($text);
    }
}
