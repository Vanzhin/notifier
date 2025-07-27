<?php

declare(strict_types=1);

namespace Notification\Application\DTO;

use App\Notification\Application\DTO\ChannelDTOTransformer;
use App\Notification\Application\DTO\SubscriptionDTO;
use App\Notification\Application\DTO\SubscriptionDTOTransformer;
use App\Notification\Domain\Aggregate\PhoneNumber;
use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Aggregate\ValueObject\EventType;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class SubscriptionDTOTransformerTest extends TestCase
{
    private SubscriptionDTOTransformer $transformer;
    private ChannelDTOTransformer $channelTransformerMock;

    protected function setUp(): void
    {
        $this->channelTransformerMock = $this->getMockBuilder(ChannelDTOTransformer::class)
            ->onlyMethods(['fromEntity'])
            ->getMock();
        $this->transformer = new SubscriptionDTOTransformer($this->channelTransformerMock);
    }

    #[DataProvider('subscriptionDataProvider')]
    public function testFromEntityBasic(Subscription $subscription): void
    {
        $dto = $this->transformer->fromEntity($subscription);

        $this->assertEquals($subscription->getId()->toString(), $dto->id);
        $this->assertEquals($subscription->getSubscriberId(), $dto->subscriber_id);
    }

    #[DataProvider('subscriptionWithoutChannelsDataProvider')]
    public function testFromEntityWithoutChannels(
        Uuid $uuid,
        string $subscriberId,
        PhoneNumber $phoneNumber,
        array $events,
        bool $isActive,
        \DateTimeImmutable $createdAt,
    ): void {
        // Assert
        $subscription = $this->createMock(Subscription::class);
        $subscription->method('getId')->willReturn($uuid);
        $subscription->method('getSubscriberId')->willReturn($subscriberId);
        $subscription->method('getSubscriptionEvents')->willReturn($events);
        $subscription->method('isActive')->willReturn($isActive);
        $subscription->method('getCreatedAt')->willReturn($createdAt);
        $subscription->channels = new ArrayCollection();
        // Arrange
        $dto = $this->transformer->fromEntity($subscription, false);

        // Assert
        $this->assertInstanceOf(SubscriptionDTO::class, $dto);
        $this->assertEquals($subscription->getId(), $dto->id);
        $this->assertEquals($subscription->getSubscriberId(), $dto->subscriber_id);
        //        $this->assertEquals(array_map(fn(PhoneNumber $phoneNumber) => $phoneNumber->getPhone()->getValue(),
        //            $subscription->phoneNumbers->toArray()), $dto->phone_numbers);
        $this->assertEquals(array_map(fn (EventType $eventType) => $eventType->value,
            $subscription->getSubscriptionEvents()),
            $dto->events);
        $this->assertEquals($subscription->isActive(), $dto->is_active);
        $this->assertEquals($subscription->getCreatedAt(), $dto->created_at);
        $this->assertEmpty($dto->channels);
    }

    public static function subscriptionDataProvider(): \Generator
    {
        yield 'case 1' => [
            new Subscription(
                Uuid::v4(),
                Uuid::v4()->toString(),
            ),
        ];
        yield 'case 2' => [
            new Subscription(
                Uuid::v4(),
                Uuid::v4()->toString(),
            ),
        ];
    }

    public static function subscriptionWithoutChannelsDataProvider(): \Generator
    {
        yield 'case 1' => [
            Uuid::v4(),
            Uuid::v4()->toString(),
            new PhoneNumber(
                Uuid::v4(),
                new \App\Notification\Domain\Aggregate\ValueObject\PhoneNumber('79111111111')),
            [EventType::MISSED_CALL, EventType::AVAILABLE],
            true,
            new \DateTimeImmutable(),
        ];
        yield 'case 2' => [
            Uuid::v4(),
            Uuid::v4()->toString(),
            new PhoneNumber(
                Uuid::v4(),
                new \App\Notification\Domain\Aggregate\ValueObject\PhoneNumber('79222222222'), ),
            [EventType::UNAVAILABLE],
            false,
            new \DateTimeImmutable(),
        ];
    }
}
