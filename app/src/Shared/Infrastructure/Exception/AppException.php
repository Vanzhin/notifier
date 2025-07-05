<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Exception;

class AppException extends \Exception
{
    public function __construct($message = '', $code = 422, private array $log = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getLog(): array
    {
        return $this->log;
    }
}
