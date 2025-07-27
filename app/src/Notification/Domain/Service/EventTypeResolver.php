<?php

declare(strict_types=1);

namespace App\Notification\Domain\Service;

use App\Notification\Domain\Aggregate\ValueObject\EventType;

final readonly class EventTypeResolver
{
    public function resolve(EventType $eventType): string
    {
        return match ($eventType) {
            EventType::AVAILABLE => 'Доступен',
            EventType::MISSED_CALL => 'Пропущенный вызов',
            EventType::UNAVAILABLE => 'Не доступен',
            EventType::CHANNEL_VERIFICATION => 'Верификация канала'
        };
    }
}
