<?php

declare(strict_types=1);

namespace App\Notification\Domain\Event;

use App\Shared\Domain\Event\EventInterface;

class SubscriptionCreatedEvent implements EventInterface
{

    public function __construct(public string $subscriptionId)
    {
    }
}
