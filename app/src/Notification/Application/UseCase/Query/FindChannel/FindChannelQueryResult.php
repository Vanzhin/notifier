<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Query\FindChannel;

use App\Notification\Application\DTO\ChannelDTO;

readonly class FindChannelQueryResult
{
    public function __construct(public ?ChannelDTO $channel)
    {
    }
}
