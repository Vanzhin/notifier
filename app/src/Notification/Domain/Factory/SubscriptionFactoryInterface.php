<?php

declare(strict_types=1);

namespace App\Notification\Domain\Factory;

use App\Notification\Domain\Aggregate\Subscription;

interface SubscriptionFactoryInterface
{
    public function create(string $subscriberId): Subscription;
}
