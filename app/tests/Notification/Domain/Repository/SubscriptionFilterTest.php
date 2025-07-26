<?php

declare(strict_types=1);

namespace App\Tests\Notification\Domain\Repository;

use App\Notification\Domain\Repository\SubscriptionFilter;
use App\Shared\Domain\Repository\Pager;
use PHPUnit\Framework\TestCase;

class SubscriptionFilterTest extends TestCase
{
    public function testInitialState(): void
    {
        $filter = new SubscriptionFilter();

        $this->assertNull($filter->pager);
        $this->assertEmpty($filter->getSort());
        $this->assertEmpty($filter->getPhoneNumbers());
        $this->assertEmpty($filter->getEvents());
        $this->assertNull($filter->getOwnerId());
    }

    public function testPagerInConstructor(): void
    {
        $pager = new Pager(1, 10);
        $filter = new SubscriptionFilter($pager);

        $this->assertSame($pager, $filter->pager);
    }

    public function testSortManagement(): void
    {
        $filter = new SubscriptionFilter();
        $sort = ['created_at' => 'desc', 'id' => 'asc'];

        $filter->setSort($sort);
        $this->assertSame($sort, $filter->getSort());
    }

    public function testPhoneNumberManagement(): void
    {
        $filter = new SubscriptionFilter();

        // Add single phone number
        $filter->addPhoneNumber('+1234567890');
        $this->assertSame(['+1234567890'], $filter->getPhoneNumbers());

        // Add another unique phone number
        $filter->addPhoneNumber('+9876543210');
        $this->assertSame(['+1234567890', '+9876543210'], $filter->getPhoneNumbers());

        // Try to add duplicate
        $filter->addPhoneNumber('+1234567890');
        $this->assertSame(['+1234567890', '+9876543210'], $filter->getPhoneNumbers());
    }

    public function testEventManagement(): void
    {
        $filter = new SubscriptionFilter();

        // Add single event
        $filter->addEvent('event1');
        $this->assertSame(['event1'], $filter->getEvents());

        // Add another unique event
        $filter->addEvent('event2');
        $this->assertSame(['event1', 'event2'], $filter->getEvents());

        // Try to add duplicate
        $filter->addEvent('event1');
        $this->assertSame(['event1', 'event2'], $filter->getEvents());
    }

    public function testOwnerIdManagement(): void
    {
        $filter = new SubscriptionFilter();

        $this->assertNull($filter->getOwnerId());

        $filter->setOwnerId('user123');
        $this->assertSame('user123', $filter->getOwnerId());

        $filter->setOwnerId('user456');
        $this->assertSame('user456', $filter->getOwnerId());
    }

    public function testMultipleFiltersTogether(): void
    {
        $pager = new Pager(2, 20);
        $filter = new SubscriptionFilter($pager);

        $filter->setSort(['created_at' => 'desc']);
        $filter->addPhoneNumber('+1111111111');
        $filter->addPhoneNumber('+2222222222');
        $filter->addEvent('notification_sent');
        $filter->addEvent('notification_read');
        $filter->setOwnerId('user789');

        $this->assertSame($pager, $filter->pager);
        $this->assertSame(['created_at' => 'desc'], $filter->getSort());
        $this->assertSame(['+1111111111', '+2222222222'], $filter->getPhoneNumbers());
        $this->assertSame(['notification_sent', 'notification_read'], $filter->getEvents());
        $this->assertSame('user789', $filter->getOwnerId());
    }
}
