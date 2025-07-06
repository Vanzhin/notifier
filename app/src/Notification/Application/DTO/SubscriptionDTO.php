<?php

declare(strict_types=1);

namespace App\Notification\Application\DTO;

class SubscriptionDTO
{
    public function __construct(
        public string $id,
        public string $subscriber_id,
        public string $phone_number,
        public array $events,
        public array $channels,
        public bool $is_active,
        public \DateTimeImmutable $created_at,

    ) {
    }
}
