<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\RemoveSubscription;

use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Application\Command\CommandHandlerInterface;
use Webmozart\Assert\Assert;

readonly class RemoveSubscriptionCommandHandler implements CommandHandlerInterface
{
    public function __construct(private SubscriptionRepositoryInterface $subscriptionRepository)
    {
    }

    public function __invoke(RemoveSubscriptionCommand $command): void
    {
        $subscription = $this->subscriptionRepository->findById($command->subscriptionId);
        Assert::notNull(
            value: $subscription,
            message: sprintf('Subscription with id "%s" not found.',
                $command->subscriptionId));

        $this->subscriptionRepository->remove($subscription);
    }
}
