<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\CreateSubscription;

use App\Shared\Application\Command\CommandInterface;

final readonly class CreateSubscriptionCommand implements CommandInterface
{
    public function __construct(
        public string $subscriberId,
        public string $phoneNumber,
        public array $events,
        public array $channels
    ) {
    }
}
