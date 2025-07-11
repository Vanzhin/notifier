<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use DomainException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;

class JwtValidatorService
{
    private string $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function validateAndGetPayload(string $jwt): array
    {
        try {
            return (array)JWT::decode(
                $jwt,
                new Key($this->secretKey, 'HS256') // Используем HMAC + SHA-256
            );
        } catch (
        SignatureInvalidException|
        BeforeValidException|
        ExpiredException|
        DomainException|
        InvalidArgumentException $e
        ) {
            throw new \RuntimeException('Invalid JWT: ' . $e->getMessage());
        }
    }

}
