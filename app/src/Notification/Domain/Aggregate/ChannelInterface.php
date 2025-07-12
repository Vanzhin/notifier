<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate;

use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use Doctrine\Common\Collections\Collection;

interface ChannelInterface
{
    public function verify(string $verificationValue): bool;

    public bool $isVerified {
        get;
    }

    public function getType(): ChannelType;

    public function getVerificationData(): array;

    public Collection $subscriptions {
        get;
    }

    public function getData(): array;
}
