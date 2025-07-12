<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Query\FindSubscription;

use App\Notification\Application\DTO\SubscriptionDTOTransformer;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Application\Query\QueryHandlerInterface;
use App\Notification\Application\Service\AccessControl\SubscriptionAccessControl;
use App\Shared\Infrastructure\Exception\AppException;

readonly class FindSubscriptionQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private SubscriptionDTOTransformer $subscriptionDTOTransformer,
        private SubscriptionAccessControl $subscriptionAccessControl,
    ) {
    }

    public function __invoke(FindSubscriptionQuery $query): FindSubscriptionQueryResult
    {
        $subscription = $this->subscriptionRepository->findById($query->subscriptionId);
        if (!$subscription) {
            return new FindSubscriptionQueryResult(null);
        }
        if (!$this->subscriptionAccessControl->canViewSubscription($subscription, $query->userId)) {
            throw new AppException('Subscription is not allowed to view.', 403);
        }

        $subscriptionDTO = $this->subscriptionDTOTransformer->fromEntity($subscription);

        return new FindSubscriptionQueryResult($subscriptionDTO);
    }
}
