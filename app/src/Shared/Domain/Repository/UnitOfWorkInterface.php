<?php

declare(strict_types=1);

namespace App\Shared\Domain\Repository;

interface UnitOfWorkInterface
{
    public function clear(): void;
}
