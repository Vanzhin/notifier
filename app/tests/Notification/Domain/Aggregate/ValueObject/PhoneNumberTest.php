<?php

declare(strict_types=1);

namespace Tests\App\Notification\Domain\Aggregate\ValueObject;

use App\Notification\Domain\Aggregate\ValueObject\PhoneNumber;
use App\Shared\Infrastructure\Exception\AppException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    #[DataProvider('validPhoneNumbersProvider')]
    public function testValidPhoneNumbers(string $input, string $expectedValue, string $expectedE164): void
    {
        $phone = new PhoneNumber($input);

        $this->assertEquals($expectedValue, $phone->getValue());
        $this->assertEquals($expectedE164, $phone->getE164());
        $this->assertEquals($expectedValue, (string)$phone);
    }

    public static function validPhoneNumbersProvider(): array
    {
        return [
            ['8 (916) 123-45-67', '79161234567', '+79161234567'],
            ['+7 916 123 45 67', '79161234567', '+79161234567'],
            ['9161234567', '79161234567', '+79161234567'],
            ['+1 650-253-0000', '16502530000', '+16502530000'],
            ['88005553535', '78005553535', '+78005553535'],
        ];
    }

    #[DataProvider('phoneComponentsProvider')]
    public function testPhoneComponents(
        string $input,
        string $expectedCountryCode,
        string $expectedNationalNumber
    ): void {
        $phone = new PhoneNumber($input);

        $this->assertEquals($expectedCountryCode, $phone->getCountryCode());
        $this->assertEquals($expectedNationalNumber, $phone->getNationalNumber());
    }

    public static function phoneComponentsProvider(): array
    {
        return [
            ['8 (916) 123-45-67', '7', '9161234567'],
            ['+1 650-253-0000', '1', '6502530000'],
            ['+44 20 7123 4567', '44', '2071234567'],
        ];
    }

    public function testNationalFormat(): void
    {
        $phone = new PhoneNumber('8 (916) 123-45-67');
        $this->assertMatchesRegularExpression('/^8\s?\(?\d{3}\)?\s?\d{3}[\s-]?\d{2}[\s-]?\d{2}$/',
            $phone->getNationalFormat());
    }

    #[DataProvider('invalidPhoneNumbersProvider')]
    public function testInvalidPhoneNumbers(string $invalidPhone): void
    {
        $this->expectException(AppException::class);
        new PhoneNumber($invalidPhone);
    }

    public static function invalidPhoneNumbersProvider(): array
    {
        return [
            ['123'],
            ['+123'],
            ['8 (123) 456-78'],
            ['not a phone number'],
            ['+999 123 456 789'], // Несуществующий код страны
        ];
    }

    public function testEquals(): void
    {
        $phone1 = new PhoneNumber('8 (916) 123-45-67');
        $phone2 = new PhoneNumber('+7 916 123 45 67');
        $phone3 = new PhoneNumber('+1 650-253-0000');

        $this->assertTrue($phone1->equals($phone2));
        $this->assertFalse($phone1->equals($phone3));
    }

    public function testIsValidStaticMethod(): void
    {
        $this->assertTrue(PhoneNumber::isValid('8 (916) 123-45-67'));
        $this->assertTrue(PhoneNumber::isValid('+1 650-253-0000'));
        $this->assertFalse(PhoneNumber::isValid('123'));
        $this->assertFalse(PhoneNumber::isValid('not a phone'));
    }
}
