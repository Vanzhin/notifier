<?php

declare(strict_types=1);

namespace App\Tests\Notification\Application\Service\AccessControl;

use App\Notification\Application\Service\AccessControl\ChannelAccessControl;
use App\Notification\Domain\Aggregate\Channel;
use PHPUnit\Framework\TestCase;

class ChannelAccessControlTest extends TestCase
{
    private ChannelAccessControl $accessControl;

    protected function setUp(): void
    {
        $this->accessControl = new ChannelAccessControl();
    }

    public function testCanDeleteChannelWhenOwner(): void
    {
        $channel = $this->createMock(Channel::class);
        $channel->method('isOwnedBy')->with('user123')->willReturn(true);

        $this->assertTrue($this->accessControl->canDeleteChannel($channel, 'user123'));
    }

    public function testCanDeleteChannelWhenNotOwner(): void
    {
        $channel = $this->createMock(Channel::class);
        $channel->method('isOwnedBy')->with('user123')->willReturn(false);

        $this->assertFalse($this->accessControl->canDeleteChannel($channel, 'user123'));
    }

    public function testCanViewChannelWhenOwner(): void
    {
        $channel = $this->createMock(Channel::class);
        $channel->method('isOwnedBy')->with('user123')->willReturn(true);

        $this->assertTrue($this->accessControl->canViewChannel($channel, 'user123'));
    }

    public function testCanViewChannelWhenNotOwner(): void
    {
        $channel = $this->createMock(Channel::class);
        $channel->method('isOwnedBy')->with('user123')->willReturn(false);

        $this->assertFalse($this->accessControl->canViewChannel($channel, 'user123'));
    }

    public function testCanInitVerificationWhenOwner(): void
    {
        $channel = $this->createMock(Channel::class);
        $channel->method('isOwnedBy')->with('user123')->willReturn(true);

        $this->assertTrue($this->accessControl->canInitVerificationChannel($channel, 'user123'));
    }

    public function testCanInitVerificationWhenNotOwner(): void
    {
        $channel = $this->createMock(Channel::class);
        $channel->method('isOwnedBy')->with('user123')->willReturn(false);

        $this->assertFalse($this->accessControl->canInitVerificationChannel($channel, 'user123'));
    }

    public function testAllMethodsUseSameOwnershipCheck(): void
    {
        $ownerId = 'user123';
        $channel = $this->createMock(Channel::class);

        // Set expectation that isOwnedBy will be called exactly 3 times with same argument
        $channel->expects($this->exactly(3))
            ->method('isOwnedBy')
            ->with($ownerId)
            ->willReturn(true);

        $this->accessControl->canDeleteChannel($channel, $ownerId);
        $this->accessControl->canViewChannel($channel, $ownerId);
        $this->accessControl->canInitVerificationChannel($channel, $ownerId);
    }
}
