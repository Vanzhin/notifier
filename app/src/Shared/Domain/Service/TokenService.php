<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use Random\Randomizer;

final readonly class TokenService
{
    private const int LENGHT = 32;

    public function __construct(private Randomizer $randomizer)
    {
    }

    public function generate(int $length = self::LENGHT): string
    {
        $length = $length > 1 ? $length : self::LENGHT;

        return bin2hex($this->randomizer->getBytes($length));
    }
}
