<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Controller\v1;

use App\Notification\Application\UseCase\Command\RemoveSubscription\RemoveSubscriptionCommand;
use App\Shared\Application\Command\CommandBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('subscription/{id}', requirements: ['id' => Requirement::UUID_V4], methods: ['DELETE'])]
class RemoveSubscriptionAction extends AbstractController
{
    public function __construct(private readonly CommandBusInterface $commandBus)
    {
    }

    public function __invoke(string $id): JsonResponse
    {
        $query = new RemoveSubscriptionCommand($id);
        $this->commandBus->execute($query);

        return new JsonResponse(null);
    }
}
