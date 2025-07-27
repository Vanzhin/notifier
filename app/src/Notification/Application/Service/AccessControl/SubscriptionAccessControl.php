<?php

declare(strict_types=1);

namespace App\Notification\Application\Service\AccessControl;

use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Aggregate\Subscription;

class SubscriptionAccessControl
{
    /**
     * Может ли пользователь удалить подписку?
     */
    public function canDeleteSubscription(Subscription $subscription, string $userId): bool
    {
        return $subscription->isOwnedBy($userId);
    }

    /**
     * Может ли пользователь изменять подписку?
     */
    public function canUpdateSubscription(Subscription $subscription, string $userId): bool
    {
        return $subscription->isOwnedBy($userId);
    }

    /**
     * Может ли пользователь смотреть подписку?
     */
    public function canViewSubscription(Subscription $subscription, string $userId): bool
    {
        return $subscription->isOwnedBy($userId);
    }

    /**
     * Может ли пользователь добавлять канал в подписку?
     */
    public function canAddChannelToSubscription(Channel $channel, Subscription $subscription, string $userId): bool
    {
        if (!$this->canViewSubscription($subscription, $userId)) {
            return false;
        }

        return $subscription->getSubscriberId() === $channel->getOwnerId();
    }
}
