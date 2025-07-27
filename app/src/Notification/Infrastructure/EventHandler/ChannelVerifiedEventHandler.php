<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\EventHandler;

use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Event\ChannelVerifiedEvent;
use App\Notification\Domain\Message\Notification\NotificationMessage;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Notification\Domain\Service\NotificationService;
use App\Shared\Application\Event\EventHandlerInterface;
use App\Shared\Infrastructure\Exception\AppException;

readonly class ChannelVerifiedEventHandler implements EventHandlerInterface
{
    public function __construct(
        private NotificationService $notificationService,
        private ChannelRepositoryInterface $channelRepository,
    ) {
    }

    public function __invoke(ChannelVerifiedEvent $event): void
    {
        $channel = $this->channelRepository->findById($event->channelId);
        if (null === $channel) {
            throw new AppException('The channel not found.');
        }

        $this->notificationService->sendMessage(
            $channel,
            new NotificationMessage(
                'Канал верифицирован',
                EventType::CHANNEL_VERIFICATION));
    }
}
