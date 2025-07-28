<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate\ValueObject;

use App\Shared\Infrastructure\Exception\AppException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneNumber implements \Stringable
{
    private const string DEFAULT_REGION = 'RU';
    private string $value; // Хранится без + (только цифры: код страны + номер)
    private PhoneNumberUtil $phoneNumberUtil;

    /**
     * @throws AppException
     */
    public function __construct(string $value)
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
        $this->value = $this->normalizePhoneNumber($value);
    }

    /**
     * @throws AppException
     */
    private function normalizePhoneNumber(string $value): string
    {
        $cleaned = preg_replace('/[^\d+]/', '', $value);

        try {
            $parsed = $this->phoneNumberUtil->parse($cleaned, static::DEFAULT_REGION);

            if (!$this->phoneNumberUtil->isValidNumber($parsed)) {
                throw new AppException('Invalid phone number');
            }

            // Возвращаем без +, только цифры
            return $parsed->getCountryCode() . $parsed->getNationalNumber();
        } catch (NumberParseException $e) {
            throw new AppException('Failed to parse phone number: ' . $e->getMessage());
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getCountryCode(): string
    {
        return $this->parseComponents()['countryCode'];
    }

    public function getNationalNumber(): string
    {
        return $this->parseComponents()['nationalNumber'];
    }

    private function parseComponents(): array
    {
        try {
            $parsed = $this->phoneNumberUtil->parse('+' . $this->value, null);
            return [
                'countryCode' => (string)$parsed->getCountryCode(),
                'nationalNumber' => (string)$parsed->getNationalNumber()
            ];
        } catch (NumberParseException) {
            // Fallback для некорректных номеров
            return [
                'countryCode' => substr($this->value, 0, 1),
                'nationalNumber' => substr($this->value, 1)
            ];
        }
    }

    public function getE164(): string
    {
        return '+' . $this->value;
    }

    public function getNationalFormat(): string
    {
        try {
            $parsed = $this->phoneNumberUtil->parse($this->getE164(), static::DEFAULT_REGION);
            return $this->phoneNumberUtil->format($parsed, PhoneNumberFormat::NATIONAL);
        } catch (NumberParseException) {
            return $this->value;
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public static function isValid(string $phone): bool
    {
        try {
            $util = PhoneNumberUtil::getInstance();
            $parsed = $util->parse($phone, static::DEFAULT_REGION);
            return $util->isValidNumber($parsed);
        } catch (NumberParseException) {
            return false;
        }
    }
}
