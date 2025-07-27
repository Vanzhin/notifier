<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Scheduler;

use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Message\PhoneNumberExternalMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule]
class PhoneNumberEventScheduler implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        $type = $this->getRandomEventType();

        return new Schedule()
            ->add(
                RecurringMessage::cron('*/30 * * * *',
                    new PhoneNumberExternalMessage(
                        '79111111111',
                        $type->value,
                        EventType::MISSED_CALL === $type ? ['с номера 792222222222'] : []
                    ),
                    new \DateTimeZone('Asia/Yekaterinburg')));
    }

    private function getRandomEventType(): EventType
    {
        $cases = EventType::cases();

        return $cases[array_rand($cases)];
    }
}
