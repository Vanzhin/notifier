<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Factory;

use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Aggregate\ValueObject\PhoneNumber;
use App\Notification\Domain\Factory\SubscriptionFactoryInterface;
use Symfony\Component\Uid\Uuid;

class SubscriptionFactory implements SubscriptionFactoryInterface
{
    public function create(string $subscriberId, string $phoneNumber, string ...$events): Subscription
    {
        $subscription = new Subscription(
            Uuid::v4(),
            $subscriberId,
            new PhoneNumber($phoneNumber),
        );
        foreach ($events as $event) {
            $subscription->addEvent(EventType::from($event));
        }

        return $subscription;
    }
}
