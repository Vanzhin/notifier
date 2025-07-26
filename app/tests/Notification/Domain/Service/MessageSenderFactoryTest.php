<?php

declare(strict_types=1);

namespace App\Tests\Notification\Domain\Service;

use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Service\EmailMessageSender;
use App\Notification\Domain\Service\MessageSenderFactory;
use App\Notification\Domain\Service\MessageSenderInterface;
use App\Notification\Domain\Service\TelegramMessageSender;
use PHPUnit\Framework\TestCase;

class MessageSenderFactoryTest extends TestCase
{
    private MessageSenderFactory $factory;
    private TelegramMessageSender $telegramSender;
    private EmailMessageSender $emailSender;

    protected function setUp(): void
    {
        $this->telegramSender = $this->createMock(TelegramMessageSender::class);
        $this->emailSender = $this->createMock(EmailMessageSender::class);
        $this->factory = new MessageSenderFactory(
            $this->telegramSender,
            $this->emailSender
        );
    }

    public function testGetTelegramSender(): void
    {
        $channel = $this->createMock(Channel::class);
        $channel->method('getType')->willReturn(ChannelType::TELEGRAM);

        $sender = $this->factory->getSender($channel);

        $this->assertInstanceOf(MessageSenderInterface::class, $sender);
        $this->assertSame($this->telegramSender, $sender);
    }

    public function testGetEmailSender(): void
    {
        $channel = $this->createMock(Channel::class);
        $channel->method('getType')->willReturn(ChannelType::EMAIL);

        $sender = $this->factory->getSender($channel);

        $this->assertInstanceOf(MessageSenderInterface::class, $sender);
        $this->assertSame($this->emailSender, $sender);
    }

    public function testAllSendersImplementInterface(): void
    {
        $this->assertInstanceOf(
            MessageSenderInterface::class,
            $this->telegramSender,
            'Telegram sender must implement MessageSenderInterface'
        );

        $this->assertInstanceOf(
            MessageSenderInterface::class,
            $this->emailSender,
            'Email sender must implement MessageSenderInterface'
        );
    }
}
