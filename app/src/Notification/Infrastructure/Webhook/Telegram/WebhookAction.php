<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Webhook\Telegram;

use App\Notification\Application\Channel\Telegram\Service\TelegramBotService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/webhook/telegram', name: 'telegram_webhook', methods: ['POST'])]
class WebhookAction extends AbstractController
{
    public function __construct(
        private readonly TelegramBotService $telegramBotService,
        private readonly LoggerInterface $notifierLogger,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request): Response
    {
        $secret = $request->headers->get('x-telegram-bot-api-secret-token');
        if ($secret) {
            $this->telegramBotService->handle($secret);
        }
        $this->notifierLogger->error(json_encode($request->headers->all(), JSON_THROW_ON_ERROR));

        return new Response('ok');
    }

}
