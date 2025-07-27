<?php

declare(strict_types=1);

namespace App\Tests\Notification\Application\UseCase\Command\RemoveSubscription;

use App\Notification\Application\Service\AccessControl\SubscriptionAccessControl;
use App\Notification\Application\UseCase\Command\RemoveSubscription\RemoveSubscriptionCommand;
use App\Notification\Application\UseCase\Command\RemoveSubscription\RemoveSubscriptionCommandHandler;
use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Infrastructure\Exception\AppException;
use PHPUnit\Framework\TestCase;

class RemoveSubscriptionCommandHandlerTest extends TestCase
{
    private RemoveSubscriptionCommandHandler $handler;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private SubscriptionAccessControl $accessControl;

    protected function setUp(): void
    {
        $this->subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->accessControl = $this->createMock(SubscriptionAccessControl::class);
        $this->handler = new RemoveSubscriptionCommandHandler(
            $this->subscriptionRepository,
            $this->accessControl
        );
    }

    public function testSuccessfullyRemovesSubscription(): void
    {
        $subscriptionId = 'sub-123';
        $userId = 'user-456';
        $command = new RemoveSubscriptionCommand($subscriptionId, $userId);

        $subscription = $this->createMock(Subscription::class);

        $this->subscriptionRepository->expects($this->once())
            ->method('findById')
            ->with($subscriptionId)
            ->willReturn($subscription);

        $this->accessControl->expects($this->once())
            ->method('canViewSubscription')
            ->with($subscription, $userId)
            ->willReturn(true);

        $this->subscriptionRepository->expects($this->once())
            ->method('remove')
            ->with($subscription);

        ($this->handler)($command);
    }

    public function testThrowsWhenSubscriptionNotFound(): void
    {
        $subscriptionId = 'non-existent-sub';
        $command = new RemoveSubscriptionCommand($subscriptionId, 'user-456');

        $this->subscriptionRepository->method('findById')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Subscription with id "%s" not found.',
            $subscriptionId
        ));

        ($this->handler)($command);
    }

    public function testThrowsWhenAccessDenied(): void
    {
        $subscriptionId = 'sub-123';
        $unauthorizedUser = 'user-789';
        $command = new RemoveSubscriptionCommand($subscriptionId, $unauthorizedUser);

        $subscription = $this->createMock(Subscription::class);
        $this->subscriptionRepository->method('findById')->willReturn($subscription);
        $this->accessControl->method('canViewSubscription')->willReturn(false);

        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Not allowed to view subscription');
        $this->expectExceptionCode(403);

        ($this->handler)($command);
    }

    public function testSubscriptionIdInErrorMessage(): void
    {
        $subscriptionId = 'specific-sub-id';
        $command = new RemoveSubscriptionCommand($subscriptionId, 'user-456');

        $this->subscriptionRepository->method('findById')->willReturn(null);

        try {
            ($this->handler)($command);
            $this->fail('Expected exception was not thrown');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString($subscriptionId, $e->getMessage());
        }
    }
}
