<?php

declare(strict_types=1);

namespace App\Notification\Domain\Message;

use App\Shared\Domain\Message\MessageInterface;

class ChannelVerificationCodeGetMessage implements MessageInterface
{
    public function __construct(public string $channelId, public string $secret)
    {
    }
}
