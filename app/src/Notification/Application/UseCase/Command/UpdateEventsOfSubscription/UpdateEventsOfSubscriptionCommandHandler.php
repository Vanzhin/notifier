<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\UpdateEventsOfSubscription;

use App\Notification\Application\Service\AccessControl\SubscriptionAccessControl;
use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Infrastructure\Exception\AppException;
use Webmozart\Assert\Assert;

readonly class UpdateEventsOfSubscriptionCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private SubscriptionAccessControl $subscriptionAccessControl,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(UpdateEventsOfSubscriptionCommand $command): void
    {
        $subscription = $this->subscriptionRepository->findById($command->subscriptionId);
        Assert::notNull($subscription, 'Subscription not found');

        if (!$this->subscriptionAccessControl->canUpdateSubscription($subscription, $command->ownerId)) {
            throw new AppException('You are not allowed to do this action.', 403);
        }
        $subscription->setSubscriptionEvents();
        foreach ($command->events as $event) {
            $subscription->addEvent(EventType::from($event));
        }

        $this->subscriptionRepository->save($subscription);
    }
}
