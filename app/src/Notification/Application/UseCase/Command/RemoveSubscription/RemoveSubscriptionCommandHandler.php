<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\RemoveSubscription;

use App\Notification\Application\Service\AccessControl\SubscriptionAccessControl;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Infrastructure\Exception\AppException;
use Webmozart\Assert\Assert;

readonly class RemoveSubscriptionCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private SubscriptionAccessControl $subscriptionAccessControl
    ) {
    }

    public function __invoke(RemoveSubscriptionCommand $command): void
    {
        $subscription = $this->subscriptionRepository->findById($command->subscriptionId);
        Assert::notNull(
            value: $subscription,
            message: sprintf('Subscription with id "%s" not found.',
                $command->subscriptionId));

        if (!$this->subscriptionAccessControl->canViewSubscription($subscription, $command->userId)) {
            throw new AppException('Not allowed to view subscription', 403);
        }

        $this->subscriptionRepository->remove($subscription);
    }
}
