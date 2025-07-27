<?php

declare(strict_types=1);

namespace App\Tests\Integration\Notification\Infrastructure\Repository;

use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Tests\Tools\DITools;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

class ChannelRepositoryTest extends KernelTestCase
{
    use DITools;

    private ChannelRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getService(ChannelRepositoryInterface::class);

        // Начинаем транзакцию для каждого теста
        $this->repository->getEntityManager()->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Откатываем транзакцию после каждого теста
        if ($this->repository->getEntityManager()->getConnection()->isTransactionActive()) {
            $this->repository->getEntityManager()->getConnection()->rollBack();
        }

        parent::tearDown();

        // Очищаем менеджер сущностей
        $this->repository->getEntityManager()->clear();
    }

    #[DataProvider('channelDataProvider')]
    public function testSaveAndFindChannel(Channel $channel): void
    {
        // 2. Сохраняем канал
        $this->repository->save($channel);

        // 3. Ищем канал
        $foundChannel = $this->repository->findById($channel->getId()->toString());

        // 4. Проверяем результаты
        $this->assertNotNull($foundChannel);
        $this->assertEquals($channel->getId(), $foundChannel->getId());
        $this->assertEquals($channel->getType(), $foundChannel->getType());
        $this->assertEquals($channel->getData(), $foundChannel->getData());
    }

    #[DataProvider('channelDataProvider')]
    public function testRemoveChannel(Channel $channel): void
    {
        // 1. Создаем и сохраняем канал

        $this->repository->save($channel);

        $channelId = $channel->getId()->toString();

        // 2. Удаляем канал
        $this->repository->remove($channel);

        // 3. Пытаемся найти удаленный канал
        $foundChannel = $this->repository->findById($channelId);

        // 4. Проверяем что канал удален
        $this->assertNull($foundChannel);
    }

    #[DataProvider('channelDataProvider')]
    public function testFindBySecret(Channel $channel): void
    {
        // 1. Создаем канал с известным секретом
        $secret = 'test-secret-123';

        $channel->setSecret($secret);

        $this->repository->save($channel);

        // 2. Ищем по секрету
        $foundChannel = $this->repository->findBySecret($secret);

        // 3. Проверяем результаты
        $this->assertNotNull($foundChannel);
        $this->assertTrue($foundChannel->verify($secret));
    }

    #[DataProvider('channelDataProvider')]
    public function testFindByChannelName(Channel $channel): void
    {
        // 1. Сохраняем
        $this->repository->save($channel);

        // 2. Ищем по имени канала
        $foundChannel = $this->repository->findByChannel($channel->getChannel());

        // 3. Проверяем результаты
        $this->assertNotNull($foundChannel);
        $this->assertEquals($channel->getChannel(), $foundChannel->getChannel());
    }

    public static function channelDataProvider(): \Generator
    {
        yield 'case telegram' => [
            new Channel(
                id: Uuid::v4(),
                ownerId: Uuid::v4()->toString(),
                data: [],
                type: ChannelType::TELEGRAM,
                channel: 'unique-channel-name-'.uniqid(),
            ),
        ];
        yield 'case email' => [
            new Channel(
                id: Uuid::v4(),
                ownerId: Uuid::v4()->toString(),
                data: ['address' => 'test@example.com'],
                type: ChannelType::EMAIL,
                channel: 'unique-channel-name-'.uniqid(),
            ),
        ];
    }
}
