<?php

declare(strict_types=1);

namespace App\Notification\Domain\Service;

use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Aggregate\ValueObject\ChannelType;

final readonly class MessageSenderFactory
{
    public function __construct(
        private TelegramMessageSender $telegramSender,
        private EmailMessageSender $emailSender,
    ) {
    }

    public function getSender(Channel $channel): MessageSenderInterface
    {
        return match ($channel->getType()) {
            ChannelType::TELEGRAM => $this->telegramSender,
            ChannelType::EMAIL => $this->emailSender,
            default => throw new \InvalidArgumentException('Unsupported channel type'),
        };
    }
}
