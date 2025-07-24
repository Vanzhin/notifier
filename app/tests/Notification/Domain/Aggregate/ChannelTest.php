<?php

declare(strict_types=1);

namespace App\Tests\Notification\Domain\Aggregate;

use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Shared\Infrastructure\Exception\AppException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class ChannelTest extends TestCase
{
    private Channel $channel;
    private Uuid $id;
    private string $ownerId = 'user123';
    private array $data = ['key' => 'value'];

    protected function setUp(): void
    {
        $this->id = Uuid::v4();
        $this->channel = new Channel(
            $this->id,
            $this->ownerId,
            $this->data,
            ChannelType::TELEGRAM
        );
    }

    public function testInitialState(): void
    {
        $this->assertEquals($this->id, $this->channel->getId());
        $this->assertEquals($this->ownerId, $this->channel->getOwnerId());
        $this->assertEquals($this->data, $this->channel->getData());
        $this->assertEquals(ChannelType::TELEGRAM, $this->channel->getType());
        $this->assertNull($this->channel->getChannel());
        $this->assertFalse($this->channel->isVerified());
        $this->assertEmpty($this->channel->getSubscriptions());
    }

    public function testSetSecret(): void
    {
        $secret = 'test-secret';
        $this->channel->setSecret($secret);
        $this->channel->setChannel('telegram');

        $this->assertFalse($this->channel->verify('wrong-secret'));
        $this->assertTrue($this->channel->verify($secret));
    }

    public function testCannotSetSecretWhenVerified(): void
    {
        $this->channel->setSecret('initial-secret');
        $this->channel->setChannel('1234567890');
        $this->channel->verify('initial-secret');

        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Нельзя установить секрет для верифицированного канала.');
        $this->channel->setSecret('new-secret');
    }

    public function testVerifyChannel(): void
    {
        $channelValue = '1234567890';
        $secret = 'verification-code';

        $this->channel->setSecret($secret);
        $this->channel->setChannel($channelValue);

        $this->assertTrue($this->channel->verify($secret));
        $this->assertTrue($this->channel->isVerified());
    }

    public function testVerifyFailsWithWrongSecret(): void
    {
        $secret = 'correct-secret';
        $this->channel->setSecret($secret);
        $this->channel->setChannel('1234567890');

        $this->assertFalse($this->channel->verify('wrong-secret'));
        $this->assertFalse($this->channel->isVerified());
    }

    public function testVerifyFailsWithoutChannel(): void
    {
        $secret = 'test-secret';
        $this->channel->setSecret($secret);

        $this->assertFalse($this->channel->verify($secret));
    }

    public function testSetChannel(): void
    {
        $channelValue = '1234567890';
        $this->channel->setChannel($channelValue);

        $this->assertEquals($channelValue, $this->channel->getChannel());
    }

    public function testCannotSetChannelWhenVerified(): void
    {
        $this->channel->setSecret('secret');
        $this->channel->setChannel('1234567890');
        $this->channel->verify('secret');

        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Channel is already verified, cannot change channel.');
        $this->channel->setChannel('new-channel');
    }

    public function testCannotSetChannelTwice(): void
    {
        $this->channel->setChannel('1234567890');

        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Channel is already has channel.');
        $this->channel->setChannel('9876543210');
    }

    public function testOwnershipCheck(): void
    {
        $this->assertTrue($this->channel->isOwnedBy($this->ownerId));
        $this->assertFalse($this->channel->isOwnedBy('other-user'));
    }

    public function testSubscriptionManagement(): void
    {
        $subscription = $this->createMock(\App\Notification\Domain\Aggregate\Subscription::class);

        $this->channel->addSubscription($subscription);
        $this->assertCount(1, $this->channel->getSubscriptions());
        $this->assertTrue($this->channel->getSubscriptions()->contains($subscription));

        $this->channel->removeSubscription($subscription);
        $this->assertCount(0, $this->channel->getSubscriptions());
    }
}
