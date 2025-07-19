<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\MessageHandler;

use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Message\PhoneNumberExternalMessage;
use App\Notification\Domain\Repository\SubscriptionFilter;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Notification\Domain\Service\NotificationService;
use App\Shared\Application\Message\MessageHandlerInterface;
use App\Shared\Domain\Repository\Pager;
use App\Shared\Domain\Repository\UnitOfWorkInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class ExternalMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private LoggerInterface $notifierLogger,
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private UnitOfWorkInterface $unitOfWork,
        private NotificationService $notificationService,
    ) {
    }

    public function __invoke(PhoneNumberExternalMessage $message): void
    {
        try {
            $filter = new SubscriptionFilter(new Pager(1, 100));
            $filter->addEvent($message->getEventType());
            $filter->addPhoneNumber($message->getPhoneNumber());

            do {
                $result = $this->subscriptionRepository->findByFilter($filter);
                $subscriptions = $result->items;

                if (empty($subscriptions)) {
                    break;
                }

                /** @var Subscription $subscription */
                foreach ($subscriptions as $subscription) {
                    foreach ($subscription->channels as $channel) {
                        //todo передавать не стрингу а объект, формировать красиво в сендере
                        $this->notificationService->sendMessage($channel,
                            $message->getPhoneNumber() . '->' . $message->getEventType());
                    }
                }

                $filter->pager->page = $filter->pager->page + 1;
                $this->unitOfWork->clear();
            } while (true);

            $this->notifierLogger->error(json_encode($message->jsonSerialize()));
        } catch (\Exception $exception) {
            $this->notifierLogger->error($exception->getMessage());
        }
    }

}
