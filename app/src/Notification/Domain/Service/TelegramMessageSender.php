<?php

declare(strict_types=1);

namespace App\Notification\Domain\Service;

use App\Notification\Application\Channel\Telegram\Service\TelegramBotService;
use App\Notification\Domain\Aggregate\Channel;

final readonly class TelegramMessageSender implements MessageSenderInterface
{
    public function __construct(private TelegramBotService $telegramBotService)
    {
    }

    /**
     * @throws \Exception
     */
    public function send(Channel $channel, string $message): void
    {
        $this->telegramBotService->sendMessage((int)$channel->getChannel(), $message);
    }
}
