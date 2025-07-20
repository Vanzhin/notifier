<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Database\ORM\Type;

use App\Notification\Domain\Aggregate\ValueObject\EventType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class EventTypeCollectionType extends Type
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string|false
    {
        return json_encode(array_map(
            fn(EventType $eventType) => $eventType->value,
            $value
        ));
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): array
    {
        $data = json_decode($value, true);
        return array_map(
            fn(string $type) => EventType::from($type),
            $data ?? []
        );
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'JSON';
    }

    public function getName()
    {
        return 'event_type_collection';
    }
}
