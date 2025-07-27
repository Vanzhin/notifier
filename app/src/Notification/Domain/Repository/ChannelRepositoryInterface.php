<?php

declare(strict_types=1);

namespace App\Notification\Domain\Repository;

use App\Notification\Domain\Aggregate\Channel;

interface ChannelRepositoryInterface
{
    public function save(Channel $channel): void;

    public function findById(string $channelId): ?Channel;

    public function findBySecret(string $secret): ?Channel;

    public function remove(Channel $channel): void;

    public function findByChannel(string $channel): ?Channel;
}
