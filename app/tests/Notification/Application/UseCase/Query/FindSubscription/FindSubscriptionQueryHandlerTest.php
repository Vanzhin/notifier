<?php

declare(strict_types=1);

namespace App\Tests\Notification\Application\UseCase\Query\FindSubscription;

use App\Notification\Application\DTO\SubscriptionDTO;
use App\Notification\Application\DTO\SubscriptionDTOTransformer;
use App\Notification\Application\Service\AccessControl\SubscriptionAccessControl;
use App\Notification\Application\UseCase\Query\FindSubscription\FindSubscriptionQuery;
use App\Notification\Application\UseCase\Query\FindSubscription\FindSubscriptionQueryHandler;
use App\Notification\Application\UseCase\Query\FindSubscription\FindSubscriptionQueryResult;
use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Infrastructure\Exception\AppException;
use PHPUnit\Framework\TestCase;

class FindSubscriptionQueryHandlerTest extends TestCase
{
    private FindSubscriptionQueryHandler $handler;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private SubscriptionDTOTransformer $transformer;
    private SubscriptionAccessControl $accessControl;

    protected function setUp(): void
    {
        $this->subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->transformer = $this->createMock(SubscriptionDTOTransformer::class);
        $this->accessControl = $this->createMock(SubscriptionAccessControl::class);

        $this->handler = new FindSubscriptionQueryHandler(
            $this->subscriptionRepository,
            $this->transformer,
            $this->accessControl
        );
    }

    public function testReturnsNullWhenSubscriptionNotFound(): void
    {
        $query = new FindSubscriptionQuery('non-existent-sub', 'user-123');

        $this->subscriptionRepository->expects($this->once())
            ->method('findById')
            ->with($query->subscriptionId)
            ->willReturn(null);

        $result = ($this->handler)($query);

        $this->assertInstanceOf(FindSubscriptionQueryResult::class, $result);
        $this->assertNull($result->subscription);
    }

    public function testThrowsWhenAccessDenied(): void
    {
        $query = new FindSubscriptionQuery('sub-123', 'unauthorized-user');
        $subscription = $this->createMock(Subscription::class);

        $this->subscriptionRepository->method('findById')->willReturn($subscription);
        $this->accessControl->method('canViewSubscription')->willReturn(false);

        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Subscription is not allowed to view.');
        $this->expectExceptionCode(403);

        ($this->handler)($query);
    }

    public function testReturnsTransformedSubscriptionWhenFound(): void
    {
        $query = new FindSubscriptionQuery('sub-123', 'user-456');
        $subscription = $this->createMock(Subscription::class);
        $subscriptionDTO = new SubscriptionDTO(id: 'sub-123', subscriber_id: 'user-456');

        $this->subscriptionRepository->expects($this->once())
            ->method('findById')
            ->with($query->subscriptionId)
            ->willReturn($subscription);

        $this->accessControl->expects($this->once())
            ->method('canViewSubscription')
            ->with($subscription, $query->userId)
            ->willReturn(true);

        $this->transformer->expects($this->once())
            ->method('fromEntity')
            ->with($subscription)
            ->willReturn($subscriptionDTO);

        $result = ($this->handler)($query);

        $this->assertInstanceOf(FindSubscriptionQueryResult::class, $result);
        $this->assertEquals($subscriptionDTO, $result->subscription);
    }

    public function testReturnsEmptyResultForNullSubscription(): void
    {
        $query = new FindSubscriptionQuery('non-existent-sub', 'user-123');

        $this->subscriptionRepository->method('findById')->willReturn(null);
        $this->accessControl->expects($this->never())->method('canViewSubscription');
        $this->transformer->expects($this->never())->method('fromEntity');

        $result = ($this->handler)($query);

        $this->assertNull($result->subscription);
    }
}
