<?php

declare(strict_types=1);

namespace App\Notification\Domain\Repository;

use App\Notification\Domain\Aggregate\Subscription;
use App\Shared\Domain\Repository\PaginationResult;

interface SubscriptionRepositoryInterface
{
    public function save(Subscription $subscription): void;

    public function findById(string $id): ?Subscription;

    public function findByFilter(SubscriptionFilter $filter): PaginationResult;


    public function remove(Subscription $subscription): void;

}
