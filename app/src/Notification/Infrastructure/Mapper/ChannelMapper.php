<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Mapper;

use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use Symfony\Component\Validator\Constraints as Assert;

class ChannelMapper
{
    public function getValidationCollectionChannel(array $data): Assert\Collection
    {
        return new Assert\Collection([
            'type' => new Assert\Choice(ChannelType::values(),
                message: sprintf('Неверный тип канала. Поддерживаются: %s.',
                    implode(', ', ChannelType::values()))),
            'data' => new Assert\Required(
                $this->getDataValidation($data['type'] ?? ''))
        ],
            allowExtraFields: false);
    }

    private function getDataValidation(string $channelType): Assert\Collection
    {
        $constrains = match ($channelType) {
            'telegram' => [
                'channel_id' => [
                    new Assert\NotBlank(allowNull: true),
                    new Assert\Type('string'),
                ]
            ],
            'email' => [
                'email' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ]
            ],
            default => []
        };

        return new Assert\Collection($constrains);
    }
}
