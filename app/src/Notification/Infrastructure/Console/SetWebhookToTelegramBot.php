<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Console;

use App\Notification\Application\Channel\Telegram\Service\TelegramBotService;
use Longman\TelegramBot\Exception\TelegramException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:telegram-bot:set-webhook', description: 'set telegram webhook')]
final class SetWebhookToTelegramBot extends Command
{
    public function __construct(private readonly TelegramBotService $botService)
    {
        parent::__construct();
    }

    /**
     * @throws TelegramException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $result = $this->botService->setWebhook();

        $io->success($result->getDescription());

        return Command::SUCCESS;
    }
}
