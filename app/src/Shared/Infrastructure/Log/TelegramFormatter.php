<?php

namespace App\Shared\Infrastructure\Log;

use Monolog\Formatter\FormatterInterface;
use Monolog\LogRecord;

readonly class TelegramFormatter implements FormatterInterface
{
    public function __construct()
    {
    }

    public function format(LogRecord $record): string|false
    {
        return json_encode([
            'message' => $record->message,
            'context' => $record->context,
            'level' => $record->level,
            'channel' => $record->channel,
            'datetime' => $record->datetime,
            'extra' => $record->extra,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function formatBatch(array $records): string
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return "```\n" . $message . "\n```";
    }
}
