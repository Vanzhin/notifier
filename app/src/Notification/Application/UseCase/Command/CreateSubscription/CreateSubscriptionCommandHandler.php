<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\CreateSubscription;

use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Factory\SubscriptionFactoryInterface;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Notification\Domain\Service\PhoneNumberOrganizer;
use App\Shared\Application\Command\CommandHandlerInterface;

readonly class CreateSubscriptionCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private SubscriptionFactoryInterface $subscriptionFactory,
        private PhoneNumberOrganizer $numberOrganizer,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(CreateSubscriptionCommand $createSubscriptionCommand): string
    {
        $subscription = $this->subscriptionFactory->create($createSubscriptionCommand->subscriberId);

        foreach ($createSubscriptionCommand->events as $event) {
            $subscription->addEvent(EventType::from($event));
        }

        foreach ($createSubscriptionCommand->phoneNumbers as $phoneNumber) {
            $subscription->addPhoneNumber($this->numberOrganizer->createPhoneIfNotExists($phoneNumber));
        }

        $this->subscriptionRepository->save($subscription);

        return $subscription->getId()->toString();
    }
}
