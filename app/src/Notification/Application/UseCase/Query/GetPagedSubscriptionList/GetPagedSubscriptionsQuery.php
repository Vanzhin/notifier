<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Query\GetPagedSubscriptionList;

use App\Notification\Domain\Repository\SubscriptionFilter;
use App\Shared\Application\Query\Query;

readonly class GetPagedSubscriptionsQuery extends Query
{
    public function __construct(public SubscriptionFilter $filter, public string $userId)
    {
    }
}
