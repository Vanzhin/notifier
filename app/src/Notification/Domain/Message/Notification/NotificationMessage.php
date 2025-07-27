<?php

declare(strict_types=1);

namespace App\Notification\Domain\Message\Notification;

use App\Notification\Domain\Aggregate\ValueObject\EventType;

readonly class NotificationMessage
{
    public function __construct(
        public string $message,
        public EventType $event_type,
        public ?string $phone_number = null,
        public array $extra = [],
    ) {
    }
}
