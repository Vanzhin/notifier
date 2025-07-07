<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Controller\v1;

use App\Notification\Application\UseCase\Query\FindSubscription\FindSubscriptionQuery;
use App\Shared\Application\Query\QueryBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('subscription/{id}', requirements: ['id' => Requirement::UUID_V4], methods: ['GET'])]
class GetSubscriptionAction extends AbstractController
{
    public function __construct(private readonly QueryBusInterface $queryBus)
    {
    }

    public function __invoke(string $id): JsonResponse
    {
        $query = new FindSubscriptionQuery($id);
        $result = $this->queryBus->execute($query);

        return new JsonResponse($result->subscription);
    }
}
