<?php

declare(strict_types=1);

namespace App\Notification\Application\Service\AccessControl;

use App\Notification\Domain\Aggregate\Channel;

class ChannelAccessControl
{

    /**
     * Может ли пользователь удалить канал?
     */
    public function canDeleteChannel(Channel $channel, string $userId): bool
    {
        return $channel->isOwnedBy($userId);
    }

    /**
     * Может ли пользователь смотреть канал?
     */
    public function canViewChannel(Channel $channel, string $userId): bool
    {
        return $channel->isOwnedBy($userId);
    }

}
