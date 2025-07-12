<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\CreateChannel;

use App\Shared\Application\Command\CommandInterface;

final readonly class CreateChannelCommand implements CommandInterface
{
    public function __construct(
        public string $ownerId,
        public string $type,
        public array $data,
    ) {
    }
}
