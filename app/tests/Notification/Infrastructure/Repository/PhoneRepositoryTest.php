<?php

declare(strict_types=1);

namespace App\Tests\Integration\Notification\Infrastructure\Repository;

use App\Notification\Domain\Aggregate\PhoneNumber;
use App\Notification\Domain\Repository\PhoneRepositoryInterface;
use App\Tests\Tools\DITools;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

class PhoneRepositoryTest extends KernelTestCase
{
    use DITools;

    private PhoneRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getService(PhoneRepositoryInterface::class);

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

    #[DataProvider('phoneDataProvider')]
    public function testFindByPhoneWhenExists(PhoneNumber $phoneNumber): void
    {
        // 1. Подготовка тестовых данных
        $this->repository->getEntityManager()->persist($phoneNumber);
        $this->repository->getEntityManager()->flush($phoneNumber);
        $this->repository->getEntityManager()->clear();

        // 2. Выполнение
        $foundPhone = $this->repository->findByPhone($phoneNumber->getPhone()->getValue());

        // 3. Проверка
        $this->assertInstanceOf(PhoneNumber::class, $foundPhone);
        $this->assertEquals($phoneNumber->getPhone(), $foundPhone->getPhone());
    }

    public function testFindByPhoneWhenNotExists(): void
    {
        // 1. Подготовка (убедимся, что такого номера нет)
        $nonExistentPhone = '+79000000000';

        // 2. Выполнение
        $foundPhone = $this->repository->findByPhone($nonExistentPhone);

        // 3. Проверка
        $this->assertNull($foundPhone);
    }

    public static function phoneDataProvider(): \Generator
    {
        yield 'case 1' => [
            new PhoneNumber(
                id: Uuid::v4(),
                phone: new \App\Notification\Domain\Aggregate\ValueObject\PhoneNumber('79111111111')
            ),
        ];
        yield 'case 2' => [
            new PhoneNumber(
                id: Uuid::v4(),
                phone: new \App\Notification\Domain\Aggregate\ValueObject\PhoneNumber('79222222222')
            ),
        ];
    }
}
