<?php

declare(strict_types=1);

namespace Notification\Application\DTO;

use App\Notification\Application\DTO\ChannelDTOTransformer;
use App\Notification\Application\DTO\SubscriptionDTO;
use App\Notification\Application\DTO\SubscriptionDTOTransformer;
use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class ChannelDTOTransformerTest extends TestCase
{
    private ChannelDTOTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new ChannelDTOTransformer();

        $this->subscriptionTransformer = $this->getMockBuilder(SubscriptionDTOTransformer::class)
            ->setConstructorArgs([$this->transformer])
            ->onlyMethods(['fromEntity'])
            ->getMock();
    }

    #[dataProvider('channelDataProvider')]
    public function testFromEntityBasic(Channel $channel): void
    {
        $dto = $this->transformer->fromEntity($channel);

        $this->assertEquals($channel->getId()->toString(), $dto->id);
        $this->assertEquals($channel->getData(), $dto->data);
        $this->assertEquals($channel->getType()->value, $dto->type);
    }

    #[dataProvider('channelWithSubscriptionDataProvider')]
    public function testFromEntityWithSubscriptions(Channel $channel): void
    {
        // Arrange
        $subscription = $channel->getSubscriptions()->first();

        // Act
        $dto = $this->transformer->fromEntity($channel, ['subscriptions']);

        // Assert
        $this->assertIsArray($dto->subscriptions);
        $this->assertCount(1, $dto->subscriptions);
        $this->assertInstanceOf(SubscriptionDTO::class, $dto->subscriptions[0]);
        $this->assertEquals($subscription->getId()->toString(), $dto->subscriptions[0]->id);
        $this->assertEquals($subscription->getSubscriberId(), $dto->subscriptions[0]->subscriber_id);
    }

    public static function channelDataProvider(): \Generator
    {
        yield 'case email' => [
            new Channel(
                Uuid::v4(),
                Uuid::v4()->toString(),
                ['email' => 'test@example.com'],
                ChannelType::EMAIL,
            )
        ];
        yield 'case telegram' => [
            new Channel(
                Uuid::v4(),
                Uuid::v4()->toString(),
                ['email' => 'test@example.com'],
                ChannelType::EMAIL,
            )
        ];
    }

    public static function channelWithSubscriptionDataProvider(): \Generator
    {
        $subscription = new Subscription(Uuid::v4(), Uuid::v4()->toString());
        $emailChannel = new Channel(
            Uuid::v4(),
            Uuid::v4()->toString(),
            ['email' => 'test@example.com'],
            ChannelType::EMAIL,
        );
        $emailChannel->addSubscription($subscription);

        $telegramChannel = new Channel(
            Uuid::v4(),
            Uuid::v4()->toString(),
            ['email' => 'test@example.com'],
            ChannelType::EMAIL,
        );
        $telegramChannel->addSubscription($subscription);
        yield 'case email' => [
            $emailChannel
        ];
        yield 'case telegram' => [
            $telegramChannel
        ];
    }
}
