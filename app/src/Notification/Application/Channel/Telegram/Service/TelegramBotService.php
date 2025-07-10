<?php

declare(strict_types=1);

namespace App\Notification\Application\Channel\Telegram\Service;

use App\Notification\Domain\Aggregate\ChannelInterface;
use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ServerResponse;
use Psr\Log\LoggerInterface;

final readonly class TelegramBotService
{
    public function __construct(
        private string $webhookUrl,
        private string $commandPath,
        private Telegram $telegram,
        private LoggerInterface $logger,
    ) {
        $this->addCommandsPath();
    }

    /**
     * Установка вебхука
     * @throws TelegramException
     */
    public function setWebhook(): ServerResponse
    {
        return $this->telegram->setWebhook($this->webhookUrl);
    }

    /**
     * Отправка уведомления через Telegram
     */
    public function sendNotification(Notification $notification): ServerResponse
    {
        if (!$this->supports($notification->getChannel())) {
            throw new \InvalidArgumentException('Unsupported channel type');
        }

        $chatId = $notification->getRecipient()->getTelegramChatId();
        $message = $notification->getMessage();

        return $this->sendMessage($chatId, $message);
    }

    /**
     * Отправка простого сообщения
     */
    public function sendMessage(int $chatId, string $text, array $options = []): ServerResponse
    {
        try {
            $params = array_merge([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ], $options);

            $response = Request::sendMessage($params);

            $this->logger->debug('Telegram message sent', [
                'chat_id' => $chatId,
                'text' => $text,
                'response' => $response->getResult()
            ]);

            return $response;
        } catch (\Exception $e) {
            $this->logger->error('Failed to send Telegram message', [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Обработка входящих запросов
     */
    public function handle(): void
    {
        try {
            $this->telegram->handle();
        } catch (\Exception $e) {
            $this->logger->error('Telegram bot handling failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Проверка поддержки канала
     */
    public function supports(ChannelInterface $channel): bool
    {
        return $channel->getType() === ChannelType::TELEGRAM;
    }

    /**
     * Получение экземпляра Telegram API клиента
     */
    public function getClient(): Telegram
    {
        return $this->telegram;
    }

    private function addCommandsPath(): void
    {
        $this->telegram->addCommandsPath($this->commandPath);
    }
}
