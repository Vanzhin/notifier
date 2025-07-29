<?php

declare(strict_types=1);

namespace App\Notification\Domain\Message\Notification;

use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Aggregate\ValueObject\PhoneNumber;

readonly class NotificationMessage
{
    public function __construct(
        public string $message,
        public EventType $event_type,
        public ?PhoneNumber $phone_number = null,
        public array $extra = [],
    ) {
    }
}
