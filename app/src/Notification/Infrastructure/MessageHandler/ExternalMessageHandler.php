<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\MessageHandler;

use App\Notification\Domain\Message\ExternalMessage;
use App\Shared\Application\Message\MessageHandlerInterface;
use Psr\Log\LoggerInterface;

readonly class ExternalMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private LoggerInterface $notifierLogger,
    ) {
    }

    public function __invoke(ExternalMessage $message): void
    {
        try {
            $this->notifierLogger->error(json_encode($message->getData()));
        } catch (\Exception $exception) {
            $this->notifierLogger->error($exception->getMessage());
        }
    }

}
