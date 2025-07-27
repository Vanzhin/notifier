<?php

declare(strict_types=1);

namespace App\Tests\Notification\Application\UseCase\Query\FindChannel;

use App\Notification\Application\DTO\ChannelDTO;
use App\Notification\Application\DTO\ChannelDTOTransformer;
use App\Notification\Application\Service\AccessControl\ChannelAccessControl;
use App\Notification\Application\UseCase\Query\FindChannel\FindChannelQuery;
use App\Notification\Application\UseCase\Query\FindChannel\FindChannelQueryHandler;
use App\Notification\Application\UseCase\Query\FindChannel\FindChannelQueryResult;
use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Shared\Infrastructure\Exception\AppException;
use PHPUnit\Framework\TestCase;

class FindChannelQueryHandlerTest extends TestCase
{
    private FindChannelQueryHandler $handler;
    private ChannelRepositoryInterface $channelRepository;
    private ChannelDTOTransformer $transformer;
    private ChannelAccessControl $accessControl;

    protected function setUp(): void
    {
        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->transformer = $this->createMock(ChannelDTOTransformer::class);
        $this->accessControl = $this->createMock(ChannelAccessControl::class);

        $this->handler = new FindChannelQueryHandler(
            $this->channelRepository,
            $this->transformer,
            $this->accessControl
        );
    }

    public function testReturnsNullWhenChannelNotFound(): void
    {
        $query = new FindChannelQuery('non-existent-channel', 'user-123');

        $this->channelRepository->expects($this->once())
            ->method('findById')
            ->with($query->channelId)
            ->willReturn(null);

        $result = ($this->handler)($query);

        $this->assertInstanceOf(FindChannelQueryResult::class, $result);
        $this->assertNull($result->channel);
    }

    public function testThrowsWhenAccessDenied(): void
    {
        $query = new FindChannelQuery('channel-123', 'unauthorized-user');
        $channel = $this->createMock(Channel::class);

        $this->channelRepository->method('findById')->willReturn($channel);
        $this->accessControl->method('canViewChannel')->willReturn(false);

        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Channel is not allowed to view.');
        $this->expectExceptionCode(403);

        ($this->handler)($query);
    }

    public function testReturnsTransformedChannelWhenFound(): void
    {
        $query = new FindChannelQuery('channel-123', 'user-456');
        $channel = $this->createMock(Channel::class);
        $channelDTO = new ChannelDTO(
            id: 'channel-123',
            data: [],
            type: 'telegram',
            is_verified: true
        );

        $this->channelRepository->expects($this->once())
            ->method('findById')
            ->with($query->channelId)
            ->willReturn($channel);

        $this->accessControl->expects($this->once())
            ->method('canViewChannel')
            ->with($channel, $query->ownerId)
            ->willReturn(true);

        $this->transformer->expects($this->once())
            ->method('fromEntity')
            ->with($channel, ['subscriptions'])
            ->willReturn($channelDTO);

        $result = ($this->handler)($query);

        $this->assertInstanceOf(FindChannelQueryResult::class, $result);
        $this->assertEquals($channelDTO, $result->channel);
    }

    public function testDependenciesAreProperlyUsed(): void
    {
        $query = new FindChannelQuery('channel-123', 'user-456');
        $channel = $this->createMock(Channel::class);
        $channelDTO = $channelDTO = new ChannelDTO(
            id: 'channel-123',
            data: [],
            type: 'telegram',
            is_verified: true
        );

        $this->channelRepository->expects($this->once())
            ->method('findById')
            ->with($query->channelId)
            ->willReturn($channel);

        $this->accessControl->expects($this->once())
            ->method('canViewChannel')
            ->with($channel, $query->ownerId)
            ->willReturn(true);

        $this->transformer->expects($this->once())
            ->method('fromEntity')
            ->with($channel, ['subscriptions'])
            ->willReturn($channelDTO);

        $result = ($this->handler)($query);

        $this->assertEquals($channelDTO, $result->channel);
    }

    public function testReturnsEmptyResultForNullChannel(): void
    {
        $query = new FindChannelQuery('non-existent-channel', 'user-123');

        $this->channelRepository->method('findById')->willReturn(null);
        $this->accessControl->expects($this->never())->method('canViewChannel');
        $this->transformer->expects($this->never())->method('fromEntity');

        $result = ($this->handler)($query);

        $this->assertNull($result->channel);
    }
}
