<?php

declare(strict_types=1);

namespace App\Notification\Domain\Service;

use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Message\Notification\NotificationMessage;

interface MessageSenderInterface
{
    public function send(Channel $channel, NotificationMessage $message): void;
}
