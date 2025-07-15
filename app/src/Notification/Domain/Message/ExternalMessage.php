<?php

declare(strict_types=1);

namespace App\Notification\Domain\Message;

use App\Shared\Domain\Message\MessageInterface;

readonly class ExternalMessage implements MessageInterface
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
