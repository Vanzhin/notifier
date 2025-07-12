<?php

declare(strict_types=1);

namespace App\Notification\Application\Service\AccessControl;

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
     * Может ли пользователь смотреть подписку?
     */
    public function canViewSubscription(Subscription $subscription, string $userId): bool
    {
        return $subscription->isOwnedBy($userId);
    }

}
