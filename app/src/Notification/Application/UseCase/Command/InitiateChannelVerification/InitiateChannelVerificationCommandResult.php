<?php

declare(strict_types=1);

namespace App\Notification\Application\UseCase\Command\InitiateChannelVerification;

class InitiateChannelVerificationCommandResult
{
    public function __construct(public ?string $verification_data = null)
    {
    }
}
