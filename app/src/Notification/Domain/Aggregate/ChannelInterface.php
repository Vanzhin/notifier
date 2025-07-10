<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate;

use App\Notification\Domain\Aggregate\ValueObject\ChannelType;

interface ChannelInterface
{
    public function verify(string $verificationValue): bool;

    public bool $isVerified {
        get;
    }

    public function getType(): ChannelType;

    public function getVerificationData(): array;

    public function getSubscription(): Subscription;

    public function getData(): array;
}
