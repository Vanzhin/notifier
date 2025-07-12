<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Query\FindChannel;

use App\Shared\Application\Query\Query;

readonly class FindChannelQuery extends Query
{
    public function __construct(public string $channelId, public string $ownerId)
    {
    }
}
