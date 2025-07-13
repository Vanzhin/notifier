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
        $this->telegramBotService->handle();
        $this->notifierLogger->error(json_encode($request->getContent()));

        return new Response('ok');
    }
}
