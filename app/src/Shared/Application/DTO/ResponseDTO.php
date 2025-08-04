<?php

declare(strict_types=1);

namespace App\Shared\Application\DTO;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ResponseDTO',
    description: 'Standard API response format',
    required: ['result', 'status', 'data', 'message'],
)]
readonly class ResponseDTO implements \JsonSerializable
{

    public function __construct(
        #[OA\Property(enum: ['success', 'error'], example: 'success')]
        private string $result,
        #[OA\Property(format: 'int32', example: 200)]
        private int $status,
        #[OA\Property(
            type: 'object|array',
            nullable: true,
            additionalProperties: true
        )]
        private mixed $data,
        private ?string $message,
    ) {
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return array<string,string>
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
