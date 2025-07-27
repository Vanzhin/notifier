<?php

declare(strict_types=1);

namespace App\Tests\Integration\Notification\Infrastructure\Repository;

use App\Notification\Domain\Aggregate\PhoneNumber;
use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Repository\SubscriptionFilter;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Domain\Repository\Pager;
use App\Shared\Domain\Repository\PaginationResult;
use App\Tests\Tools\DITools;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

class SubscriptionRepositoryTest extends KernelTestCase
{
    use DITools;

    private SubscriptionRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getService(SubscriptionRepositoryInterface::class);

        // Начинаем транзакцию для каждого теста
        $this->repository->getEntityManager()->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Откатываем транзакцию после каждого теста
        if ($this->repository->getEntityManager()->getConnection()->isTransactionActive()) {
            $this->repository->getEntityManager()->getConnection()->rollBack();
        }

        parent::tearDown();

        // Очищаем менеджер сущностей
        $this->repository->getEntityManager()->clear();
    }

    #[DataProvider('subscriptionDataProvider')]
    public function testSaveAndFindById(Subscription $subscription): void
    {
        $this->repository->save($subscription);

        $found = $this->repository->findById($subscription->getId()->toString());

        $this->assertInstanceOf(Subscription::class, $found);
        $this->assertEquals($subscription->getId(), $found->getId());
    }

    #[DataProvider('subscriptionDataProvider')]
    public function testRemove(Subscription $subscription): void
    {
        $this->repository->save($subscription);
        $id = $subscription->getId();

        $this->repository->remove($subscription);

        $this->assertNull($this->repository->findById($id->toString()));
    }

    #[DataProvider('subscriptionDataProvider')]
    public function testFindByFilterWithPhoneNumbers(Subscription $subscription): void
    {
        // Создаем тестовые данные
        $this->repository->save($subscription);

        // Тестируем фильтр
        $filter = new SubscriptionFilter();
        foreach ($subscription->phoneNumbers->toArray() as $phoneNumber) {
            $filter->addPhoneNumber($phoneNumber->getPhone()->getValue());
        }

        $result = $this->repository->findByFilter($filter);

        $this->assertInstanceOf(PaginationResult::class, $result);
        $this->assertGreaterThanOrEqual(1, $result->items);
    }

    #[DataProvider('subscriptionDataProvider')]
    public function testFindByFilterWithEvents(Subscription $subscription): void
    {
        // Создаем тестовые данные
        $this->repository->save($subscription);

        // Тестируем фильтр
        $filter = new SubscriptionFilter();
        foreach ($subscription->getSubscriptionEvents() as $event) {
            $filter->addEvent($event->value);
        }

        $result = $this->repository->findByFilter($filter);

        $this->assertInstanceOf(PaginationResult::class, $result);
        $this->assertGreaterThanOrEqual(1, $result->items);
    }

    #[DataProvider('subscriptionDataProvider')]
    public function testFindByFilterWithOwnerId(Subscription $subscription): void
    {
        // Создаем тестовые данные
        $this->repository->save($subscription);

        // Тестируем фильтр
        $filter = new SubscriptionFilter();
        $filter->setOwnerId($subscription->getSubscriberId());

        $result = $this->repository->findByFilter($filter);

        $this->assertInstanceOf(PaginationResult::class, $result);
        $this->assertGreaterThanOrEqual(1, $result->items);
    }

    public function testFindByFilterWithPagination(): void
    {
        // Создаем 15 тестовых подписок
        for ($i = 10; $i < 25; ++$i) {
            $sub = new Subscription(
                id: Uuid::v4(), subscriberId: Uuid::v4()->toString(),
            );
            $sub->addPhoneNumber(new PhoneNumber(Uuid::v4(),
                new \App\Notification\Domain\Aggregate\ValueObject\PhoneNumber('791111111'.$i)));
            $sub->addEvent(EventType::AVAILABLE);
            $this->repository->save($sub);
        }

        $filter = new SubscriptionFilter(new Pager(1, 5));

        $result = $this->repository->findByFilter($filter);

        $this->assertCount(5, $result->items);
        $this->assertEquals(15, $result->total);
    }

    #[DataProvider('subscriptionDataProvider')]
    public function testFindByFilterWithMultipleConditions(Subscription $subscription): void
    {
        // Создаем тестовые данные
        $this->repository->save($subscription);

        // Тестируем фильтр
        $filter = new SubscriptionFilter();
        foreach ($subscription->getSubscriptionEvents() as $event) {
            $filter->addEvent($event->value);
        }
        foreach ($subscription->phoneNumbers->toArray() as $phoneNumber) {
            $filter->addPhoneNumber($phoneNumber->getPhone()->getValue());
        }

        $filter->setOwnerId($subscription->getSubscriberId());

        $result = $this->repository->findByFilter($filter);

        $this->assertInstanceOf(PaginationResult::class, $result);
        $this->assertGreaterThanOrEqual(1, $result->items);
    }

    public static function subscriptionDataProvider(): \Generator
    {
        $sub1 = new Subscription(
            id: Uuid::v4(),
            subscriberId: Uuid::v4()->toString()
        );
        $sub1->addPhoneNumber(new PhoneNumber(Uuid::v4(),
            new \App\Notification\Domain\Aggregate\ValueObject\PhoneNumber('79111111111')));
        $sub1->addEvent(EventType::AVAILABLE);

        $sub2 = new Subscription(
            id: Uuid::v4(),
            subscriberId: Uuid::v4()->toString()
        );
        $sub2->addPhoneNumber(new PhoneNumber(Uuid::v4(),
            new \App\Notification\Domain\Aggregate\ValueObject\PhoneNumber('79222222222')));
        $sub2->addEvent(EventType::AVAILABLE);
        $sub2->addEvent(EventType::UNAVAILABLE);

        yield 'case 1' => [
            $sub1,
        ];
        yield 'case 2' => [
            $sub2,
        ];
    }
}
