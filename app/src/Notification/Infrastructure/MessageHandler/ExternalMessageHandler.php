<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\MessageHandler;

use App\Notification\Domain\Message\PhoneNumberExternalMessage;
use App\Shared\Application\Message\MessageHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class ExternalMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private LoggerInterface $notifierLogger,
    ) {
    }

    public function __invoke(PhoneNumberExternalMessage $message): void
    {
        try {
            $this->notifierLogger->error(json_encode($message->jsonSerialize()));
        } catch (\Exception $exception) {
            $this->notifierLogger->error($exception->getMessage());
        }
    }

}
