<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Controller\v1\Channel;

use App\Notification\Application\UseCase\Command\InitiateChannelVerification\InitiateChannelVerificationCommand;
use App\Shared\Application\Command\CommandBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('channel/{channelId}', requirements: ['channelId' => Requirement::UUID_V4], methods: ['GET'])]
class InitiateChannelVerificationAction extends AbstractController
{
    public function __construct(private readonly CommandBusInterface $commandBus)
    {
    }

    public function __invoke(string $channelId): JsonResponse
    {
        $command = new InitiateChannelVerificationCommand($channelId);
        $result = $this->commandBus->execute($command);

        return new JsonResponse($result);
    }
}
