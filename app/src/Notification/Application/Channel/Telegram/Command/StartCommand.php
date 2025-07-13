<?php

declare(strict_types=1);

namespace App\Notification\Application\Channel\Telegram\Command;

use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Message\ChannelVerificationCodeGetMessage;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Shared\Application\Message\MessageBusInterface;
use App\Shared\Infrastructure\Exception\AppException;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Psr\Log\LoggerInterface;

class StartCommand extends UserCommand
{
    private static LoggerInterface $notifierLogger;
    private static MessageBusInterface $messageBus;
    private static ChannelRepositoryInterface $channelRepository;

    public static function setLogger(LoggerInterface $notifierLogger): void
    {
        self::$notifierLogger = $notifierLogger;
    }

    public static function setMessageBus(MessageBusInterface $messageBus): void
    {
        self::$messageBus = $messageBus;
    }

    public static function setRepository(ChannelRepositoryInterface $channelRepository): void
    {
        self::$channelRepository = $channelRepository;
    }

    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';
    protected $version = '1.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chatId = $message->getChat()->getId();
        $text = $this->buildDefaultTextMessage($message);

        $verificationCode = $this->getVerificationCodeFromMessage($message);

        if (!$verificationCode) {
            return $this->replyWithVerificationError($text, 'Код верификации не обнаружен.');
        }

        $channel = $this->findChannelByVerificationCode($verificationCode);
        self::$notifierLogger->error(json_encode(['code' => $verificationCode, 'channel' => $channel?->getId()]));

        if (!$channel) {
            return $this->replyWithVerificationError($text, 'Канал не найден или код верификации не верен.');
        }
        if ($channel->isVerified()) {
            return $this->replyWithSuccess($text, 'Канал уже верифицирован.');
        }
        $this->addChannelToChannel($channel, (string)$chatId);

        $this->dispatchChannelVerificationEvent($verificationCode, $channel->getId()->toString());

        return $this->replyWithSuccess($text, 'Ваш код обрабатывается, после обработки придет уведомление.');
    }

    /**
     * @throws TelegramException
     */
    private function replyWithVerificationError(string $baseText, string $errorMessage): ServerResponse
    {
        return $this->replyToChat($baseText . PHP_EOL . $errorMessage . ' Удалите этот чат и попробуйте еще раз.');
    }

    private function findChannelByVerificationCode(string $code): ?Channel
    {
        return self::$channelRepository->findBySecret($code);
    }

    /**
     * @throws AppException
     */
    private function addChannelToChannel(Channel $channel, string $chan): void
    {
        $channel->setChannel($chan);
        self::$channelRepository->save($channel);
    }

    private function dispatchChannelVerificationEvent(string $verificationCode, string $channelId): void
    {
        self::$messageBus->executeMessages(
            new ChannelVerificationCodeGetMessage($channelId, $verificationCode)
        );
    }

    /**
     * @throws TelegramException
     */
    private function replyWithSuccess(string $baseText, string $successMessage): ServerResponse
    {
        return $this->replyToChat($baseText . PHP_EOL . $successMessage);
    }

    private function getVerificationCodeFromMessage(Message $message): ?string
    {
        $code = str_replace($this->usage, "", $message->getText());

        if (empty($code)) {
            return null;
        }

        return trim($code);
    }

    private function buildDefaultTextMessage(Message $message): string
    {
        $message = $this->getMessage();
        $first_name = $message->getFrom()->getFirstName();

        // Основное приветственное сообщение
        $text = "👋 Привет, {$first_name}!\n\n";
        $text .= "Я бот для управления уведомлениями.\n";

        return $text;
    }
}
