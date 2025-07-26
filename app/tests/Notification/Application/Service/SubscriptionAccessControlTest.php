<?php

declare(strict_types=1);

namespace App\Tests\Notification\Application\Service\AccessControl;

use App\Notification\Application\Service\AccessControl\SubscriptionAccessControl;
use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Aggregate\Subscription;
use PHPUnit\Framework\TestCase;

class SubscriptionAccessControlTest extends TestCase
{
    private SubscriptionAccessControl $accessControl;

    protected function setUp(): void
    {
        $this->accessControl = new SubscriptionAccessControl();
    }

    // Basic subscription access tests
    public function testCanDeleteSubscriptionWhenOwner(): void
    {
        $subscription = $this->createMock(Subscription::class);
        $subscription->method('isOwnedBy')->with('user123')->willReturn(true);

        $this->assertTrue($this->accessControl->canDeleteSubscription($subscription, 'user123'));
    }

    public function testCanDeleteSubscriptionWhenNotOwner(): void
    {
        $subscription = $this->createMock(Subscription::class);
        $subscription->method('isOwnedBy')->with('user123')->willReturn(false);

        $this->assertFalse($this->accessControl->canDeleteSubscription($subscription, 'user123'));
    }

    public function testCanUpdateSubscriptionWhenOwner(): void
    {
        $subscription = $this->createMock(Subscription::class);
        $subscription->method('isOwnedBy')->with('user123')->willReturn(true);

        $this->assertTrue($this->accessControl->canUpdateSubscription($subscription, 'user123'));
    }

    public function testCanUpdateSubscriptionWhenNotOwner(): void
    {
        $subscription = $this->createMock(Subscription::class);
        $subscription->method('isOwnedBy')->with('user123')->willReturn(false);

        $this->assertFalse($this->accessControl->canUpdateSubscription($subscription, 'user123'));
    }

    public function testCanViewSubscriptionWhenOwner(): void
    {
        $subscription = $this->createMock(Subscription::class);
        $subscription->method('isOwnedBy')->with('user123')->willReturn(true);

        $this->assertTrue($this->accessControl->canViewSubscription($subscription, 'user123'));
    }

    public function testCanViewSubscriptionWhenNotOwner(): void
    {
        $subscription = $this->createMock(Subscription::class);
        $subscription->method('isOwnedBy')->with('user123')->willReturn(false);

        $this->assertFalse($this->accessControl->canViewSubscription($subscription, 'user123'));
    }

    public function testCanAddChannelWhenOwnerAndMatchingIds(): void
    {
        $subscription = $this->createMock(Subscription::class);
        $subscription->method('isOwnedBy')->with('user123')->willReturn(true);
        $subscription->method('getSubscriberId')->willReturn('user123');

        $channel = $this->createMock(Channel::class);
        $channel->method('getOwnerId')->willReturn('user123');

        $this->assertTrue($this->accessControl->canAddChannelToSubscription($channel, $subscription, 'user123'));
    }

    public function testCannotAddChannelWhenNotSubscriptionOwner(): void
    {
        $subscription = $this->createMock(Subscription::class);
        $subscription->method('isOwnedBy')->with('user123')->willReturn(false);
        $subscription->method('getSubscriberId')->willReturn('user456');

        $channel = $this->createMock(Channel::class);
        $channel->method('getOwnerId')->willReturn('user123');

        $this->assertFalse($this->accessControl->canAddChannelToSubscription($channel, $subscription, 'user123'));
    }

    public function testCannotAddChannelWhenIdsDontMatch(): void
    {
        $subscription = $this->createMock(Subscription::class);
        $subscription->method('isOwnedBy')->with('user123')->willReturn(true);
        $subscription->method('getSubscriberId')->willReturn('user123');

        $channel = $this->createMock(Channel::class);
        $channel->method('getOwnerId')->willReturn('user456');

        $this->assertFalse($this->accessControl->canAddChannelToSubscription($channel, $subscription, 'user123'));
    }

    public function testConsistentOwnershipCheck(): void
    {
        $userId = 'user123';
        $subscription = $this->createMock(Subscription::class);

        // Expect isOwnedBy to be called 4 times (3 direct calls + 1 in canAddChannelToSubscription)
        $subscription->expects($this->exactly(4))
            ->method('isOwnedBy')
            ->with($userId)
            ->willReturn(true);

        $this->accessControl->canDeleteSubscription($subscription, $userId);
        $this->accessControl->canUpdateSubscription($subscription, $userId);
        $this->accessControl->canViewSubscription($subscription, $userId);

        $channel = $this->createMock(Channel::class);
        $channel->method('getOwnerId')->willReturn($userId);
        $subscription->method('getSubscriberId')->willReturn($userId);

        $this->accessControl->canAddChannelToSubscription($channel, $subscription, $userId);
    }
}
