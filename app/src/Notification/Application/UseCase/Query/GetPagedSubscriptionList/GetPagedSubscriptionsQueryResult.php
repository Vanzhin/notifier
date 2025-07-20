<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Query\GetPagedSubscriptionList;

use App\Shared\Domain\Repository\Pager;

readonly class GetPagedSubscriptionsQueryResult
{
    public function __construct(public array $subscriptions, public Pager $pager)
    {
    }
}
