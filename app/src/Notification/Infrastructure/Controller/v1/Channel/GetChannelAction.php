<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Controller\v1\Channel;

use App\Notification\Application\UseCase\Query\FindChannel\FindChannelQuery;
use App\Shared\Application\Query\QueryBusInterface;
use App\Shared\Domain\Service\JwtValidatorService;
use App\Shared\Infrastructure\Controller\JwtCheckController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('channel/{id}', requirements: ['id' => Requirement::UUID_V4], methods: ['GET'])]
class GetChannelAction extends JwtCheckController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        JwtValidatorService $jwtValidatorService
    ) {
        parent::__construct($jwtValidatorService);
    }

    public function __invoke(string $id, Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);
        $query = new FindChannelQuery(channelId: $id, ownerId: $userId,);
        $result = $this->queryBus->execute($query);

        return new JsonResponse($result->channel);
    }
}
