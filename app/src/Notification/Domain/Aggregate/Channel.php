<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate;

use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Event\ChannelVerifiedEvent;
use App\Shared\Domain\Aggregate\Aggregate;
use App\Shared\Infrastructure\Exception\AppException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SensitiveParameter;
use Symfony\Component\Uid\Uuid;

class Channel extends Aggregate implements ChannelInterface
{
    private ?string $secret = null;

    private bool $isVerified;
    /**
     * @var Collection<Subscription>
     */
    private Collection $subscriptions;

    public function __construct(
        private readonly Uuid $id,
        private readonly string $ownerId,
        private array $data,
        private readonly ChannelType $type,
        private ?string $channel = null,
    ) {
        $this->subscriptions = new ArrayCollection();
        $this->isVerified = false;
    }

    public function verify(string $verificationValue): bool
    {
        if ($this->secret !== $verificationValue) {
            return false;
        }
        if ($this->channel === null) {
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

    public function getData(): array
    {
        return $this->data;
    }

    public function setSecret(#[SensitiveParameter] string $secret): void
    {
        if ($this->isVerified) {
            throw new AppException('Нельзя установить секрет для верифицированного канала.');
        }
        $this->secret = $secret;
    }

    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
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

    public function isOwnedBy(string $userId): bool
    {
        return $this->ownerId === $userId;
    }

    public function getChannel(): ?string
    {
        return $this->channel;
    }

    /**
     * @throws AppException
     */
    public function setChannel(string $channel): void
    {
        if ($this->isVerified) {
            throw new AppException('Channel is already verified, cannot change channel.');
        }
        if ($this->channel) {
            throw new AppException('Channel is already has channel.');
        }
        $this->channel = $channel;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    private function checkChannel(string $channel): bool
    {
        return match ($this->type) {
            ChannelType::TELEGRAM => ctype_digit($channel),
            ChannelType::EMAIL => (bool)filter_var($channel, FILTER_VALIDATE_EMAIL),
            default => false,
        };
    }

}
