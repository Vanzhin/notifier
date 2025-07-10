<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Repository;

use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ChannelRepository extends ServiceEntityRepository implements ChannelRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Channel::class);
    }

    public function save(Channel $channel): void
    {
        $this->getEntityManager()->persist($channel);
        $this->getEntityManager()->flush();
    }

    public function findById(string $channelId): ?Channel
    {
        return $this->find($channelId);
    }
}
