<?php

declare(strict_types=1);

namespace App\Notification\Application\Channel\Telegram\Command;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;

class StartCommand extends UserCommand
{
    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';
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
        $text .= "Я бот для управления уведомлениями. это старт!!!!!!!!!!!\n";


        return $this->replyToChat($text);
    }
}
