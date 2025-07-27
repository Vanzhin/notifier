<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Controller\v1\Subscription;

use App\Notification\Application\UseCase\Command\RemoveSubscription\RemoveSubscriptionCommand;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Domain\Service\JwtValidatorService;
use App\Shared\Infrastructure\Controller\JwtCheckController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('subscription/{id}', requirements: ['id' => Requirement::UUID_V4], methods: ['DELETE'])]
class RemoveSubscriptionAction extends JwtCheckController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        JwtValidatorService $jwtValidatorService,
    ) {
        parent::__construct($jwtValidatorService);
    }

    public function __invoke(string $id, Request $request): JsonResponse
    {
        $query = new RemoveSubscriptionCommand(subscriptionId: $id, userId: $this->getUserId($request));
        $this->commandBus->execute($query);

        return new JsonResponse(null);
    }
}
