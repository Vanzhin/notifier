<?php

declare(strict_types=1);

namespace App\Notification\Application\DTO;

class SubscriptionDTO
{
    public ?array $channels = null;

    public function __construct(
        public ?string $id = null,
        public ?string $subscriber_id = null,
        public ?array $phone_numbers = null,
        public ?array $events = null,
        public ?bool $is_active = null,
        public ?\DateTimeImmutable $created_at = null,
    ) {
    }
}
