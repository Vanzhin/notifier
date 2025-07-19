<?php

declare(strict_types=1);

namespace App\Notification\Domain\Service;

use App\Notification\Domain\Aggregate\PhoneNumber;
use App\Notification\Domain\Repository\PhoneRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final readonly class PhoneNumberOrganizer
{
    public function __construct(private PhoneRepositoryInterface $phoneRepository)
    {
    }

    /**
     * @throws \Exception
     */
    public function createPhoneIfNotExists(string $phone): PhoneNumber
    {
        $phone = new PhoneNumber(
            Uuid::v4(),
            new \App\Notification\Domain\Aggregate\ValueObject\PhoneNumber($phone));

        $exist = $this->phoneRepository->findByPhone($phone->getPhone()->getValue());
        if ($exist) {
            $phone = $exist;
        }

        return $phone;
    }
}
