<?php

declare(strict_types=1);

namespace App\Notification\Application\DTO;

class ChannelDTO
{
    public ?array $subscriptions;

    public function __construct(
        public ?string $id,
        public ?array $data,
        public ?string $type,
        public ?bool $is_verified,
    ) {
    }
}
