<?php

declare(strict_types=1);

namespace App\Tests\Notification\Application\UseCase\Command\CreateChannel;

use App\Notification\Application\UseCase\Command\CreateChannel\CreateChannelCommand;
use App\Notification\Application\UseCase\Command\CreateChannel\CreateChannelCommandHandler;
use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class CreateChannelCommandHandlerTest extends TestCase
{
    private CreateChannelCommandHandler $handler;
    private ChannelRepositoryInterface $channelRepository;

    protected function setUp(): void
    {
        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->handler = new CreateChannelCommandHandler($this->channelRepository);
    }

    public function testCreatesAndSavesChannel(): void
    {
        $command = new CreateChannelCommand(
            ownerId: 'user-123',
            type: 'telegram',
            data: ['key' => 'value'],
            channel: '@test_channel'
        );

        // Capture the channel that will be saved
        $savedChannel = null;
        $this->channelRepository->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Channel $channel) use (&$savedChannel) {
                $savedChannel = $channel;
            });

        $result = ($this->handler)($command);

        // Verify the returned ID is a valid UUID string
        $this->assertTrue(Uuid::isValid($result));

        // Verify channel properties
        $this->assertInstanceOf(Channel::class, $savedChannel);
        $this->assertEquals('user-123', $savedChannel->getOwnerId());
        $this->assertEquals(['key' => 'value'], $savedChannel->getData());
        $this->assertEquals(ChannelType::TELEGRAM, $savedChannel->getType());
        $this->assertEquals('@test_channel', $savedChannel->getChannel());
    }

    public function testCreatesTelegramChannel(): void
    {
        $this->assertChannelTypeCreation('telegram', ChannelType::TELEGRAM);
    }

    public function testCreatesEmailChannel(): void
    {
        $this->assertChannelTypeCreation('email', ChannelType::EMAIL);
    }

    private function assertChannelTypeCreation(string $typeString, ChannelType $expectedType): void
    {
        $command = new CreateChannelCommand(
            ownerId: 'user-123',
            type: $typeString,
            data: [],
            channel: 'test-value'
        );

        $this->channelRepository->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Channel $channel) use ($expectedType) {
                $this->assertEquals($expectedType, $channel->getType());
            });

        ($this->handler)($command);
    }

    public function testReturnsChannelId(): void
    {
        $command = new CreateChannelCommand(
            ownerId: 'user-123',
            type: 'telegram',
            data: [],
            channel: null
        );

        $generatedId = null;
        $this->channelRepository->method('save')
            ->willReturnCallback(function (Channel $channel) use (&$generatedId) {
                $generatedId = $channel->getId()->toString();
            });

        $result = ($this->handler)($command);

        $this->assertEquals($generatedId, $result);
        $this->assertTrue(Uuid::isValid($result));
    }

    public function testHandlesOptionalChannelField(): void
    {
        $command = new CreateChannelCommand(
            ownerId: 'user-123',
            type: 'email',
            data: [],
            channel: null
        );

        $this->channelRepository->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Channel $channel) {
                $this->assertNull($channel->getChannel());
            });

        ($this->handler)($command);
    }
}
