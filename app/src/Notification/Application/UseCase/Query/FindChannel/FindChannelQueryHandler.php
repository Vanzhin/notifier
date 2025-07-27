<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Query\FindChannel;

use App\Notification\Application\DTO\ChannelDTOTransformer;
use App\Notification\Application\Service\AccessControl\ChannelAccessControl;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Shared\Application\Query\QueryHandlerInterface;
use App\Shared\Infrastructure\Exception\AppException;

readonly class FindChannelQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private ChannelRepositoryInterface $channelRepository,
        private ChannelDTOTransformer $channelDTOTransformer,
        private ChannelAccessControl $channelAccessControl,
    ) {
    }

    public function __invoke(FindChannelQuery $query): FindChannelQueryResult
    {
        $channel = $this->channelRepository->findById($query->channelId);
        if (!$channel) {
            return new FindChannelQueryResult(null);
        }
        if (!$this->channelAccessControl->canViewChannel($channel, $query->ownerId)) {
            throw new AppException('Channel is not allowed to view.', 403);
        }

        $channelDTO = $this->channelDTOTransformer->fromEntity(entity: $channel, with: ['subscriptions']);

        return new FindChannelQueryResult($channelDTO);
    }
}
