<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Controller\v1;

use App\Notification\Application\UseCase\Command\CreateSubscription\CreateSubscriptionCommand;
use App\Notification\Infrastructure\Mapper\SubscriptionMapper;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Infrastructure\Exception\AppException;
use App\Shared\Infrastructure\Validation\Validator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('subscription', methods: ['POST'])]
class CreateSubscriptionAction extends AbstractController
{
    public function __construct(
        private readonly Validator $validator,
        private readonly SubscriptionMapper $subscriptionMapper,
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $errors = $this->validator->validate($data, $this->subscriptionMapper->getValidationCollectionSubscription());
        if ($errors) {
            throw new AppException(current($errors)->getFullMessage());
        }
        extract($data);

        $command = new CreateSubscriptionCommand(
            $subscriber_id,
            $phone_number,
            $events,
            $channels
        );
        $id = $this->commandBus->execute($command);

        return new JsonResponse(compact('id'));
    }
}
