<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\InitiateChannelVerification;

use App\Shared\Application\Command\CommandInterface;

final readonly class InitiateChannelVerificationCommand implements CommandInterface
{
    public function __construct(public string $channelId, public string $userId)
    {
    }
}
