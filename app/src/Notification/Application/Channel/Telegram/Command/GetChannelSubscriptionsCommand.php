<?php

declare(strict_types=1);

namespace App\Notification\Application\Channel\Telegram\Command;

use App\Notification\Domain\Aggregate\PhoneNumber;
use App\Notification\Domain\Aggregate\Subscription;
use App\Notification\Domain\Aggregate\ValueObject\EventType;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Notification\Domain\Service\EventTypeResolver;
use Doctrine\Common\Collections\Collection;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Psr\Log\LoggerInterface;

class GetChannelSubscriptionsCommand extends UserCommand
{
    private LoggerInterface $logger;
    private ChannelRepositoryInterface $channelRepository;
    private EventTypeResolver $eventTypeResolver;

    protected $name = 'get_channel_subscriptions';
    protected $description = 'Get the channel subscriptions list';
    protected $usage = '/get_channel_subscriptions';
    protected $version = '1.0';

    public function execute(): ServerResponse
    {
        $this->initDependencies();

        $message = $this->getMessage();
        $chatId = $message->getChat()->getId();
        $channel = $this->channelRepository->findByChannel((string)$chatId);

        $responseText = $this->buildGreetingMessage($message);

        if (null === $channel) {
            return $this->replyToChat($responseText . '❌ *Канал не найден.*');
        }

        if ($channel->getSubscriptions()->isEmpty()) {
            return $this->replyToChat($responseText . 'ℹ️ *Подписок нет.*');
        }

        $responseText .= $this->buildSubscriptionsMessage($channel->getSubscriptions());

        return $this->replyToChat($responseText, ['parse_mode' => 'Markdown']);
    }

    private function initDependencies(): void
    {
        $this->logger = $this->config['logger'];
        $this->channelRepository = $this->config['channelRepository'];
        $this->eventTypeResolver = $this->config['eventTypeResolver'];
    }

    private function buildGreetingMessage(Message $message): string
    {
        $firstName = $message->getFrom()->getFirstName();

        return "👋 *Привет, {$firstName}!*\n\n";
    }

    private function buildSubscriptionsMessage(Collection $subscriptions): string
    {
        $message = sprintf("📌 *Подписок в канале: %d*\n\n", $subscriptions->count());

        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
            $message .= $this->formatSubscription($subscription);
        }

        return $message;
    }

    private function formatSubscription(Subscription $subscription): string
    {
        $numbers = $subscription->phoneNumbers->map(
            function (PhoneNumber $phone) {
                $number = new \App\Notification\Domain\Aggregate\ValueObject\PhoneNumber($phone->getPhone()->getValue());
                return "`{$number}`";
            }
        )->toArray();

        $events = array_map(function (EventType $event) {
            return $this->eventTypeResolver->resolve($event);
        }, $subscription->getSubscriptionEvents());

        return sprintf(
            "📱 *Номера:*\n%s\n📌 *События:*\n`%s`\n\n",
            implode("\n", $numbers),
            implode('`,  `', $events)
        );
    }
}
