<?php

declare(strict_types=1);

namespace App\Notification\Domain\Aggregate\ValueObject;

use App\Shared\Domain\Trait\EnumToArray;

enum EventType: string
{
    use EnumToArray;

    //Не доступен
    case UNAVAILABLE = 'unavailable';

    //Пропущенный
    case MISSED_CALL = 'missed_call';

    //Появился в сети
    case AVAILABLE = 'available';

    //верификация канала
    case CHANNEL_VERIFICATION = 'channel_verification';


}
