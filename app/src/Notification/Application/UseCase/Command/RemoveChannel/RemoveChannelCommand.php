<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\RemoveChannel;

use App\Shared\Application\Command\CommandInterface;

final readonly class RemoveChannelCommand implements CommandInterface
{
    public function __construct(public string $channelId, public string $ownerId)
    {
    }
}
