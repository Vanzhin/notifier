<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Query\GetPagedSubscriptionList;

use App\Notification\Application\DTO\SubscriptionDTOTransformer;
use App\Notification\Application\Service\AccessControl\SubscriptionAccessControl;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Application\Query\QueryHandlerInterface;
use App\Shared\Domain\Repository\Pager;

readonly class GetPagedSubscriptionsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private SubscriptionAccessControl $subscriptionAccessControl,
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private SubscriptionDTOTransformer $subscriptionDTOTransformer,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(GetPagedSubscriptionsQuery $query): GetPagedSubscriptionsQueryResult
    {
        $filter = $query->filter;
        $filter->setOwnerId($query->userId);
        $subscriptions = [];

        $paginator = $this->subscriptionRepository->findByFilter($filter);
        foreach ($paginator->items as $subscription) {
            if ($this->subscriptionAccessControl->canViewSubscription($subscription, $query->userId)) {
                $subscriptions[] = $this->subscriptionDTOTransformer->fromEntity($subscription);
            }
        }
        $pager = new Pager(
            $query->filter->pager->page,
            $query->filter->pager->per_page,
            $paginator->total
        );

        return new GetPagedSubscriptionsQueryResult($subscriptions, $pager);
    }
}
