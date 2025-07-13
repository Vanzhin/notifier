<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\CreateChannel;

use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Shared\Application\Command\CommandHandlerInterface;
use Symfony\Component\Uid\Uuid;

readonly class CreateChannelCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
    ) {
    }

    /**
     * @return string UserId
     */
    public function __invoke(CreateChannelCommand $command): string
    {
        $channel = new Channel(
            Uuid::v4(),
            $command->ownerId,
            $command->data,
            ChannelType::from($command->type),
            $command->channel
        );
        $this->channelRepository->save($channel);

        return $channel->getId()->toString();
    }
}
