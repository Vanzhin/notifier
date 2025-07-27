<?php

declare(strict_types=1);

namespace App\Tests\Notification\Application\UseCase\Command\InitiateChannelVerification;

use App\Notification\Application\Service\AccessControl\ChannelAccessControl;
use App\Notification\Application\UseCase\Command\InitiateChannelVerification\InitiateChannelVerificationCommand;
use App\Notification\Application\UseCase\Command\InitiateChannelVerification\InitiateChannelVerificationCommandHandler;
use App\Notification\Application\UseCase\Command\InitiateChannelVerification\InitiateChannelVerificationCommandResult;
use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Notification\Domain\Service\ChannelVerifierInterface;
use App\Shared\Infrastructure\Exception\AppException;
use PHPUnit\Framework\TestCase;

class InitiateChannelVerificationCommandHandlerTest extends TestCase
{
    private InitiateChannelVerificationCommandHandler $handler;
    private ChannelRepositoryInterface $repository;
    private ChannelAccessControl $accessControl;
    private array $strategies = [];

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ChannelRepositoryInterface::class);
        $this->accessControl = $this->createMock(ChannelAccessControl::class);

        // Create handler with empty strategies by default
        $this->handler = new InitiateChannelVerificationCommandHandler(
            $this->repository,
            $this->strategies,
            $this->accessControl
        );
    }

    public function testSuccessfulVerificationInitiation(): void
    {
        $channel = $this->createMock(Channel::class);
        $command = new InitiateChannelVerificationCommand('channel-id', 'user-id');
        $verificationUrl = 'https://verification.url';

        // Create a mock strategy that supports the channel
        $strategy = $this->createMock(ChannelVerifierInterface::class);
        $strategy->method('supports')->willReturn(true);
        $strategy->method('initiateChannelVerification')->willReturn($verificationUrl);

        $this->strategies = [$strategy];
        $this->handler = new InitiateChannelVerificationCommandHandler(
            $this->repository,
            $this->strategies,
            $this->accessControl
        );

        $this->repository->method('findById')->willReturn($channel);
        $this->accessControl->method('canViewChannel')->willReturn(true);

        $result = ($this->handler)($command);

        $this->assertInstanceOf(InitiateChannelVerificationCommandResult::class, $result);
        $this->assertEquals($verificationUrl, $result->verification_data);
    }

    public function testThrowsWhenChannelNotFound(): void
    {
        $command = new InitiateChannelVerificationCommand('non-existent-channel', 'user-id');

        $this->repository->method('findById')->willReturn(null);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Channel not found.');

        ($this->handler)($command);
    }

    public function testThrowsWhenAccessDenied(): void
    {
        $channel = $this->createMock(Channel::class);
        $command = new InitiateChannelVerificationCommand('channel-id', 'unauthorized-user');

        $this->repository->method('findById')->willReturn($channel);
        $this->accessControl->method('canViewChannel')->willReturn(false);

        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Channel is not allowed to initiate verification.');
        $this->expectExceptionCode(403);

        ($this->handler)($command);
    }

    public function testReturnsEmptyResultWhenNoStrategySupportsChannel(): void
    {
        $channel = $this->createMock(Channel::class);
        $command = new InitiateChannelVerificationCommand('channel-id', 'user-id');

        // Create a strategy that doesn't support the channel
        $strategy = $this->createMock(ChannelVerifierInterface::class);
        $strategy->method('supports')->willReturn(false);

        $this->strategies = [$strategy];
        $this->handler = new InitiateChannelVerificationCommandHandler(
            $this->repository,
            $this->strategies,
            $this->accessControl
        );

        $this->repository->method('findById')->willReturn($channel);
        $this->accessControl->method('canViewChannel')->willReturn(true);

        $result = ($this->handler)($command);

        $this->assertInstanceOf(InitiateChannelVerificationCommandResult::class, $result);
        $this->assertNull($result->verification);
    }

    public function testUsesFirstSupportedStrategy(): void
    {
        $channel = $this->createMock(Channel::class);
        $command = new InitiateChannelVerificationCommand('channel-id', 'user-id');
        $verificationUrl = 'https://first.strategy.url';

        // Create multiple strategies, first one should be used
        $firstStrategy = $this->createMock(ChannelVerifierInterface::class);
        $firstStrategy->method('supports')->willReturn(true);
        $firstStrategy->method('initiateChannelVerification')->willReturn($verificationUrl);

        $secondStrategy = $this->createMock(ChannelVerifierInterface::class);
        $secondStrategy->expects($this->never())->method('supports');

        $this->strategies = [$firstStrategy, $secondStrategy];
        $this->handler = new InitiateChannelVerificationCommandHandler(
            $this->repository,
            $this->strategies,
            $this->accessControl
        );

        $this->repository->method('findById')->willReturn($channel);
        $this->accessControl->method('canViewChannel')->willReturn(true);

        $result = ($this->handler)($command);

        $this->assertEquals($verificationUrl, $result->verification_data);
    }

    public function testDependenciesAreProperlyUsed(): void
    {
        $channel = $this->createMock(Channel::class);
        $command = new InitiateChannelVerificationCommand('channel-id', 'user-id');
        $verificationUrl = 'https://verification.url';

        $strategy = $this->createMock(ChannelVerifierInterface::class);
        $strategy->method('supports')->willReturn(true);
        $strategy->method('initiateChannelVerification')->willReturn($verificationUrl);

        $this->strategies = [$strategy];
        $this->handler = new InitiateChannelVerificationCommandHandler(
            $this->repository,
            $this->strategies,
            $this->accessControl
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($command->channelId)
            ->willReturn($channel);

        $this->accessControl->expects($this->once())
            ->method('canViewChannel')
            ->with($channel, $command->userId)
            ->willReturn(true);

        $result = ($this->handler)($command);

        $this->assertEquals($verificationUrl, $result->verification_data);
    }
}
