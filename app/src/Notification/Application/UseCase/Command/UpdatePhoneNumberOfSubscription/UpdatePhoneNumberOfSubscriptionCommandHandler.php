<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\UpdatePhoneNumberOfSubscription;

use App\Notification\Application\Service\AccessControl\SubscriptionAccessControl;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Notification\Domain\Service\PhoneNumberOrganizer;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Infrastructure\Exception\AppException;
use Webmozart\Assert\Assert;

readonly class UpdatePhoneNumberOfSubscriptionCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private PhoneNumberOrganizer $numberOrganizer,
        private SubscriptionAccessControl $subscriptionAccessControl
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(UpdatedPhoneNumberOfSubscriptionCommand $command): void
    {
        $subscription = $this->subscriptionRepository->findById($command->subscriptionId);
        Assert::notNull($subscription, 'Subscription not found');

        if (!$this->subscriptionAccessControl->canUpdateSubscription($subscription, $command->ownerId)) {
            throw new AppException('You are not allowed to do this action.', 403);
        };

        $subscription->phoneNumbers->clear();
        foreach ($command->phoneNumbers as $phoneNumber) {
            $subscription->addPhoneNumber($this->numberOrganizer->createPhoneIfNotExists($phoneNumber));
        }

        $this->subscriptionRepository->save($subscription);
    }
}
