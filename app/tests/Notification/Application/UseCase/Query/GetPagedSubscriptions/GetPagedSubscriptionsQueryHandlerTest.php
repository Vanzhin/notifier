<?php

declare(strict_types=1);

namespace App\Tests\Notification\Application\UseCase\Query\GetPagedSubscriptionList;

use App\Notification\Application\DTO\SubscriptionDTO;
use App\Notification\Application\DTO\SubscriptionDTOTransformer;
use App\Notification\Application\Service\AccessControl\SubscriptionAccessControl;
use App\Notification\Application\UseCase\Query\GetPagedSubscriptionList\GetPagedSubscriptionsQuery;
use App\Notification\Application\UseCase\Query\GetPagedSubscriptionList\GetPagedSubscriptionsQueryHandler;
use App\Notification\Application\UseCase\Query\GetPagedSubscriptionList\GetPagedSubscriptionsQueryResult;
use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Domain\Repository\Pager;
use App\Notification\Domain\Repository\SubscriptionFilter;
use App\Shared\Domain\Repository\PaginationResult;
use PHPUnit\Framework\TestCase;

class GetPagedSubscriptionsQueryHandlerTest extends TestCase
{
    private GetPagedSubscriptionsQueryHandler $handler;
    private SubscriptionAccessControl $accessControl;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private SubscriptionDTOTransformer $transformer;

    protected function setUp(): void
    {
        $this->accessControl = $this->createMock(SubscriptionAccessControl::class);
        $this->subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->transformer = $this->createMock(SubscriptionDTOTransformer::class);

        $this->handler = new GetPagedSubscriptionsQueryHandler(
            $this->accessControl,
            $this->subscriptionRepository,
            $this->transformer
        );
    }

    public function testReturnsPagedSubscriptions(): void
    {
        $userId = 'user-123';
        $page = 1;
        $perPage = 10;
        $total = 2;

        $filter = new SubscriptionFilter(new Pager($page, $perPage));
        $query = new GetPagedSubscriptionsQuery(filter: $filter, userId: $userId);

        $subscription1 = $this->createMock(Subscription::class);
        $subscription2 = $this->createMock(Subscription::class);
        $dto1 = new SubscriptionDTO(id: 'sub-1', subscriber_id: 'user-1234');
        $dto2 = new SubscriptionDTO(id: 'sub-2', subscriber_id: 'user-3432');
        $paginatedResult = new PaginationResult([$subscription1, $subscription2],
            count([$subscription1, $subscription2]));

        $this->subscriptionRepository->expects($this->once())
            ->method('findByFilter')
            ->with($this->callback(function ($filter) use ($userId) {
                return $filter->getOwnerId() === $userId;
            }))
            ->willReturn($paginatedResult);

        $this->accessControl->expects($this->exactly(2))
            ->method('canViewSubscription')
            ->willReturn(true);

        $this->transformer->expects($this->exactly(2))
            ->method('fromEntity')
            ->willReturnOnConsecutiveCalls($dto1, $dto2);

        $result = ($this->handler)($query);

        $this->assertInstanceOf(GetPagedSubscriptionsQueryResult::class, $result);
        $this->assertEquals([$dto1, $dto2], $result->subscriptions);
        $this->assertEquals($page, $result->pager->page);
        $this->assertEquals($perPage, $result->pager->per_page);
        $this->assertEquals($total, $result->pager->total_items);
    }

    public function testFiltersUnauthorizedSubscriptions(): void
    {
        $userId = 'user-123';
        $filter = new SubscriptionFilter(new Pager(1, 10));
        $query = new GetPagedSubscriptionsQuery(filter: $filter, userId: $userId);

        $subscription1 = $this->createMock(Subscription::class);
        $subscription2 = $this->createMock(Subscription::class);
        $dto1 = new SubscriptionDTO(id: 'sub-1', subscriber_id: 'user-1234');


        $paginatedResult = new PaginationResult([$subscription1, $subscription2],
            count([$subscription1, $subscription2]));

        $this->subscriptionRepository->method('findByFilter')->willReturn($paginatedResult);

        $this->accessControl->expects($this->exactly(2))
            ->method('canViewSubscription')
            ->willReturnOnConsecutiveCalls(true, false);

        $this->transformer->expects($this->once())
            ->method('fromEntity')
            ->with($subscription1)
            ->willReturn($dto1);

        $result = ($this->handler)($query);

        $this->assertEquals([$dto1], $result->subscriptions);
    }

    public function testSetsOwnerIdInFilter(): void
    {
        $userId = 'user-123';
        $filter = new SubscriptionFilter(new Pager(1, 10));
        $query = new GetPagedSubscriptionsQuery(filter: $filter, userId: $userId);

        $paginatedResult = new PaginationResult([],0);

        $this->subscriptionRepository->expects($this->once())
            ->method('findByFilter')
            ->with($this->callback(function ($filter) use ($userId) {
                return $filter->getOwnerId() === $userId;
            }))
            ->willReturn($paginatedResult);

        $result = ($this->handler)($query);

        $this->assertEmpty($result->subscriptions);
    }

    public function testReturnsEmptyResultWhenNoSubscriptions(): void
    {
        $userId = 'user-123';
        $filter = new SubscriptionFilter(new Pager(1, 10));
        $query = new GetPagedSubscriptionsQuery(filter: $filter, userId: $userId);

        $paginatedResult = new PaginationResult([],0);

        $this->subscriptionRepository->method('findByFilter')->willReturn($paginatedResult);
        $this->accessControl->expects($this->never())->method('canViewSubscription');
        $this->transformer->expects($this->never())->method('fromEntity');

        $result = ($this->handler)($query);

        $this->assertEmpty($result->subscriptions);
        $this->assertEquals(0, $result->pager->total);
    }
}
