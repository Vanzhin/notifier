<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\UpdatePhoneNumberOfSubscription;

use App\Shared\Application\Command\CommandInterface;

final readonly class UpdatePhoneNumberOfSubscriptionCommand implements CommandInterface
{
    public array $phoneNumbers;

    public function __construct(
        public string $subscriptionId,
        public string $ownerId,
        string ...$phoneNumbers,
    ) {
        $this->phoneNumbers = $phoneNumbers;
    }
}
