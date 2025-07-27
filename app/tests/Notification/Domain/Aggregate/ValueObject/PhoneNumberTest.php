<?php

declare(strict_types=1);

namespace App\Tests\Notification\Domain\Aggregate\ValueObject;

use App\Notification\Domain\Aggregate\ValueObject\PhoneNumber;
use App\Shared\Infrastructure\Exception\AppException;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function testValidRussianPhoneNumber(): void
    {
        $phone = new PhoneNumber('89123456789');
        $this->assertEquals('79123456789', (string) $phone);
        $this->assertEquals('79123456789', $phone->getValue());
    }

    public function testValidInternationalPhoneNumber(): void
    {
        $phone = new PhoneNumber('1234567890123');
        $this->assertEquals('8101234567890123', (string) $phone);
    }

    public function testMinimumLengthPhoneNumber(): void
    {
        $phone = new PhoneNumber('12345678901');
        $this->assertEquals('12345678901', (string) $phone);
    }

    public function testMaximumLengthPhoneNumber(): void
    {
        $phone = new PhoneNumber('12345678901234567');
        $this->assertEquals('81012345678901234567', (string) $phone);
    }

    public function testInvalidShortPhoneNumber(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Incorrect phone number');
        new PhoneNumber('1234567890'); // 10 digits
    }

    public function testInvalidLongPhoneNumber(): void
    {
        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Incorrect phone number');
        new PhoneNumber('123456789012345678'); // 18 digits
    }

    public function testInvalidFormatPhoneNumber(): void
    {
        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Incorrect phone number');
        new PhoneNumber('+79123456789'); // contains non-digit character
    }

    public function testRussianPhoneNumberWith7Prefix(): void
    {
        $phone = new PhoneNumber('79123456789');
        $this->assertEquals('79123456789', (string) $phone);
    }

    public function testMNPhoneNumberTransformation(): void
    {
        $phone = new PhoneNumber('123456789012');
        $this->assertEquals('810123456789012', (string) $phone);
    }

    public function testStringableInterface(): void
    {
        $phone = new PhoneNumber('89123456789');
        $this->assertInstanceOf(\Stringable::class, $phone);
        $this->assertEquals('79123456789', (string) $phone);
    }
}
