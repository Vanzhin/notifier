<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate;

use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use Doctrine\Common\Collections\Collection;

interface ChannelInterface
{
    public function verify(string $verificationValue): bool;

    public function getType(): ChannelType;

    public function getVerificationData(): array;

    public function getSubscriptions(): Collection;

    public function getData(): array;
}
