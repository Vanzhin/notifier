<?php

declare(strict_types=1);

namespace App\Notification\Application\DTO;

use App\Notification\Domain\Aggregate\Subscription;

readonly class SubscriptionDTOTransformer
{
    public function __construct(private ChannelDTOTransformer $channelDTOTransformer)
    {
    }

    public function fromEntity(Subscription $entity): SubscriptionDTO
    {
        $events = [];
        foreach ($entity->getSubscriptionEvents() as $event) {
            $events[] = $event->value;
        }

        return new SubscriptionDTO(
            $entity->getId()->toString(),
            $entity->getSubscriberId(),
            $entity->getPhoneNumber()->getValue(),
            $events,
            $this->channelDTOTransformer->fromEntityList($entity->getChannels()->toArray()),
            $entity->isActive(),
            $entity->getCreatedAt(),
        );
    }
}
