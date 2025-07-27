<?php

declare(strict_types=1);

namespace App\Notification\Domain\Service;

use App\Notification\Application\Channel\Telegram\Service\TelegramBotService;
use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Message\Notification\NotificationMessage;

readonly class TelegramMessageSender implements MessageSenderInterface
{
    public function __construct(
        private TelegramBotServiceInterface $telegramBotService,
        private NotificationMessageFormatterInterface $telegramMessageFormatter,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function send(Channel $channel, NotificationMessage $message): void
    {
        $text = $this->telegramMessageFormatter->format($message);
        $this->telegramBotService->sendMessage((int)$channel->getChannel(), $text);
    }
}
