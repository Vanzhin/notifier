<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate;

use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Event\SubscriptionCreatedEvent;
use App\Shared\Domain\Aggregate\Aggregate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

class Subscription extends Aggregate
{
    /**
     * @var Collection<EventType>
     */
    private Collection $subscriptionEvents;
    /**
     * @var Collection<Channel>
     */
    private Collection $channels;

    /**
     * @var Collection<PhoneNumber>
     */
    private Collection $phoneNumbers;

    private \DateTimeImmutable $createdAt;

    public function __construct(
        private readonly Uuid $id,
        private readonly string $subscriberId,
    ) {
        $this->subscriptionEvents = new ArrayCollection();
        $this->channels = new ArrayCollection();
        $this->phoneNumbers = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->raise(new SubscriptionCreatedEvent($this->getId()->toString()));
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSubscriptionEvents(): Collection
    {
        return $this->subscriptionEvents;
    }

    public function getSubscriberId(): string
    {
        return $this->subscriberId;
    }

    public function addEvent(EventType $eventType): void
    {
        if (!$this->subscriptionEvents->contains($eventType)) {
            $this->subscriptionEvents->add($eventType);
        }
    }

    public function removeEvent(EventType $eventType): void
    {
        $this->subscriptionEvents->removeElement($eventType);
    }

    public function addChannel(Channel $channel): void
    {
        if (!$this->channels->contains($channel)) {
            $this->channels->add($channel);
        }
    }

    public function removeChannel(Channel $channel): void
    {
        $this->channels->removeElement($channel);
    }

    public function addPhoneNumber(PhoneNumber $phoneNumber): void
    {
        $alreadyHasNumber = $this->phoneNumbers
            ->findFirst(function ($key, PhoneNumber $phone) use ($phoneNumber) {
                return $phone->getPhone() === $phoneNumber->getPhone();
            });

        if (!$alreadyHasNumber) {
            $this->phoneNumbers->add($phoneNumber);
        }
    }

    public function removePhoneNumber(PhoneNumber $phoneNumber): void
    {
        $this->channels->removeElement($phoneNumber);
    }

    public function isActive(): bool
    {
        if (array_any($this->channels->toArray(), fn($channel) => !$channel->isVerified)) {
            return false;
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

    public function getChannels(): Collection
    {
        return $this->channels;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPhoneNumbers(): Collection
    {
        return $this->phoneNumbers;
    }
}
