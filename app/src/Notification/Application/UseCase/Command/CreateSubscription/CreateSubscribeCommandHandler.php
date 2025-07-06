<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\CreateSubscription;

use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Aggregate\EmailChannel;
use App\Notification\Domain\Aggregate\TelegramChannel;
use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Factory\SubscriptionFactoryInterface;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Application\Command\CommandHandlerInterface;
use Symfony\Component\Uid\Uuid;

readonly class CreateSubscribeCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private SubscriptionFactoryInterface $subscriptionFactory
    ) {
    }

    /**
     * @return string UserId
     */
    public function __invoke(CreateSubscriptionCommand $createSubscriptionCommand): string
    {
        $subscription = $this->subscriptionFactory->create(
            $createSubscriptionCommand->subscriberId,
            $createSubscriptionCommand->phoneNumber,
            ...$createSubscriptionCommand->events
        );

        foreach ($createSubscriptionCommand->channels as $type => $params) {
            $channel = new Channel(
                Uuid::v4(),
                $subscription,
                $params, ChannelType::from($type)
            );

            $subscription->addChannel($channel);
//            $this->channelVerifier->initiateVerification($channel);
        }

        $this->subscriptionRepository->save($subscription);

        return $subscription->getId()->toString();
    }
}
