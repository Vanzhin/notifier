<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Controller\v1\Channel;

use App\Notification\Application\UseCase\Query\FindChannel\FindChannelQuery;
use App\Shared\Application\Query\QueryBusInterface;
use App\Shared\Domain\Service\JwtValidatorService;
use App\Shared\Infrastructure\Controller\JwtCheckController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('channel/{id}', requirements: ['id' => Requirement::UUID_V4], methods: ['GET'])]
#[OA\Get(
    path: '/api/v1/channel/{id}',
    operationId: 'getChannelById',
    description: 'Returns channel details for the specified ID',
    summary: 'Получить канал по идентификатору',
    security: [['Bearer' => []]],
    tags: ['Channel'],
    parameters: [
        new OA\Parameter(
            name: 'id',
            description: 'UUID of the channel',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string', format: 'uuid')
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Channel details',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'type', type: 'string'),
                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time')
                ],
                type: 'object'
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthorized - Invalid or missing JWT token'
        ),
        new OA\Response(
            response: 403,
            description: 'Forbidden - Channel does not belong to user'
        ),
        new OA\Response(
            response: 404,
            description: 'Channel not found'
        )
    ]
)]
class GetChannelAction extends JwtCheckController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        JwtValidatorService $jwtValidatorService,
    ) {
        parent::__construct($jwtValidatorService);
    }

    public function __invoke(string $id, Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);
        $query = new FindChannelQuery(channelId: $id, ownerId: $userId);
        $result = $this->queryBus->execute($query);

        return new JsonResponse($result->channel);
    }
}
