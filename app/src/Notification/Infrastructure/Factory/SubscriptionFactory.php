<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Factory;

use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Factory\SubscriptionFactoryInterface;
use Symfony\Component\Uid\Uuid;

class SubscriptionFactory implements SubscriptionFactoryInterface
{
    public function create(string $subscriberId): Subscription
    {
        return new Subscription(
            Uuid::v4(),
            $subscriberId,
        );
    }
}
