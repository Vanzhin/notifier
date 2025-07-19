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
    private ?string $owner_id = null;

    public function __construct(public ?Pager $pager = null)
    {
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

    public function addPhoneNumber(string $phone_number): void
    {
        if (!in_array($phone_number, $this->phone_numbers, true)) {
            $this->phone_numbers[] = $phone_number;
        }
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function addEvent(string $event): void
    {
        if (!in_array($event, $this->events, true)) {
            $this->events[] = $event;
        }
    }

    public function getOwnerId(): ?string
    {
        return $this->owner_id;
    }

    public function setOwnerId(string $owner_id): void
    {
        $this->owner_id = $owner_id;
    }

}
