<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\RemoveSubscription;

use App\Shared\Application\Command\CommandInterface;

readonly class RemoveSubscriptionCommand implements CommandInterface
{
    public function __construct(public string $subscriptionId)
    {
    }
}
