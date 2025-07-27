<?php

declare(strict_types=1);

namespace App\Tests\Integration\Notification\Infrastructure\Controller\v1\Channel;

use App\Notification\Infrastructure\Controller\v1\Channel\CreateChannelAction;
use App\Notification\Infrastructure\Mapper\ChannelMapper;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Domain\Service\JwtValidatorService;
use App\Shared\Infrastructure\Exception\AppException;
use App\Shared\Infrastructure\Validation\Validator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CreateChannelActionTest extends WebTestCase
{
    private CreateChannelAction $controller;
    private Validator $validator;
    private ChannelMapper $channelMapper;
    private CommandBusInterface $commandBus;
    private JwtValidatorService $jwtValidatorService;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        // Получаем реальные сервисы из контейнера
        $this->validator = self::getContainer()->get(Validator::class);
        $this->channelMapper = self::getContainer()->get(ChannelMapper::class);
        $this->commandBus = self::getContainer()->get(CommandBusInterface::class);

        $this->jwtValidatorService = $this->createMock(JwtValidatorService::class);

        $this->controller = new CreateChannelAction(
            $this->validator,
            $this->channelMapper,
            $this->commandBus,
            $this->jwtValidatorService
        );
    }

    public function testSuccessfulChannelCreation(): void
    {
        $request = Request::create(
            '/channel',
            'POST',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer token', // Добавляем JWT-токен
            ],
            json_encode([
                'type' => 'telegram',
                'data' => ['channel_id' => 'test'],
                'channel' => null,
            ])
        );

        // Мокаем JWT валидацию
        $this->jwtValidatorService->method('validateAndGetPayload')
            ->willReturn(['user_id' => 'user-123']);

        $response = $this->controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('channel_id', $responseData);
        $this->assertNotEmpty($responseData['channel_id']);
    }

    public function testValidationFailure(): void
    {
        $this->expectException(AppException::class);

        $request = Request::create(
            '/channel',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'type' => '', // невалидное значение
                'data' => [],
                'channel' => '',
            ])
        );

        // Мокаем JWT валидацию
        $this->jwtValidatorService->method('validateAndGetPayload')
            ->willReturn(['user_id' => 'user-123']);

        $this->controller->__invoke($request);
    }

    public function testMissingUserId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('user_id cannot be found.');

        $request = Request::create(
            '/channel',
            'POST',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer token', // Добавляем JWT-токен
            ],
            json_encode([
                'type' => 'email',
                'data' => ['address' => 'test@example.com'],
                'channel' => 'marketing',
            ])
        );

        // Возвращаем null для userId
        $this->jwtValidatorService->method('validateAndGetPayload')
            ->willReturn([]);

        $this->controller->__invoke($request);
    }

    public function testMissingRequiredFields(): void
    {
        $this->expectException(AppException::class);

        $request = Request::create(
            '/channel',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer token', // Добавляем JWT-токен
            ],
            json_encode([
                // Пропущены обязательные поля
                'data' => [],
            ])
        );

        // Мокаем JWT валидацию
        $this->jwtValidatorService->method('validateAndGetPayload')
            ->willReturn(['user_id' => 'user-123']);

        $this->controller->__invoke($request);
    }
}
