<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\EventHandler;

use App\Notification\Domain\Event\SubscriptionCreatedEvent;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Application\Event\EventHandlerInterface;
use App\Shared\Domain\Service\AssertService;
use App\Shared\Infrastructure\Exception\AppException;

readonly class SubscriptionCreatedEventHandler implements EventHandlerInterface
{
    public function __construct(
        private SubscriptionRepositoryInterface $repository,
    ) {
    }

    public function __invoke(SubscriptionCreatedEvent $event): void
    {
        $subscription = $this->repository->findById($event->subscriptionId);
        AssertService::notNull($subscription, 'Подписка не найдена.');

        // проверяем, что объект имеет хотя бы по одному объекту в соответствующих коллекциях
        if ($subscription->getSubscriptionEvents()->isEmpty()) {
            throw new AppException('Необходимо внести хотя бы одно событие для подписки.');
        }

        if ($subscription->getPhoneNumbers()->isEmpty()) {
            throw new AppException('Необходимо внести хотя бы один телефонный номер для подписки.');
        }

        if ($subscription->getChannels()->isEmpty()) {
            throw new AppException('Необходимо внести хотя бы один канал номер для подписки.');
        }
    }

}
