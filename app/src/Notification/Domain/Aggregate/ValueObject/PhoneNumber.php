<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate\ValueObject;

use App\Shared\Infrastructure\Exception\AppException;

class PhoneNumber implements \Stringable
{
    private const string MN_PREFIX = '810';

    private string $value;

    /**
     * @throws \Exception
     */
    public function __construct(string $value)
    {
        $this->assertValidName($value);
        $this->setPhone($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @throws AppException
     */
    private function assertValidName(string $value): void
    {
        if (!preg_match('/^\d{11,17}$/', $value)) {
            throw new AppException('Incorrect phone number');
        }
    }

    private function setPhone(string $value): void
    {
        // Для RU номеров заменим первую цифру на 7
        if (11 === strlen($value) && str_starts_with($value, '8')) {
            $value = preg_replace('/^8/', '7', $value);
        }
        if (strlen($value) > 11) {
            $value = self::MN_PREFIX.$value;
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
