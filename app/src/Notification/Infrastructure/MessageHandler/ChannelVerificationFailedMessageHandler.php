<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\MessageHandler;

use App\Notification\Domain\Message\ChannelVerificationFailedMessage;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Notification\Domain\Service\NotificationService;
use App\Shared\Application\Message\MessageHandlerInterface;

readonly class ChannelVerificationFailedMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private NotificationService $notificationService,
    ) {
    }

    public function __invoke(ChannelVerificationFailedMessage $message)
    {
        $channel = $this->channelRepository->findBySecret($message->reason);
        if ($channel){
            $this->notificationService->sendMessage($channel, $message->reason);
        }
    }

}
