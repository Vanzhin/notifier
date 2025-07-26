<?php

declare(strict_types=1);

namespace App\Notification\Application\Service\AccessControl;

use App\Notification\Domain\Aggregate\Channel;

class ChannelAccessControl
{
// пока так, потом можно ввести систему ролей
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

    /**
     * Может ли пользователь инициировать верификацию канала?
     */
    public function canInitVerificationChannel(Channel $channel, string $userId): bool
    {
        return $channel->isOwnedBy($userId);
    }

}
