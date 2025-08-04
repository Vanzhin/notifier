<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Controller\v1\Channel;

use App\Notification\Application\DTO\ChannelDTO;
use App\Notification\Application\UseCase\Query\FindChannel\FindChannelQuery;
use App\Shared\Application\DTO\ResponseDTO;
use App\Shared\Application\Query\QueryBusInterface;
use App\Shared\Domain\Service\JwtValidatorService;
use App\Shared\Infrastructure\Controller\JwtCheckController;
use Nelmio\ApiDocBundle\Attribute\Model;
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
                required: ["result", "status", "data", "message"],
                properties: [
                    new OA\Property(property: "result", type: "string", example: "success"),
                    new OA\Property(property: "status", type: "integer", example: 200),
                    new OA\Property(property: "data", ref: new Model(type: ChannelDTO::class), nullable: true),
                    new OA\Property(property: "message", ref: null),
                ],
                type: "object",
                additionalProperties: false
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthorized - Invalid or missing JWT token',
            content: new OA\JsonContent(
                examples: [
                    new OA\Examples(
                        example: 'unauthorized',
                        summary: 'Unauthorized',
                        value: [
                            'result' => 'error',
                            'status' => 401,
                            'data' => null,
                            'message' => 'Unauthorized'
                        ]
                    )
                ],
                ref: new Model(type: ResponseDTO::class),
                type: 'object'
            )
        ),
        new OA\Response(
            response: 403,
            description: 'Forbidden',
            content: new OA\JsonContent(
                examples: [
                    new OA\Examples(
                        example: 'forbidden',
                        summary: 'Forbidden',
                        value: [
                            'result' => 'error',
                            'status' => 403,
                            'data' => null,
                            'message' => 'Forbidden'
                        ]
                    )
                ],
                ref: new Model(type: ResponseDTO::class),
                type: 'object'
            )
        ),
        new OA\Response(
            response: 500,
            description: 'Internal server error',
            content: new OA\JsonContent(
                examples: [
                    new OA\Examples(
                        example: 'internal_server_error',
                        summary: 'Internal server error',
                        value: [
                            'result' => 'error',
                            'status' => 500,
                            'data' => null,
                            'message' => 'Internal server error'
                        ]
                    )
                ],
                ref: new Model(type: ResponseDTO::class),
                type: 'object'
            )
        ),
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
