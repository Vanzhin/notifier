<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Service;

use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Message\Notification\NotificationMessage;
use App\Notification\Domain\Service\EventTypeResolver;
use App\Notification\Domain\Service\NotificationMessageFormatterInterface;

final readonly class TelegramMessageFormatter implements NotificationMessageFormatterInterface
{
    public function __construct(private EventTypeResolver $eventTypeResolver)
    {
    }

    public function format(NotificationMessage $message): string
    {
        return sprintf(
            "🔔 Уведомление\n\n📞 Номер: %s\n🎯 Событие: %s\n📌 Дополнительно: %s\n",
            $message->phone_number,
            $this->eventTypeResolver->resolve(EventType::from($message->event_type)),
            implode(', ', $message->extra)
        );
    }

}
