<?php

declare(strict_types=1);

namespace App\Tests\Notification\Application\UseCase\Command\AddChannelToSubscription;

use App\Notification\Application\Service\AccessControl\SubscriptionAccessControl;
use App\Notification\Application\UseCase\Command\AddChannelToSubscription\AddChannelToSubscriptionCommand;
use App\Notification\Application\UseCase\Command\AddChannelToSubscription\AddChannelToSubscriptionCommandHandler;
use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Infrastructure\Exception\AppException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class AddChannelToSubscriptionCommandHandlerTest extends TestCase
{
    private AddChannelToSubscriptionCommandHandler $handler;
    private ChannelRepositoryInterface $channelRepository;
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private SubscriptionAccessControl $accessControl;

    protected function setUp(): void
    {
        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->subscriptionRepository = $this->createMock(SubscriptionRepositoryInterface::class);
        $this->accessControl = $this->createMock(SubscriptionAccessControl::class);

        $this->handler = new AddChannelToSubscriptionCommandHandler(
            $this->channelRepository,
            $this->accessControl,
            $this->subscriptionRepository
        );
    }

    /**
     * @throws Exception
     * @throws AppException
     */
    public function testSuccessfullyAddsChannelToSubscription(): void
    {
        $channel = $this->createMock(Channel::class);
        $subscription = $this->createMock(Subscription::class);
        $command = new AddChannelToSubscriptionCommand(
            'channel-id',
            'subscription-id',
            'user-id'
        );

        $this->channelRepository->expects($this->once())
            ->method('findById')
            ->with($command->channelId)
            ->willReturn($channel);

        $this->subscriptionRepository->expects($this->once())
            ->method('findById')
            ->with($command->subscriptionId)
            ->willReturn($subscription);

        $this->accessControl->expects($this->once())
            ->method('canAddChannelToSubscription')
            ->with($channel, $subscription, $command->userId)
            ->willReturn(true);

        $subscription->expects($this->once())
            ->method('addChannel')
            ->with($channel);

        $this->subscriptionRepository->expects($this->once())
            ->method('save')
            ->with($subscription);

        ($this->handler)($command);
    }

    public function testThrowsWhenChannelNotFound(): void
    {
        $command = new AddChannelToSubscriptionCommand(
            'non-existent-channel',
            'subscription-id',
            'user-id'
        );

        $this->channelRepository->method('findById')->willReturn(null);
        $this->subscriptionRepository->method('findById')->willReturn($this->createMock(Subscription::class));

        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Channel or subscription not found.');

        ($this->handler)($command);
    }

    public function testThrowsWhenSubscriptionNotFound(): void
    {
        $command = new AddChannelToSubscriptionCommand(
            'channel-id',
            'non-existent-subscription',
            'user-id'
        );

        $this->channelRepository->method('findById')->willReturn($this->createMock(Channel::class));
        $this->subscriptionRepository->method('findById')->willReturn(null);

        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Channel or subscription not found.');

        ($this->handler)($command);
    }

    public function testThrowsWhenAccessDenied(): void
    {
        $channel = $this->createMock(Channel::class);
        $subscription = $this->createMock(Subscription::class);
        $command = new AddChannelToSubscriptionCommand(
            'channel-id',
            'subscription-id',
            'unauthorized-user'
        );

        $this->channelRepository->method('findById')->willReturn($channel);
        $this->subscriptionRepository->method('findById')->willReturn($subscription);

        $this->accessControl->method('canAddChannelToSubscription')
            ->willReturn(false);

        $this->expectException(AppException::class);
        $this->expectExceptionMessage('You are not allowed to do this action.');
        $this->expectExceptionCode(403);

        ($this->handler)($command);
    }
}
