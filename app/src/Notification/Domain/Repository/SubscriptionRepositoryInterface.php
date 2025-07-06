<?php

declare(strict_types=1);

namespace App\Notification\Domain\Repository;

use App\Notification\Domain\Aggregate\Subscription;

interface SubscriptionRepositoryInterface
{
    public function save(Subscription $subscription): void;

    public function findById(string $id): ?Subscription;

    public function remove(Subscription $subscription): void;

}
