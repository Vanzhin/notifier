<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\MessageHandler;

use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Aggregate\ValueObject\PhoneNumber;
use App\Notification\Domain\Message\Notification\NotificationMessage;
use App\Notification\Domain\Message\PhoneNumberExternalMessage;
use App\Notification\Domain\Repository\SubscriptionFilter;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Notification\Domain\Service\NotificationService;
use App\Shared\Application\Message\MessageHandlerInterface;
use App\Shared\Domain\Repository\Pager;
use App\Shared\Domain\Repository\UnitOfWorkInterface;
use App\Shared\Infrastructure\Exception\AppException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class ExternalMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private UnitOfWorkInterface $unitOfWork,
        private NotificationService $notificationService,
        private LoggerInterface $notifierLogger,
    ) {
    }

    /**
     * @throws AppException
     */
    public function __invoke(PhoneNumberExternalMessage $message): void
    {
        $filter = new SubscriptionFilter(new Pager(1, 100));
        $filter->addEvent($message->getEventType());
        $filter->addPhoneNumber($message->getPhoneNumber());

        while (true) {
            $result = $this->subscriptionRepository->findByFilter($filter);
            $subscriptions = $result->items;

            if (empty($subscriptions)) {
                break;
            }

            /** @var Subscription $subscription */
            foreach ($subscriptions as $subscription) {
                foreach ($subscription->channels as $channel) {
                    $this->notificationService->sendMessage($channel,
                        new NotificationMessage(
                            'New phone number event',
                            EventType::from($message->getEventType()),
                            new PhoneNumber($message->getPhoneNumber()),
                            $message->getExtra()
                        )
                    );
                }
            }

            $filter->pager->page = $filter->pager->page + 1;
            $this->unitOfWork->clear();
        }
    }
}
