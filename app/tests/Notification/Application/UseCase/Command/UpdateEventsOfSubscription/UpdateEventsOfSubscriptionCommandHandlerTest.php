<?php

declare(strict_types=1);

namespace App\Tests\Notification\Application\UseCase\Command\UpdateEventsOfSubscription;

use App\Notification\Application\Service\AccessControl\SubscriptionAccessControl;
use App\Notification\Application\UseCase\Command\UpdateEventsOfSubscription\UpdateEventsOfSubscriptionCommand;
use App\Notification\Application\UseCase\Command\UpdateEventsOfSubscription\UpdateEventsOfSubscriptionCommandHandler;
use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Infrastructure\Exception\AppException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UpdateEventsOfSubscriptionCommandHandlerTest extends TestCase
{
    private UpdateEventsOfSubscriptionCommandHandler $handler;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private SubscriptionAccessControl $accessControl;

    protected function setUp(): void
    {
        $this->subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->accessControl = $this->createMock(SubscriptionAccessControl::class);
        $this->handler = new UpdateEventsOfSubscriptionCommandHandler(
            $this->subscriptionRepository,
            $this->accessControl
        );
    }

    #[dataProvider('subscriptionDataProvider')]
    public function testSuccessfullyUpdatesSubscriptionEvents(
        string $subscriptionId,
        string $ownerId,
        array $events,
    ): void {
        $command = new UpdateEventsOfSubscriptionCommand($subscriptionId, $ownerId, ...$events);

        $subscription = $this->createMock(Subscription::class);

        $this->subscriptionRepository->expects($this->once())
            ->method('findById')
            ->with($subscriptionId)
            ->willReturn($subscription);

        $this->accessControl->expects($this->once())
            ->method('canUpdateSubscription')
            ->with($subscription, $ownerId)
            ->willReturn(true);

        $subscription->expects($this->once())
            ->method('setSubscriptionEvents');

        $subscription->expects($this->exactly(count($events)))
            ->method('addEvent');

        $this->subscriptionRepository->expects($this->once())
            ->method('save')
            ->with($subscription);

        ($this->handler)($command);
    }

    public function testThrowsWhenSubscriptionNotFound(): void
    {
        $command = new UpdateEventsOfSubscriptionCommand(
            'non-existent-sub',
            'user-456',
            ...[]
        );

        $this->subscriptionRepository->method('findById')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subscription not found');

        ($this->handler)($command);
    }

    public function testThrowsWhenAccessDenied(): void
    {
        $subscriptionId = 'sub-123';
        $unauthorizedUser = 'user-789';
        $command = new UpdateEventsOfSubscriptionCommand($subscriptionId, $unauthorizedUser, ...[]);

        $subscription = $this->createMock(Subscription::class);
        $this->subscriptionRepository->method('findById')->willReturn($subscription);
        $this->accessControl->method('canUpdateSubscription')->willReturn(false);

        $this->expectException(AppException::class);
        $this->expectExceptionMessage('You are not allowed to do this action.');
        $this->expectExceptionCode(403);

        ($this->handler)($command);
    }

    public static function subscriptionDataProvider(): \Generator
    {
        yield 'case 1 event' => [
            'sub-123',
            'user-456',
            [EventType::MISSED_CALL->value]
        ];
        yield 'case 2 events' => [
            'sub-123',
            'user-456',
            [EventType::MISSED_CALL->value, EventType::UNAVAILABLE->value]
        ];
        yield 'case empty events' => [
            'sub-123',
            'user-456',
            []
        ];
    }
}
