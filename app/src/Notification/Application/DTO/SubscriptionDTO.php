<?php

declare(strict_types=1);

namespace App\Notification\Application\DTO;

class SubscriptionDTO
{
    public ?array $channels;

    public function __construct(
        public ?string $id,
        public ?string $subscriber_id,
        public ?array $phone_numbers,
        public ?array $events,
        public ?bool $is_active,
        public ?\DateTimeImmutable $created_at,

    ) {
    }
}
