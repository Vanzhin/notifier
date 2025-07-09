<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate;

use App\Shared\Domain\Aggregate\Aggregate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

final class PhoneNumber extends Aggregate implements \Stringable
{
    private readonly string $phone;

    /**
     * @var Collection<Subscription>
     */
    private Collection $subscriptions {
        get {
            return $this->subscriptions;
        }
    }

    public function __construct(
        private Uuid $id,
        string $value
    ) {
        $this->assertValidName($value);
        $this->phone = $value;
        $this->subscriptions = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function __toString(): string
    {
        return $this->phone;
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

    private function assertValidName(string $value): void
    {
        if (!preg_match('/^\d{11,17}$/', $value)) {
            throw new \Exception('Incorrect phone number');
        }
    }
}
