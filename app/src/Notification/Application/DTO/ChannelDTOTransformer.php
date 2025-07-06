<?php

declare(strict_types=1);

namespace App\Notification\Application\DTO;

use App\Notification\Domain\Aggregate\Channel;

class ChannelDTOTransformer
{
    public function fromEntity(Channel $entity): ChannelDTO
    {
        return new ChannelDTO(
            $entity->getId()->toString(),
            $entity->getData(),
            $entity->getType()->value,
            $entity->isVerified(),
        );
    }

    public function fromEntityList(array $channels): array
    {
        $channelDTOs = [];
        foreach ($channels as $channel) {
            $channelDTOs[] = $this->fromEntity($channel);
        }

        return $channelDTOs;
    }
}
