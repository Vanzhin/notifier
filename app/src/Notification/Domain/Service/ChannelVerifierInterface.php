<?php

declare(strict_types=1);

namespace App\Notification\Domain\Service;

use App\Notification\Domain\Aggregate\Channel;

interface ChannelVerifierInterface
{

    public function supports(Channel $channel): bool;

    public function initiateChannelVerification(Channel $channel): string;

    public function verify(Channel $channel, string $secret): void;

}
