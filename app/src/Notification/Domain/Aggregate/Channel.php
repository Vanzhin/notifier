<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate;

use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Shared\Domain\Aggregate\Aggregate;
use Symfony\Component\Uid\Uuid;

final class Channel extends Aggregate implements ChannelInterface
{
    private bool $isVerified;

    public function __construct(
        private Uuid $id,
        private Subscription $subscription,
        private array $data,
        private ChannelType $type,
    ) {
        $this->isVerified = false;
    }

    public function verify(string $verificationValue): bool
    {
        // TODO: Implement verify() method.
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
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

}
