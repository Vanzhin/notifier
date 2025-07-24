<?php

declare(strict_types=1);

namespace App\Tests\Notification\Domain\Aggregate;

use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Aggregate\ValueObject\PhoneNumber;
use App\Shared\Infrastructure\Exception\AppException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class SubscriptionTest extends TestCase
{
    private Subscription $subscription;
    private Uuid $id;
    private string $subscriberId = 'user123';

    protected function setUp(): void
    {
        $this->id = Uuid::v4();
        $this->subscription = new Subscription($this->id, $this->subscriberId);
    }

    public function testInitialState(): void
    {
        $this->assertEquals($this->id, $this->subscription->getId());
        $this->assertEquals($this->subscriberId, $this->subscription->getSubscriberId());
        $this->assertEmpty($this->subscription->getSubscriptionEvents());
        $this->assertEmpty($this->subscription->channels);
        $this->assertEmpty($this->subscription->phoneNumbers);
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->subscription->getCreatedAt());
    }

    public function testEventManagement(): void
    {
        $eventTypeUnavailable = EventType::UNAVAILABLE;
        $eventTypeAvailable = EventType::AVAILABLE;

        // Add events
        $this->subscription->addEvent($eventTypeUnavailable);
        $this->subscription->addEvent($eventTypeAvailable);
        $this->assertCount(2, $this->subscription->getSubscriptionEvents());
        $this->assertContains($eventTypeUnavailable, $this->subscription->getSubscriptionEvents());
        $this->assertContains($eventTypeAvailable, $this->subscription->getSubscriptionEvents());

        // Add duplicate event
        $this->subscription->addEvent($eventTypeUnavailable);
        $this->assertCount(2, $this->subscription->getSubscriptionEvents());

        // Remove event
        $this->subscription->removeEvent($eventTypeUnavailable);
        $this->assertCount(1, $this->subscription->getSubscriptionEvents());
        $this->assertNotContains($eventTypeUnavailable, $this->subscription->getSubscriptionEvents());
    }

    public function testSetSubscriptionEvents(): void
    {
        $eventTypeUnavailable = EventType::UNAVAILABLE;
        $eventTypeAvailable = EventType::AVAILABLE;

        $this->subscription->setSubscriptionEvents($eventTypeUnavailable, $eventTypeAvailable);
        $this->assertCount(2, $this->subscription->getSubscriptionEvents());
        $this->assertContains($eventTypeUnavailable, $this->subscription->getSubscriptionEvents());
        $this->assertContains($eventTypeAvailable, $this->subscription->getSubscriptionEvents());

        // Test overwriting
        $eventTypeMissedCall = EventType::MISSED_CALL;
        $this->subscription->setSubscriptionEvents($eventTypeMissedCall);
        $this->assertCount(1, $this->subscription->getSubscriptionEvents());
        $this->assertContains($eventTypeMissedCall, $this->subscription->getSubscriptionEvents());
    }

    public function testAddChannel(): void
    {
        $channel = $this->createMockChannel($this->subscriberId, ChannelType::TELEGRAM);

        $this->subscription->addChannel($channel);
        $this->assertCount(1, $this->subscription->channels);
        $this->assertTrue($this->subscription->channels->contains($channel));
    }

    public function testAddChannelFailsForWrongOwner(): void
    {
        $channel = $this->createMockChannel('different-user', ChannelType::TELEGRAM);

        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Channel cannot be added.');
        $this->subscription->addChannel($channel);
    }

    public function testRemoveChannel(): void
    {
        $channel = $this->createMockChannel($this->subscriberId, ChannelType::EMAIL);

        $this->subscription->addChannel($channel);
        $this->assertCount(1, $this->subscription->channels);

        $this->subscription->removeChannel($channel);
        $this->assertCount(0, $this->subscription->channels);
    }

    public function testPhoneNumberManagement(): void
    {
        $phoneNumber1 = $this->createMockPhoneNumber('79111111111');
        $phoneNumber2 = $this->createMockPhoneNumber('792222222222');

        // Add phone numbers
        $this->subscription->addPhoneNumber($phoneNumber1);
        $this->subscription->addPhoneNumber($phoneNumber2);
        $this->assertCount(2, $this->subscription->phoneNumbers);

        // Add duplicate phone number
        $this->subscription->addPhoneNumber($phoneNumber1);
        $this->assertCount(2, $this->subscription->phoneNumbers);

        // Remove phone number
        $this->subscription->removePhoneNumber($phoneNumber1);
        $this->assertCount(1, $this->subscription->phoneNumbers);
    }

    public function testIsActive(): void
    {
        // Test with no channels
        $this->assertFalse($this->subscription->isActive());

        // Test with unverified channel
        $unverifiedChannel = $this->createMockChannel($this->subscriberId, ChannelType::EMAIL, false);
        $this->subscription->addChannel($unverifiedChannel);
        $this->assertFalse($this->subscription->isActive());

        // Test with verified channel
        $verifiedChannel = $this->createMockChannel($this->subscriberId, ChannelType::TELEGRAM, true);
        $this->subscription->addChannel($verifiedChannel);
        $this->assertFalse($this->subscription->isActive());
    }


    public function testOwnershipCheck(): void
    {
        $this->assertTrue($this->subscription->isOwnedBy($this->subscriberId));
        $this->assertFalse($this->subscription->isOwnedBy('different-user'));
    }

    private function createMockChannel(
        string $ownerId,
        ChannelType $channelType,
        bool $isVerified = true
    ): \App\Notification\Domain\Aggregate\Channel {
        $channel = $this->createMock(\App\Notification\Domain\Aggregate\Channel::class);
        $channel->method('getOwnerId')->willReturn($ownerId);
        $channel->method('isVerified')->willReturn($isVerified);
        $channel->method('getType')->willReturn($channelType);
        return $channel;
    }

    /**
     * @throws \Exception
     * @throws Exception
     */
    private function createMockPhoneNumber(string $phoneNumber): \App\Notification\Domain\Aggregate\PhoneNumber
    {
        $phone = $this->createMock(\App\Notification\Domain\Aggregate\PhoneNumber::class);
        $phoneNumber = new PhoneNumber(
            $phoneNumber);
        $phone->method('getPhone')->willReturn($phoneNumber);
        return $phone;
    }
}
