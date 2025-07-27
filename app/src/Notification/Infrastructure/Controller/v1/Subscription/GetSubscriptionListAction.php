<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Controller\v1\Subscription;

use App\Notification\Application\UseCase\Query\GetPagedSubscriptionList\GetPagedSubscriptionsQuery;
use App\Notification\Infrastructure\Mapper\SubscriptionMapper;
use App\Shared\Application\Query\QueryBusInterface;
use App\Shared\Domain\Service\JwtValidatorService;
use App\Shared\Infrastructure\Controller\JwtCheckController;
use App\Shared\Infrastructure\Exception\AppException;
use App\Shared\Infrastructure\Validation\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('subscription/list', methods: ['POST'])]
class GetSubscriptionListAction extends JwtCheckController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly Validator $validator,
        private readonly SubscriptionMapper $subscriptionMapper,
        JwtValidatorService $jwtValidatorService,
    ) {
        parent::__construct($jwtValidatorService);
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->getPayload()->all();
        $errors = $this->validator->validate($data,
            $this->subscriptionMapper->getValidationCollectionSubscriptionFilter());
        if ($errors) {
            throw new AppException(current($errors)->getFullMessage());
        }
        $filter = $this->subscriptionMapper->buildFilter($data);

        $query = new GetPagedSubscriptionsQuery($filter, $this->getUserId($request));
        $result = $this->queryBus->execute($query);

        return new JsonResponse($result);
    }
}
