<?php

declare(strict_types=1);

namespace App\Notification\Domain\Repository;

use App\Notification\Domain\Aggregate\PhoneNumber;

interface PhoneRepositoryInterface
{
    public function findByPhone(string $phone): ?PhoneNumber;

}
