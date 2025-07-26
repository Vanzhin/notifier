<?php

declare(strict_types=1);

namespace App\Tests\Notification\Domain\Service;

use App\Notification\Domain\Aggregate\PhoneNumber;
use App\Notification\Domain\Aggregate\ValueObject\PhoneNumber as PhoneValueObject;
use App\Notification\Domain\Repository\PhoneRepositoryInterface;
use App\Notification\Domain\Service\PhoneNumberOrganizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class PhoneNumberOrganizerTest extends TestCase
{
    private PhoneNumberOrganizer $organizer;
    private PhoneRepositoryInterface $phoneRepository;

    protected function setUp(): void
    {
        $this->phoneRepository = $this->createMock(PhoneRepositoryInterface::class);
        $this->organizer = new PhoneNumberOrganizer($this->phoneRepository);
    }

    public function testCreateNewPhoneNumberWhenNotExists(): void
    {
        $phoneValue = '79111111111';
        $phoneValueObject = new PhoneValueObject($phoneValue);

        $this->phoneRepository->expects($this->once())
            ->method('findByPhone')
            ->with($phoneValue)
            ->willReturn(null);

        $result = $this->organizer->createPhoneIfNotExists($phoneValue);

        $this->assertInstanceOf(PhoneNumber::class, $result);
        $this->assertEquals($phoneValueObject, $result->getPhone());
    }

    public function testReturnExistingPhoneNumberWhenExists(): void
    {
        $phoneValue = '79111111111';
        $existingPhone = new PhoneNumber(
            Uuid::v4(),
            new PhoneValueObject($phoneValue)
        );

        $this->phoneRepository->expects($this->once())
            ->method('findByPhone')
            ->with($phoneValue)
            ->willReturn($existingPhone);

        $result = $this->organizer->createPhoneIfNotExists($phoneValue);

        $this->assertSame($existingPhone, $result);
    }

    public function testGeneratedUuidForNewPhoneNumber(): void
    {
        $phoneValue = '79222222222';

        $this->phoneRepository->method('findByPhone')->willReturn(null);

        $result1 = $this->organizer->createPhoneIfNotExists($phoneValue);
        $result2 = $this->organizer->createPhoneIfNotExists($phoneValue);

        $this->assertNotEquals($result1->getId(), $result2->getId());
    }

    public function testPhoneNumberValidation(): void
    {
        $this->expectException(\Exception::class);

        $this->organizer->createPhoneIfNotExists('invalid-phone');
    }

}
