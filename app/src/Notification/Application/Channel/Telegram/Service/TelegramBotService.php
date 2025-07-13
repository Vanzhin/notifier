<?php

declare(strict_types=1);

namespace App\Notification\Application\Channel\Telegram\Service;

use App\Notification\Application\Channel\Telegram\Command\StartCommand;
use App\Notification\Domain\Aggregate\ChannelInterface;
use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Shared\Application\Message\MessageBusInterface;
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
        private LoggerInterface $notifierLogger,
        private MessageBusInterface $messageBus,
        private ChannelRepositoryInterface $channelRepository,
    ) {
        $this->addCommandsPath();
        //todo костыль убрать
        StartCommand::setLogger($this->notifierLogger);
        StartCommand::setMessageBus($this->messageBus);
        StartCommand::setRepository($this->channelRepository);
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

            return Request::sendMessage($params);
        } catch (\Exception $e) {
            $this->notifierLogger->error('Failed to send Telegram message', [
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
            $this->notifierLogger->error('Telegram bot handling failed', ['error' => $e->getMessage()]);
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
