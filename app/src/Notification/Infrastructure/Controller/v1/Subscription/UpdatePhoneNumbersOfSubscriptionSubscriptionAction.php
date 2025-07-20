<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Controller\v1\Subscription;

use App\Notification\Application\UseCase\Command\UpdatePhoneNumberOfSubscription\UpdatePhoneNumberOfSubscriptionCommand;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Domain\Service\JwtValidatorService;
use App\Shared\Infrastructure\Controller\JwtCheckController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Webmozart\Assert\Assert;

#[Route('subscription/{subscriptionId}/phones', requirements: [
    'subscriptionId' => Requirement::UUID_V4,
], methods: ['PATCH'])]
class UpdatePhoneNumbersOfSubscriptionSubscriptionAction extends JwtCheckController
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
        $phoneNumbers = null;
        if ($request->getPayload()->has('phone_numbers')) {
            $phoneNumbers = $request->getPayload()->all('phone_numbers');
        }
        Assert::notNull($phoneNumbers, message: 'phone numbers not found');

        $command = new UpdatePhoneNumberOfSubscriptionCommand($subscriptionId, $userId, ...$phoneNumbers);
        $this->commandBus->execute($command);

        return new JsonResponse(null);
    }
}
