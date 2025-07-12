<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Query\FindSubscription;

use App\Shared\Application\Query\Query;

readonly class FindSubscriptionQuery extends Query
{
    public function __construct(public string $subscriptionId, public string $userId)
    {
    }
}
