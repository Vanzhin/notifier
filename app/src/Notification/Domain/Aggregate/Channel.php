<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate;

use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Event\ChannelVerifiedEvent;
use App\Shared\Domain\Aggregate\Aggregate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SensitiveParameter;
use Symfony\Component\Uid\Uuid;

class Channel extends Aggregate implements ChannelInterface
{
    private ?string $secret = null;

    public bool $isVerified {
        get {
            return $this->isVerified;
        }
    }
    /**
     * @var Collection<Subscription>
     */
    private Collection $subscriptions;

    public function __construct(
        private readonly Uuid $id,
        private readonly string $ownerId,
        private array $data,
        private readonly ChannelType $type,
    ) {
        $this->isVerified = false;
        $this->subscriptions = new ArrayCollection();
    }

    public function verify(string $verificationValue): bool
    {
        if ($this->secret !== $verificationValue) {
            return false;
        }
        $this->isVerified = true;
        $this->raise(new ChannelVerifiedEvent($this->getId()->toString()));

        return true;
    }

    public function getType(): ChannelType
    {
        return $this->type;
    }

    public function getVerificationData(): array
    {
        // TODO: Implement getVerificationData() method.
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setSecret(#[SensitiveParameter] string $secret): void
    {
        $this->secret = $secret;
    }

    public function addSubscription(Subscription $subscription): void
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
        }
    }

    public function removeSubscription(Subscription $subscription): void
    {
        $this->subscriptions->removeElement($subscription);
    }

    public function getOwnerId(): string
    {
        return $this->ownerId;
    }

}
