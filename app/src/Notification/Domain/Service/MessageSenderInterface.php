<?php

declare(strict_types=1);

namespace App\Notification\Domain\Service;

use App\Notification\Domain\Aggregate\Channel;

interface MessageSenderInterface
{
    public function send(Channel $channel, string $message): void;
}
