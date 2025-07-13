<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate;

use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Event\SubscriptionCreatedEvent;
use App\Shared\Domain\Aggregate\Aggregate;
use App\Shared\Infrastructure\Exception\AppException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

class Subscription extends Aggregate
{
    /**
     * @var Collection<EventType>
     */
    public Collection $subscriptionEvents {
        get {
            return $this->subscriptionEvents;
        }
    }
    /**
     * @var Collection<Channel>
     */
    public Collection $channels {
        get {
            return $this->channels;
        }
    }

    /**
     * @var Collection<PhoneNumber>
     */
    public Collection $phoneNumbers {
        get {
            return $this->phoneNumbers;
        }
    }

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

    /**
     * @throws AppException
     */
    public function addChannel(Channel $channel): void
    {
        //todo пока так
        if (!$this->channels->contains($channel)) {
            if ($this->isOwnedBy($channel->getOwnerId())) {
                $this->channels->add($channel);
            } else {
                throw new AppException('Channel cannot be added.');
            }
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
        if (array_any($this->channels->toArray(), fn($channel) => !$channel->isVerified())) {
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isOwnedBy(string $userId): bool
    {
        return $this->subscriberId === $userId;
    }
}
