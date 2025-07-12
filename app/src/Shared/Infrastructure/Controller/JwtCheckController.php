<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Controller;

use App\Shared\Domain\Service\JwtValidatorService;
use App\Shared\Infrastructure\Exception\AppException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class JwtCheckController extends AbstractController
{
    public function __construct(
        private readonly JwtValidatorService $jwtValidatorService,
    ) {
    }

    protected function getJwtPayload(Request $request): array
    {
        $authHeader = $request->headers->get('Authorization');
        $token = str_replace('Bearer ', '', $authHeader ?? '');

        if (empty($token)) {
            throw new AppException('token is empty.');
        }
        return $this->jwtValidatorService->validateAndGetPayload($token);
    }

    protected function getUserId(Request $request): ?string
    {
        return $this->getJwtPayload($request)['user_id'] ?? null;
    }


}
