<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Repository;

use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Repository\SubscriptionFilter;
use App\Notification\Domain\Repository\SubscriptionRepositoryInterface;
use App\Shared\Domain\Repository\PaginationResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

final class SubscriptionRepository extends ServiceEntityRepository implements SubscriptionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    public function save(Subscription $subscription): void
    {
        $this->getEntityManager()->persist($subscription);
        $this->getEntityManager()->flush();
    }

    public function findById(string $id): ?Subscription
    {
        return $this->find($id);
    }

    public function remove(Subscription $subscription): void
    {
        $this->getEntityManager()->remove($subscription);
        $this->getEntityManager()->flush();
    }

    public function findByFilter(SubscriptionFilter $filter): PaginationResult
    {
        $qb = $this->createQueryBuilder('s');
        if ($filter->getPhoneNumbers()) {
            $qb->join('s.phoneNumbers', 'p')
                ->andWhere('p.phone.value IN (:phones)')
                ->setParameter('phones', $filter->getPhoneNumbers());
        }

        if ($filter->getEvents()) {
            $orX = $qb->expr()->orX();
            foreach ($filter->getEvents() as $key => $event) {
                $paramName = 'event_' . $key;
                $orX->add(
                    sprintf("JSONB_EXISTS(s.subscriptionEvents, :%s) = true", $paramName)
                );
                $qb->setParameter($paramName, $event);
            }

            $qb->andWhere($orX);
        }

        if ($filter->getOwnerId()) {
            $qb->andWhere('s.subscriberId = :ownerId')
                ->setParameter('ownerId', $filter->getOwnerId());
        }

        if ($filter->getSort()) {
            foreach ($filter->getSort() as $field => $direction) {
                $qb->addOrderBy("s.$field", $direction);
            }
        } else {
            $qb->addOrderBy('s.createdAt', 'DESC'); // Сортировка по умолчанию
        }

        if ($filter->pager) {
            $qb->setMaxResults($filter->pager->getLimit());
            $qb->setFirstResult($filter->pager->getOffset());
        }
        $paginator = new Paginator($qb->getQuery());

        return new PaginationResult(iterator_to_array($paginator->getIterator()), $paginator->count());
    }
}
