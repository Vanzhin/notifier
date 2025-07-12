<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\AddChannelToSubscription;

use App\Notification\Application\Service\AccessControl\SubscriptionAccessControl;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Infrastructure\Exception\AppException;

readonly class AddChannelToSubscriptionCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private SubscriptionAccessControl $subscriptionAccessControl,
        private SubscriptionRepositoryInterface $subscriptionRepository,
    ) {
    }

    public function __invoke(AddChannelToSubscriptionCommand $command): void
    {
        $channel = $this->channelRepository->findById($command->channelId);
        $subscription = $this->subscriptionRepository->findById($command->subscriptionId);

        if (!$channel or !$subscription) {
            throw new AppException('Channel or subscription not found.');
        }

        if (!$this->subscriptionAccessControl->canAddChannelToSubscription(
            channel: $channel,
            subscription: $subscription,
            userId: $command->userId
        )) {
            throw new AppException('You are not allowed to do this action.', 403);
        }

        $subscription->addChannel($channel);
        $this->subscriptionRepository->save($subscription);
    }
}
