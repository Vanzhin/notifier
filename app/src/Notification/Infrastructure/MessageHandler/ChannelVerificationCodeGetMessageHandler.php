<?php

declare(strict_types=1);


namespace App\Notification\Infrastructure\MessageHandler;

use App\Notification\Domain\Message\ChannelVerificationCodeGetMessage;
use App\Notification\Domain\Message\ChannelVerificationFailedMessage;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Notification\Domain\Service\ChannelVerifierInterface;
use App\Shared\Application\Message\MessageBusInterface;
use App\Shared\Application\Message\MessageHandlerInterface;
use App\Shared\Infrastructure\Exception\AppException;

readonly class ChannelVerificationCodeGetMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private ChannelVerifierInterface $channelVerifier,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(ChannelVerificationCodeGetMessage $message)
    {
        try {
            $channel = null;
            $channel = $this->channelRepository->findById($message->channelId);
            if ($channel){
                if ($channel->isVerified()) {
                    throw new AppException('The channel is already verified.');
                }
            }
            $this->channelVerifier->verify($channel, $message->secret);
        } catch (AppException $e) {
            $this->messageBus->executeMessages(new ChannelVerificationFailedMessage(
                reason: $e->getMessage(),
                channelId: $channel?->getId()->toString()));
        } catch (\Throwable $exception) {
            $this->messageBus->executeMessages(new ChannelVerificationFailedMessage(
                reason: 'Application error',
                channelId: $channel?->getId()->toString()));
        }
    }

}
