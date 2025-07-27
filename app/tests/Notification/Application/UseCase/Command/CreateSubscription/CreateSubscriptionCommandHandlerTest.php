<?php

declare(strict_types=1);

namespace App\Tests\Notification\Application\UseCase\Command\CreateSubscription;

use App\Notification\Application\UseCase\Command\CreateSubscription\CreateSubscriptionCommand;
use App\Notification\Application\UseCase\Command\CreateSubscription\CreateSubscriptionCommandHandler;
use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Factory\SubscriptionFactoryInterface;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Notification\Domain\Service\PhoneNumberOrganizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class CreateSubscriptionCommandHandlerTest extends TestCase
{
    private CreateSubscriptionCommandHandler $handler;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private SubscriptionFactoryInterface $subscriptionFactory;

    protected function setUp(): void
    {
        $this->subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->subscriptionFactory = $this->createMock(SubscriptionFactoryInterface::class);

        // Create real PhoneNumberOrganizer with mock dependencies
        $this->numberOrganizer = new PhoneNumberOrganizer(
            $this->createMock(\App\Notification\Domain\Repository\PhoneRepositoryInterface::class)
        );

        $this->handler = new CreateSubscriptionCommandHandler(
            $this->subscriptionRepository,
            $this->subscriptionFactory,
            $this->numberOrganizer
        );
    }

    #[dataProvider('subscriptionDataProvider')]
    public function testCreatesSubscriptionWithEventsAndPhoneNumbers(
        string $subscriberId,
        array $phoneNumbers,
        array $events,
    ): void {
        $command = new CreateSubscriptionCommand(
            subscriberId: $subscriberId,
            phoneNumbers: $phoneNumbers,
            events: $events
        );

        $subscription = $this->createMock(Subscription::class);
        $subscription->method('getId')->willReturn(Uuid::v4());

        // Mock factory
        $this->subscriptionFactory->expects($this->once())
            ->method('create')
            ->with($command->subscriberId)
            ->willReturn($subscription);

        $subscription->expects($this->exactly(count($phoneNumbers)))
            ->method('addPhoneNumber')
            ->with($this->callback(function ($phoneNumber) {
                return $phoneNumber instanceof \App\Notification\Domain\Aggregate\PhoneNumber;
            }));

        // Verify subscription is saved
        $this->subscriptionRepository->expects($this->once())
            ->method('save')
            ->with($subscription);

        $result = ($this->handler)($command);

        $this->assertTrue(Uuid::isValid($result));
    }

    public function testCreatesSubscriptionWithoutOptionalFields(): void
    {
        $command = new CreateSubscriptionCommand(
            subscriberId: 'user-123',
            phoneNumbers: [],
            events: []
        );

        $subscription = $this->createMock(Subscription::class);
        $subscription->method('getId')->willReturn(Uuid::v4());

        $this->subscriptionFactory->method('create')->willReturn($subscription);

        // Should not add any events or phone numbers
        $subscription->expects($this->never())->method('addEvent');
        $subscription->expects($this->never())->method('addPhoneNumber');

        $this->subscriptionRepository->expects($this->once())
            ->method('save')
            ->with($subscription);

        ($this->handler)($command);
    }

    public function testReturnsSubscriptionId(): void
    {
        $expectedId = Uuid::v4();
        $command = new CreateSubscriptionCommand('user-123', [], []);

        $subscription = $this->createMock(Subscription::class);
        $subscription->method('getId')->willReturn($expectedId);

        $this->subscriptionFactory->method('create')->willReturn($subscription);

        $result = ($this->handler)($command);

        $this->assertEquals($expectedId->toString(), $result);
    }

    public static function subscriptionDataProvider(): \Generator
    {
        yield 'case 1 phone' => [
            '1234',
            ['79111111111'],
            [EventType::MISSED_CALL->value]
        ];
        yield 'case 2 phones' => [
            '1234',
            ['79111111111', '79222222222'],
            [EventType::MISSED_CALL->value]
        ];
        yield 'case empty phones' => [
            '1234',
            [],
            [EventType::MISSED_CALL->value]
        ];
        yield 'case empty phone empty events' => [
            '1234',
            [],
            []
        ];
        yield 'case 1 phone many events' => [
            '1234',
            ['79111111111'],
            [EventType::MISSED_CALL->value, EventType::UNAVAILABLE->value, EventType::AVAILABLE->value]
        ];
        yield 'case 2 phone many events' => [
            '1234',
            ['79111111111', '79222222222'],
            [EventType::MISSED_CALL->value, EventType::UNAVAILABLE->value, EventType::AVAILABLE->value]
        ];
    }
}
