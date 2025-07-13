<?php

declare(strict_types=1);

namespace App\Notification\Application\DTO;

use App\Notification\Domain\Aggregate\Channel;
use Doctrine\Common\Collections\Collection;

readonly class ChannelDTOTransformer
{

    public function fromEntity(Channel $entity, array $with = []): ChannelDTO
    {
        $channelDTO = new ChannelDTO(
            $entity->getId()->toString(),
            $entity->getData(),
            $entity->getType()->value,
            $entity->isVerified(),
        );
        foreach ($with as $relation) {
            if (property_exists($entity, $relation)) {
                match ($relation) {
                    'subscriptions' => $this->addSubscriptions($channelDTO, $entity->getSubscriptions()),
                };
            }
        }

        return $channelDTO;
    }

    public function fromEntityList(array $channels): array
    {
        $channelDTOs = [];
        foreach ($channels as $channel) {
            $channelDTOs[] = $this->fromEntity($channel);
        }

        return $channelDTOs;
    }

    private function addSubscriptions(ChannelDTO $channelDTO, Collection $subscriptions): void
    {
        $subscriptionDTOTransformer = new SubscriptionDTOTransformer($this);
        $subscriptionDTOs = [];
        foreach ($subscriptions as $subscription) {
            $subscriptionDTOs[] = $subscriptionDTOTransformer->fromEntity($subscription, withChannels: false);
        }
        $channelDTO->subscriptions = $subscriptionDTOs;
    }
}
