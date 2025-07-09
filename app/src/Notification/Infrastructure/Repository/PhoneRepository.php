<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Repository;

use App\Notification\Domain\Aggregate\PhoneNumber;
use App\Notification\Domain\Repository\PhoneRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class PhoneRepository extends ServiceEntityRepository implements PhoneRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PhoneNumber::class);
    }

    public function findByPhone(string $phone): ?PhoneNumber
    {
        return $this->findOneBy(['phone' => $phone]);
    }
}
