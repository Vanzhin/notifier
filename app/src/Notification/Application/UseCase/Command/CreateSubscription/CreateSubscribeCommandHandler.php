<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\CreateSubscription;

use App\Notification\Domain\Aggregate\PhoneNumber;
use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Factory\SubscriptionFactoryInterface;
use App\Notification\Domain\Repository\PhoneRepositoryInterface;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Application\Command\CommandHandlerInterface;
use Symfony\Component\Uid\Uuid;

readonly class CreateSubscribeCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private SubscriptionFactoryInterface $subscriptionFactory,
        private PhoneRepositoryInterface $phoneRepository,
    ) {
    }

    /**
     * @return string UserId
     */
    public function __invoke(CreateSubscriptionCommand $createSubscriptionCommand): string
    {
        $subscription = $this->subscriptionFactory->create($createSubscriptionCommand->subscriberId);

        foreach ($createSubscriptionCommand->events as $event) {
            $subscription->addEvent(EventType::from($event));
        }

        foreach ($createSubscriptionCommand->phoneNumbers as $phoneNumber) {
            $subscription->addPhoneNumber($this->createPhoneIfNotExists($phoneNumber));
        }

        $this->subscriptionRepository->save($subscription);

        return $subscription->getId()->toString();
    }

    private function createPhoneIfNotExists(string $phone): PhoneNumber
    {
        $phone = new PhoneNumber(
            Uuid::v4(),
            $phone);

        $exist = $this->phoneRepository->findByPhone($phone->getPhone());

        if ($exist) {
            $phone = $exist;
        }

        return $phone;
    }
}
