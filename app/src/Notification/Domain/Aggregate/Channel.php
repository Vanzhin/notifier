<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate;

use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Event\ChannelVerifiedEvent;
use App\Shared\Domain\Aggregate\Aggregate;
use Symfony\Component\Uid\Uuid;

class Channel extends Aggregate implements ChannelInterface
{
    private ?string $secret = null;

    public bool $isVerified {
        get {
            return $this->isVerified;
        }
    }

    public function __construct(
        private Uuid $id,
        private readonly Subscription $subscription,
        private array $data,
        private ChannelType $type,
    ) {
        $this->isVerified = false;
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

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

}
