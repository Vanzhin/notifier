<?php

declare(strict_types=1);

namespace App\Notification\Domain\Message\Notification;

readonly class NotificationMessage
{
    public function __construct(
        public string $phone_number,
        public string $event_type,
        public array $extra = []
    ) {
    }

}
