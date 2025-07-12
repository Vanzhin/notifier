<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\AddChannelToSubscription;

use App\Shared\Application\Command\CommandInterface;

readonly class AddChannelToSubscriptionCommand implements CommandInterface
{
    public function __construct(public string $subscriptionId, public string $channelId, public string $userId)
    {
    }
}
