<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Controller\v1\Subscription;

use App\Notification\Application\UseCase\Command\AddChannelToSubscription\AddChannelToSubscriptionCommand;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Domain\Service\JwtValidatorService;
use App\Shared\Infrastructure\Controller\JwtCheckController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('subscription/{subscriptionId}/channel/{channelId}', requirements: [
    'subscriptionId' => Requirement::UUID_V4,
    'channelId' => Requirement::UUID_V4
], methods: ['PATCH'])]
class AddChannelToSubscriptionAction extends JwtCheckController
{
    public function __construct(
        private readonly CommandBusInterface $queryBus,
        JwtValidatorService $jwtValidatorService,
    ) {
        parent::__construct($jwtValidatorService);
    }

    public function __invoke(string $subscriptionId, string $channelId, Request $request): JsonResponse
    {
            $userId = $this->getUserId($request);
            $query = new AddChannelToSubscriptionCommand($subscriptionId, $channelId, $userId);
            $this->queryBus->execute($query);

            return new JsonResponse(null);
    }
}
