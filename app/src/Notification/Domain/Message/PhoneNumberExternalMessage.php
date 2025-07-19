<?php

declare(strict_types=1);

namespace App\Notification\Domain\Message;

use App\Shared\Domain\Message\MessageInterface;

readonly class PhoneNumberExternalMessage implements MessageInterface, \JsonSerializable
{
    public function __construct(private string $phone_number, private string $event_type)
    {
    }

    public function getPhoneNumber(): string
    {
        return $this->phone_number;
    }

    public function getEventType(): string
    {
        return $this->event_type;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
