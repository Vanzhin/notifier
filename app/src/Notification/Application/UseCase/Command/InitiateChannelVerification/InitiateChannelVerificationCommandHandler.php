<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\InitiateChannelVerification;

use App\Notification\Application\Service\AccessControl\ChannelAccessControl;
use App\Notification\Domain\Repository\ChannelRepositoryInterface;
use App\Notification\Domain\Service\ChannelVerifierInterface;
use App\Shared\Application\Command\CommandHandlerInterface;
use App\Shared\Infrastructure\Exception\AppException;
use Webmozart\Assert\Assert;

readonly class InitiateChannelVerificationCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private ChannelRepositoryInterface $repository,
        private iterable $strategies,
        private ChannelAccessControl $accessControl,
    ) {
    }

    public function __invoke(InitiateChannelVerificationCommand $command): InitiateChannelVerificationCommandResult
    {
        $channel = $this->repository->findById($command->channelId);
        Assert::notNull($channel, message: 'Channel not found.');
        if (!$this->accessControl->canViewChannel($channel, $command->userId)) {
            throw new AppException('Channel is not allowed to initiate verification.', 403);
        }
        foreach ($this->strategies as $strategy) {
            /** @var ChannelVerifierInterface $strategy */
            if ($strategy->supports($channel)) {
                $verification = $strategy->initiateChannelVerification($channel);

                return new InitiateChannelVerificationCommandResult($verification);
            }
        }

        return new InitiateChannelVerificationCommandResult();
    }
}
