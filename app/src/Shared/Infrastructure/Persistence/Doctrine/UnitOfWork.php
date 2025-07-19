<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine;

use App\Shared\Domain\Repository\UnitOfWorkInterface;
use Doctrine\ORM\EntityManagerInterface;

readonly class UnitOfWork implements UnitOfWorkInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function clear(): void
    {
        $this->entityManager->clear();
    }
}
