<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Controller\v1\Channel;

use App\Notification\Application\UseCase\Command\InitiateChannelVerification\InitiateChannelVerificationCommand;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Domain\Service\JwtValidatorService;
use App\Shared\Infrastructure\Controller\JwtCheckController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('channel/{channelId}/init-verification', requirements: ['channelId' => Requirement::UUID_V4], methods: ['GET'])]
class InitiateChannelVerificationAction extends JwtCheckController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        JwtValidatorService $jwtValidatorService,
    ) {
        parent::__construct($jwtValidatorService);
    }

    public function __invoke(string $channelId, Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);
        $command = new InitiateChannelVerificationCommand(channelId: $channelId, userId: $userId);
        $result = $this->commandBus->execute($command);

        return new JsonResponse($result);
    }
}
