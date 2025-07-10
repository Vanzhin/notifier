<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate;

use App\Shared\Domain\Aggregate\Aggregate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

final class PhoneNumber extends Aggregate implements \Stringable
{
    private const string MN_PREFIX = '810';

    private readonly string $phone;

    /**
     * @var Collection<Subscription>
     */
    private Collection $subscriptions {
        get {
            return $this->subscriptions;
        }
    }

    /**
     * @throws \Exception
     */
    public function __construct(
        private Uuid $id,
        string $value
    ) {
        $this->assertValidName($value);
        $this->setPhone($value);
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

    private function setPhone(string $value): void
    {
        // Для RU номеров заменим первую цифру на 7
        if (strlen($value) === 11 && str_starts_with($value, '8')) {
            $value = preg_replace('/^8/', '7', $value);;
        }
        if (strlen($value) > 11) {
            $value = self::MN_PREFIX . $value;
        }

        $this->phone = $value;
    }
}
