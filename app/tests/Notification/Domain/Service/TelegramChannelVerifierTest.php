<?php

declare(strict_types=1);

namespace App\Tests\Notification\Domain\Service;

use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Notification\Domain\Service\TelegramChannelVerifier;
use App\Shared\Domain\Service\TokenService;
use App\Shared\Infrastructure\Exception\AppException;
use PHPUnit\Framework\TestCase;
use Random\Randomizer;

class TelegramChannelVerifierTest extends TestCase
{
    private TelegramChannelVerifier $verifier;
    private ChannelRepositoryInterface $channelRepository;
    private string $botName = 'test_bot';

    protected function setUp(): void
    {
        // Create a real TokenService instance with predictable output
        $this->tokenService = new TokenService(new Randomizer());

        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->verifier = new TelegramChannelVerifier(
            $this->tokenService,
            $this->channelRepository,
            $this->botName
        );
    }

    public function testInitiateChannelVerificationGeneratesValidUrl(): void
    {
        $channel = $this->createMock(Channel::class);

        $this->channelRepository->expects($this->once())
            ->method('save')
            ->with($channel);

        $result = $this->verifier->initiateChannelVerification($channel);

        $this->assertStringStartsWith("https://t.me/{$this->botName}?start=", $result);
        $this->assertEquals(32 * 2, strlen(parse_url($result, PHP_URL_QUERY)) - 6);
    }

    public function testSuccessfulVerification(): void
    {
        $channel = $this->createMock(Channel::class);
        $secret = 'test_secret_12345678901234567890';

        $channel->expects($this->once())
            ->method('verify')
            ->with($secret)
            ->willReturn(true);

        $this->channelRepository->expects($this->once())
            ->method('save')
            ->with($channel);

        $this->verifier->verify($channel, $secret);
    }

    public function testFailedVerificationThrowsException(): void
    {
        $channel = $this->createMock(Channel::class);
        $secret = 'invalid_secret';

        $channel->expects($this->once())
            ->method('verify')
            ->with($secret)
            ->willReturn(false);

        $this->expectException(AppException::class);
        $this->expectExceptionMessage('The channel verification data is invalid.');

        $this->verifier->verify($channel, $secret);
    }

    public function testSupportsOnlyTelegramChannels(): void
    {
        $telegramChannel = $this->createMock(Channel::class);
        $telegramChannel->method('getType')->willReturn(ChannelType::TELEGRAM);

        $emailChannel = $this->createMock(Channel::class);
        $emailChannel->method('getType')->willReturn(ChannelType::EMAIL);

        $this->assertTrue($this->verifier->supports($telegramChannel));
        $this->assertFalse($this->verifier->supports($emailChannel));
    }

    public function testBotNameIsUsedInVerificationUrl(): void
    {
        $customBotName = 'custom_bot';
        $verifier = new TelegramChannelVerifier(
            $this->tokenService,
            $this->channelRepository,
            $customBotName
        );

        $channel = $this->createMock(Channel::class);
        $result = $verifier->initiateChannelVerification($channel);

        $this->assertStringContainsString($customBotName, $result);
    }
}
