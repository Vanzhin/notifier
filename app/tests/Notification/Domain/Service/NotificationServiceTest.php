<?php

declare(strict_types=1);

namespace App\Tests\Notification\Domain\Service;

use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Message\Notification\NotificationMessage;
use App\Notification\Domain\Service\EmailMessageSender;
use App\Notification\Domain\Service\MessageSenderFactory;
use App\Notification\Domain\Service\MessageSenderInterface;
use App\Notification\Domain\Service\NotificationService;
use App\Notification\Domain\Service\TelegramMessageSender;
use PHPUnit\Framework\TestCase;

class NotificationServiceTest extends TestCase
{
    private NotificationService $notificationService;
    private MessageSenderInterface $telegramSender;
    private MessageSenderInterface $emailSender;

    protected function setUp(): void
    {
        $this->telegramSender = $this->createMock(TelegramMessageSender::class);
        $this->emailSender = $this->createMock(EmailMessageSender::class);

        // Create real MessageSenderFactory with mock senders
        $senderFactory = new MessageSenderFactory(
            $this->telegramSender,
            $this->emailSender
        );

        $this->notificationService = new NotificationService($senderFactory);
    }

    public function testSendMessageWithVerifiedTelegramChannel(): void
    {
        $channel = $this->createMock(Channel::class);
        $message = $this->createMock(NotificationMessage::class);

        $channel->method('isVerified')->willReturn(true);
        $channel->method('getType')->willReturn(ChannelType::TELEGRAM);

        $this->telegramSender->expects($this->once())
            ->method('send')
            ->with($channel, $message);

        $this->notificationService->sendMessage($channel, $message);
    }

    public function testSendMessageWithVerifiedEmailChannel(): void
    {
        $channel = $this->createMock(Channel::class);
        $message = $this->createMock(NotificationMessage::class);

        $channel->method('isVerified')->willReturn(true);
        $channel->method('getType')->willReturn(ChannelType::EMAIL);

        $this->emailSender->expects($this->once())
            ->method('send')
            ->with($channel, $message);

        $this->notificationService->sendMessage($channel, $message);
    }

    public function testSendMessageWithUnverifiedChannel(): void
    {
        $channel = $this->createMock(Channel::class);
        $message = $this->createMock(NotificationMessage::class);

        $channel->method('isVerified')->willReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Channel is not verified');

        $this->notificationService->sendMessage($channel, $message);
    }
}
