<?php

declare(strict_types=1);

namespace App\Tests\Notification\Application\UseCase\Command\RemoveChannel;

use App\Notification\Application\Service\AccessControl\ChannelAccessControl;
use App\Notification\Application\UseCase\Command\RemoveChannel\RemoveChannelCommand;
use App\Notification\Application\UseCase\Command\RemoveChannel\RemoveChannelCommandHandler;
use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Shared\Infrastructure\Exception\AppException;
use PHPUnit\Framework\TestCase;

class RemoveChannelCommandHandlerTest extends TestCase
{
    private RemoveChannelCommandHandler $handler;
    private ChannelRepositoryInterface $repository;
    private ChannelAccessControl $accessControl;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ChannelRepositoryInterface::class);
        $this->accessControl = $this->createMock(ChannelAccessControl::class);
        $this->handler = new RemoveChannelCommandHandler($this->repository, $this->accessControl);
    }

    public function testSuccessfullyRemovesChannel(): void
    {
        $channelId = 'channel-123';
        $ownerId = 'user-456';
        $command = new RemoveChannelCommand($channelId, $ownerId);

        $channel = $this->createMock(Channel::class);

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($channelId)
            ->willReturn($channel);

        $this->accessControl->expects($this->once())
            ->method('canViewChannel')
            ->with($channel, $ownerId)
            ->willReturn(true);

        $this->repository->expects($this->once())
            ->method('remove')
            ->with($channel);

        ($this->handler)($command);
    }

    public function testThrowsWhenChannelNotFound(): void
    {
        $channelId = 'non-existent-channel';
        $command = new RemoveChannelCommand($channelId, 'user-456');

        $this->repository->method('findById')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Channel with id "%s" not found.',
            $channelId
        ));

        ($this->handler)($command);
    }

    public function testThrowsWhenAccessDenied(): void
    {
        $channelId = 'channel-123';
        $unauthorizedUser = 'user-789';
        $command = new RemoveChannelCommand($channelId, $unauthorizedUser);

        $channel = $this->createMock(Channel::class);
        $this->repository->method('findById')->willReturn($channel);
        $this->accessControl->method('canViewChannel')->willReturn(false);

        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Channel is not allowed to view.');
        $this->expectExceptionCode(403);

        ($this->handler)($command);
    }

    public function testDependenciesAreProperlyUsed(): void
    {
        $channelId = 'channel-123';
        $ownerId = 'user-456';
        $command = new RemoveChannelCommand($channelId, $ownerId);

        $channel = $this->createMock(Channel::class);

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($channelId)
            ->willReturn($channel);

        $this->accessControl->expects($this->once())
            ->method('canViewChannel')
            ->with($channel, $ownerId)
            ->willReturn(true);

        $this->repository->expects($this->once())
            ->method('remove')
            ->with($channel);

        ($this->handler)($command);
    }

    public function testChannelIdInErrorMessage(): void
    {
        $channelId = 'specific-channel-id';
        $command = new RemoveChannelCommand($channelId, 'user-456');

        $this->repository->method('findById')->willReturn(null);

        try {
            ($this->handler)($command);
            $this->fail('Expected exception was not thrown');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString($channelId, $e->getMessage());
        }
    }
}
