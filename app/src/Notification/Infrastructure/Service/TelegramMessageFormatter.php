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
        return match ($message->event_type) {
            EventType::CHANNEL_VERIFICATION => sprintf(
                "🔔 Уведомление\n\n🎯 Событие: %s\n📌 Дополнительно: %s\n",
                $message->message,
                implode(', ', $message->extra)),
            default => sprintf(
                "🔔 Уведомление\n\n📞 Номер: %s\n🎯 Событие: %s\n📌 Дополнительно: %s\n",
                $message->phone_number->getNationalFormat(),
                $this->eventTypeResolver->resolve($message->event_type),
                implode(', ', $message->extra)),
        };
    }
}
