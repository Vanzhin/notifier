<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Mapper;

use App\Notification\Domain\Aggregate\ValueObject\EventType;
use Symfony\Component\Validator\Constraints as Assert;

class SubscriptionMapper
{
    public function getValidationCollectionSubscription(): Assert\Collection
    {
        return new Assert\Collection([
            'phone_numbers' => new Assert\All([
                new Assert\NotBlank(),
                new Assert\Type('numeric'),
                new Assert\Length(min: 11, max: 17),
            ]),
            'events' => new Assert\All([
                new Assert\Choice(EventType::values(),
                    message: sprintf('Invalid event type. Allowed: %s.',
                        implode(', ', EventType::values())))
            ]),
//            'channels' => new Assert\Collection([
//                'telegram' => new Assert\Collection([
//                    'channel_id' => [
//                        new Assert\NotBlank(allowNull: true),
//                        new Assert\Type('string'),
//                    ]
//                ]),
//                'email' => new Assert\Collection([
//                    'email' => [
//                        new Assert\NotBlank(),
//                        new Assert\Email(),
//                    ]
//                ]),
//            ]),

        ],
            allowExtraFields: false);
    }
}
