<?php

declare(strict_types=1);

namespace App\Notification\Domain\Service;

use App\Notification\Domain\Aggregate\Channel;
use App\Notification\Domain\Aggregate\ValueObject\ChannelType;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Shared\Domain\Service\TokenService;
use App\Shared\Infrastructure\Exception\AppException;

final readonly class TelegramChannelVerifier implements ChannelVerifierInterface
{
    public function __construct(
        private TokenService $tokenService,
        private ChannelRepositoryInterface $channelRepository,
        private string $botName,
    ) {
    }

    public function initiateChannelVerification(Channel $channel): string
    {
        $secret = $this->tokenService->generate(32);
        $channel->setSecret($secret);
        $this->channelRepository->save($channel);

        return sprintf('https://t.me/%s?start=%s', $this->botName, $secret);
    }

    /**
     * @throws AppException
     */
    public function verify(Channel $channel, string $secret): void
    {
        $isVerified = $channel->verify($secret);

        if (!$isVerified) {
            throw new AppException('The channel verification data is invalid.');
        }
    }

    public function supports(Channel $channel): bool
    {
        return $channel->getType() === ChannelType::TELEGRAM;
    }
}
