<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Controller\v1\Channel;

use App\Notification\Application\UseCase\Command\CreateChannel\CreateChannelCommand;
use App\Notification\Infrastructure\Mapper\ChannelMapper;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Domain\Service\JwtValidatorService;
use App\Shared\Infrastructure\Controller\JwtCheckController;
use App\Shared\Infrastructure\Exception\AppException;
use App\Shared\Infrastructure\Validation\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Webmozart\Assert\Assert;

#[Route('channel', methods: ['POST'])]
class CreateChannelAction extends JwtCheckController
{
    public function __construct(
        private readonly Validator $validator,
        private readonly ChannelMapper $channelMapper,
        private readonly CommandBusInterface $commandBus,
        JwtValidatorService $jwtValidatorService,
    ) {
        parent::__construct($jwtValidatorService);
    }

    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->getUserId($request);

        Assert::notNull($userId, 'user_id cannot be found.');

        $data = json_decode($request->getContent(), true);
        $errors = $this->validator->validate($data, $this->channelMapper->getValidationCollectionChannel($data));
        if ($errors) {
            throw new AppException(current($errors)->getFullMessage());
        }
        extract($data);

        $command = new CreateChannelCommand(
            $userId,
            $type,
            $data,
            $channel,
        );
        $channel_id = $this->commandBus->execute($command);

        return new JsonResponse(compact('channel_id'));
    }
}
