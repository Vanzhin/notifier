<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\UpdateEventsOfSubscription;

use App\Shared\Application\Command\CommandInterface;

final readonly class UpdatedEventsOfSubscriptionCommand implements CommandInterface
{
    public array $events;

    public function __construct(
        public string $subscriptionId,
        public string $ownerId,
        string ...$events,
    ) {
        $this->events = $events;
    }
}
