<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Controller\v1\Subscription;

use App\Notification\Application\UseCase\Command\UpdateEventsOfSubscription\UpdateEventsOfSubscriptionCommand;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Domain\Service\JwtValidatorService;
use App\Shared\Infrastructure\Controller\JwtCheckController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Webmozart\Assert\Assert;

#[Route('subscription/{subscriptionId}/events', requirements: [
    'subscriptionId' => Requirement::UUID_V4,
], methods: ['PATCH'])]
class UpdateEventsOfSubscriptionSubscriptionAction extends JwtCheckController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        JwtValidatorService $jwtValidatorService,
    ) {
        parent::__construct($jwtValidatorService);
    }

    public function __invoke(string $subscriptionId, Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);
        $events = null;
        if ($request->getPayload()->has('events')) {
            $events = $request->getPayload()->all('events');
        }
        Assert::notNull($events, message: 'events not found');

        $command = new UpdateEventsOfSubscriptionCommand($subscriptionId, $userId, ...$events);
        $this->commandBus->execute($command);

        return new JsonResponse(null);
    }
}
