<?php

declare(strict_types=1);

namespace App\Notification\Domain\Service;

use App\Notification\Domain\Message\Notification\NotificationMessage;

interface NotificationMessageFormatterInterface
{
    public function format(NotificationMessage $message): string;
}
