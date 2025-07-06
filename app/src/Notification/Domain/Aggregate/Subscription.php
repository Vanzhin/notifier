<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate;

use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Aggregate\ValueObject\PhoneNumber;
use App\Shared\Domain\Aggregate\Aggregate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

final  class Subscription extends Aggregate
{
    /**
     * @var Collection<EventType>
     */
    private Collection $subscriptionEvents;
    /**
     * @var Collection<Channel>
     */
    private Collection $channels;

    private \DateTimeImmutable $createdAt;

    public function __construct(
        private readonly Uuid $id,
        private readonly string $subscriberId,
        private readonly PhoneNumber $phoneNumber,
    ) {
        $this->subscriptionEvents = new ArrayCollection();
        $this->channels = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSubscriptionEvents(): ArrayCollection
    {
        return $this->subscriptionEvents;
    }

    public function getSubscriberId(): string
    {
        return $this->subscriberId;
    }

    public function getPhoneNumber(): PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function addEvent(EventType $eventType): void
    {
        if (!$this->subscriptionEvents->contains($eventType)) {
            $this->subscriptionEvents->add($eventType);
        }
    }

    public function removeEvent(EventType $eventType): void
    {
        if ($this->subscriptionEvents->contains($eventType)) {
            $this->subscriptionEvents->removeElement($eventType);
        }
    }

    public function addChannel(Channel $channel): void
    {
        if (!$this->channels->contains($channel)) {
            $this->channels->add($channel);
        }
    }

    public function removeChannel(Channel $channel): void
    {
        if ($this->channels->contains($channel)) {
            $this->channels->removeElement($channel);
        }
    }

    public function isActive(): bool
    {
        foreach ($this->channels as $channel) {
            if (!$channel->isVerified()) {
                return false;
            }
        }

        return !empty($this->channels);
    }

    public function getChannelVerificationData(ChannelType $channelType): ?array
    {
        return $this->channels->findFirst(function (Channel $item) use ($channelType) {
            if ($item->getType() === $channelType) {
                return $item->getVerificationData();
            }
            return null;
        });
    }

    public function getChannels(): ArrayCollection
    {
        return $this->channels;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
