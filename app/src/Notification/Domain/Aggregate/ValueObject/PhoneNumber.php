<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate\ValueObject;

class PhoneNumber implements \Stringable
{
    private string $value;

    public function __construct(string $value)
    {
        $this->assertValidName($value);
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    private function assertValidName(string $value): void
    {
        if (!preg_match('/^\d{11,17}$/', $value)) {
            throw new \Exception('Incorrect phone number');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
