<?php

declare(strict_types=1);

namespace App\Notification\Domain\Repository;

use App\Shared\Domain\Repository\Pager;

final class SubscriptionFilter
{
    //todo sort
    private array $sort = [];
    private array $phone_numbers = [];
    private array $events = [];
    private string $owner_id;

    public function __construct(public ?Pager $pager = null,) {
    }

    public function getSort(): array
    {
        return $this->sort;
    }

    public function setSort(array $sort): void
    {
        $this->sort = $sort;
    }

    public function getPhoneNumbers(): array
    {
        return $this->phone_numbers;
    }

    public function addPhoneNumber( $phone_numbers): void
    {
        $this->phone_numbers = $phone_numbers;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function setEvents(array $events): void
    {
        $this->events = $events;
    }

    public function getOwnerId(): string
    {
        return $this->owner_id;
    }

    public function setOwnerId(string $owner_id): void
    {
        $this->owner_id = $owner_id;
    }

}
