<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Query\FindSubscription;

use App\Notification\Application\DTO\SubscriptionDTOTransformer;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Application\Query\QueryHandlerInterface;

readonly class FindSubscriptionQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private SubscriptionDTOTransformer $subscriptionDTOTransformer
    ) {
    }

    public function __invoke(FindSubscriptionQuery $query): FindSubscriptionQueryResult
    {
        $subscription = $this->subscriptionRepository->findById($query->subscriptionId);
        if (!$subscription) {
            return new FindSubscriptionQueryResult(null);
        }

        $subscriptionDTO = $this->subscriptionDTOTransformer->fromEntity($subscription);

        return new FindSubscriptionQueryResult($subscriptionDTO);
    }
}
