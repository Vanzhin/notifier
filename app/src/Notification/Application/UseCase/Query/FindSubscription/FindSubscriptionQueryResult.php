<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Query\FindSubscription;

use App\Notification\Application\DTO\SubscriptionDTO;

readonly class FindSubscriptionQueryResult
{
    public function __construct(public ?SubscriptionDTO $subscription)
    {
    }
}
