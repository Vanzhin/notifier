<?php

declare(strict_types=1);

namespace App\Notification\Domain\Service;

use App\Notification\Domain\Aggregate\Channel;

final class EmailMessageSender implements MessageSenderInterface
{

    public function send(Channel $channel, string $message): void
    {
        // TODO: Implement send() method.
    }
}
