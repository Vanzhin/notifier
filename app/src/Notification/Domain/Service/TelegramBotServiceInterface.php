<?php

declare(strict_types=1);


namespace App\Notification\Domain\Service;

use App\Notification\Domain\Aggregate\ChannelInterface;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Telegram;

interface TelegramBotServiceInterface
{
    public function setWebhook(): ServerResponse;

    public function sendMessage(int $chatId, string $text, array $options = []): ServerResponse;

    public function handle(string $secret): void;

    public function supports(ChannelInterface $channel): bool;

    public function getClient(): Telegram;
}
