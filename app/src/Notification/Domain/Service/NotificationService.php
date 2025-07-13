<?php

declare(strict_types=1);

namespace App\Notification\Domain\Service;

use App\Notification\Domain\Aggregate\Channel;

final readonly class NotificationService
{
    public function __construct(private MessageSenderFactory $senderFactory)
    {
    }

    public function sendMessage(Channel $channel, string $message): void
    {
        if (!$channel->isVerified()) {
            throw new \RuntimeException('Channel is not verified');
        }

        $sender = $this->senderFactory->getSender($channel);

        $sender->send($channel, $message);
    }

}
