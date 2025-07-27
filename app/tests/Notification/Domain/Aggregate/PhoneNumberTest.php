<?php

declare(strict_types=1);

namespace App\Tests\Notification\Domain\Aggregate;

use App\Notification\Domain\Aggregate\PhoneNumber;
use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Aggregate\ValueObject\PhoneNumber as Phone;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class PhoneNumberTest extends TestCase
{
    private PhoneNumber $phoneNumber;
    private Uuid $id;
    private Phone $phone;

    protected function setUp(): void
    {
        $this->id = Uuid::v4();
        $this->phone = new Phone('79111111111');
        $this->phoneNumber = new PhoneNumber($this->id, $this->phone);
    }

    public function testInitialState(): void
    {
        $this->assertEquals($this->id, $this->phoneNumber->getId());
        $this->assertEquals($this->phone, $this->phoneNumber->getPhone());
        $this->assertCount(0, $this->phoneNumber->getSubscriptions());
    }

    public function testStringRepresentation(): void
    {
        $this->assertEquals('79111111111', (string) $this->phoneNumber);
    }

    public function testAddSubscription(): void
    {
        $subscription = $this->createMock(Subscription::class);

        $this->phoneNumber->addSubscription($subscription);

        $this->assertCount(1, $this->phoneNumber->getSubscriptions());
        $this->assertTrue($this->phoneNumber->getSubscriptions()->contains($subscription));
    }

    public function testAddSameSubscriptionTwice(): void
    {
        $subscription = $this->createMock(Subscription::class);

        $this->phoneNumber->addSubscription($subscription);
        $this->phoneNumber->addSubscription($subscription);

        $this->assertCount(1, $this->phoneNumber->getSubscriptions());
    }

    public function testRemoveSubscription(): void
    {
        $subscription = $this->createMock(Subscription::class);

        $this->phoneNumber->addSubscription($subscription);
        $this->assertCount(1, $this->phoneNumber->getSubscriptions());

        $this->phoneNumber->removeSubscription($subscription);
        $this->assertCount(0, $this->phoneNumber->getSubscriptions());
    }

    public function testRemoveNonExistentSubscription(): void
    {
        $subscription1 = $this->createMock(Subscription::class);
        $subscription2 = $this->createMock(Subscription::class);

        $this->phoneNumber->addSubscription($subscription1);
        $this->assertCount(1, $this->phoneNumber->getSubscriptions());

        $this->phoneNumber->removeSubscription($subscription2);
        $this->assertCount(1, $this->phoneNumber->getSubscriptions());
    }

    public function testMultipleSubscriptions(): void
    {
        $subscription1 = $this->createMock(Subscription::class);
        $subscription2 = $this->createMock(Subscription::class);
        $subscription3 = $this->createMock(Subscription::class);

        $this->phoneNumber->addSubscription($subscription1);
        $this->phoneNumber->addSubscription($subscription2);
        $this->phoneNumber->addSubscription($subscription3);

        $this->assertCount(3, $this->phoneNumber->getSubscriptions());

        $this->phoneNumber->removeSubscription($subscription2);
        $this->assertCount(2, $this->phoneNumber->getSubscriptions());
        $this->assertTrue($this->phoneNumber->getSubscriptions()->contains($subscription1));
        $this->assertTrue($this->phoneNumber->getSubscriptions()->contains($subscription3));
    }
}
