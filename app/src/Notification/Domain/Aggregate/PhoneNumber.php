<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate;

use App\Notification\Domain\Aggregate\ValueObject\PhoneNumber as Phone;
use App\Shared\Domain\Aggregate\Aggregate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

class PhoneNumber extends Aggregate implements \Stringable
{
    /**
     * @var Collection<Subscription>
     */
    private Collection $subscriptions;

    /**
     * @throws \Exception
     */
    public function __construct(
        private Uuid $id,
        private readonly Phone $phone,
    ) {
        $this->subscriptions = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getPhone(): Phone
    {
        return $this->phone;
    }

    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function __toString(): string
    {
        return $this->phone->__toString();
    }

    public function addSubscription(Subscription $subscription): void
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
        }
    }

    public function removeSubscription(Subscription $subscription): void
    {
        $this->subscriptions->removeElement($subscription);
    }
}
