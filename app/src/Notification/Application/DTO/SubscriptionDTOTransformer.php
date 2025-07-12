<?php

declare(strict_types=1);

namespace App\Notification\Application\DTO;

use App\Notification\Domain\Aggregate\Subscription;

readonly class SubscriptionDTOTransformer
{
    public function __construct(private ChannelDTOTransformer $channelDTOTransformer)
    {
    }

    public function fromEntity(Subscription $entity, bool $withChannels = true): SubscriptionDTO
    {
        $events = [];
        foreach ($entity->subscriptionEvents as $event) {
            $events[] = $event->value;
        }
        $phoneNumbers = [];
        foreach ($entity->phoneNumbers as $phone) {
            $phoneNumbers[] = $phone->getPhone();
        }

        return new SubscriptionDTO(
            $entity->getId()->toString(),
            $entity->getSubscriberId(),
            $phoneNumbers,
            $events,
            $withChannels ? $this->channelDTOTransformer->fromEntityList($entity->channels->toArray()) : null,
            $entity->isActive(),
            $entity->getCreatedAt(),
        );
    }
}
