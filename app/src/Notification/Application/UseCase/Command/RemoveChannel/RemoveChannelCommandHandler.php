<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\RemoveChannel;

use App\Notification\Application\Service\AccessControl\ChannelAccessControl;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Infrastructure\Exception\AppException;
use Webmozart\Assert\Assert;

readonly class RemoveChannelCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ChannelRepositoryInterface $repository,
        private ChannelAccessControl $channelAccessControl
    ) {
    }

    public function __invoke(RemoveChannelCommand $command): void
    {
        $channel = $this->repository->findById($command->channelId);
        Assert::notNull(
            value: $channel,
            message: sprintf('Channel with id "%s" not found.',
                $command->channelId));
        if (!$this->channelAccessControl->canViewChannel($channel, $command->ownerId)) {
            throw new AppException('Channel is not allowed to view.', 403);
        }

        $this->repository->remove($channel);
    }
}
