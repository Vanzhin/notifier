<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\DetachChannelFromSubscription;

use App\Shared\Application\Command\CommandInterface;

readonly class DetachChannelFromSubscriptionCommand implements CommandInterface
{
    public function __construct(public string $subscriptionId, public string $channelId, public string $userId)
    {
    }
}
