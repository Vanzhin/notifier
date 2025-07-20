<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Mapper;

use App\Customers\Domain\Repository\CustomerRewardsFilter;
use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Repository\SubscriptionFilter;
use App\Shared\Domain\Repository\Pager;
use Symfony\Component\Validator\Constraints as Assert;

class SubscriptionMapper
{
    public function getValidationCollectionSubscription(): Assert\Collection
    {
        return new Assert\Collection(
            fields: [
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
            ],
            allowExtraFields: false);
    }

    public function getValidationCollectionSubscriptionFilter(): Assert\Collection
    {
        return new Assert\Collection(
            fields: [
                'page' => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Type('numeric'),
                    new Assert\Positive(),
                ]),
                'limit' => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Type('numeric'),
                    new Assert\Positive(),

                ]),
                'filters' => new Assert\Optional([
                    new Assert\NotBlank(),
                    new Assert\Type('array'),
                    new Assert\Collection(
                        fields: [
                            'phone_numbers' => new Assert\Optional(
                                new Assert\All(
                                    [
                                        new Assert\Type('string'),
                                        new Assert\Type('numeric'),
                                        new Assert\Length(max: 17),
                                    ]
                                )
                            ),
                            'owner_id' => new Assert\Optional(
                                [
                                    new Assert\Type('string'),
                                    new Assert\NotBlank(allowNull: true),
                                ]
                            ),
                            'events' => new Assert\Optional(
                                new Assert\All(
                                    [
                                        new Assert\Choice(
                                            EventType::values(),
                                            message: sprintf('Не верный тип события. Поддерживаются: %s.',
                                                implode(', ', EventType::values())))
                                    ]
                                )
                            ),
                        ]
                    ),
                ]),
                'sort' => new Assert\Optional(
                    [
                        new Assert\NotBlank(),
                        new Assert\Type('array'),
                        new Assert\Choice(choices: ['ASC', 'DESC'], multiple: true)
                    ]
                ),
            ],
            allowExtraFields: false);
    }

    public function buildFilter(array $payload): SubscriptionFilter
    {
        $filter = new SubscriptionFilter(Pager::fromPage(
            (int)$payload['page'] ?? null,
            (int)$payload['limit'] ?? null));

        if (isset($payload['sort'])) {
            $filter->setSort($payload['sort']);
        }
        if (isset($payload['filters'])) {
            if (isset($payload['filters']['phone_numbers'])) {
                foreach ($payload['filters']['phone_numbers'] as $phone_number) {
                    $filter->addPhoneNumber($phone_number);
                }
            }
            if (isset($payload['filters']['owner_id'])) {
                $filter->setOwnerId($payload['filters']['owner_id']);
            }
            if (isset($payload['filters']['events'])) {
                foreach ($payload['filters']['events'] as $event) {
                    $filter->addEvent($event);
                }
            }
        }

        return $filter;
    }
}
