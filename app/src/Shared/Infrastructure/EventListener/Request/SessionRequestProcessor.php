<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EventListener\Request;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class SessionRequestProcessor implements ProcessorInterface
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public function __invoke(LogRecord $record)
    {
        try {
            $this->requestStack->getSession();
        } catch (SessionNotFoundException $e) {
            return $record;
        }
        $post = empty($this->requestStack->getCurrentRequest()->getContent()) ?
            $this->requestStack->getCurrentRequest()->request->all() : json_decode($this->requestStack->getCurrentRequest()->getContent(),
                true);

        $record->extra['ip'] = $this->requestStack->getCurrentRequest()->getClientIp();
        $record->extra['method'] = $this->requestStack->getCurrentRequest()->getMethod();
        $record->extra['query'] = $this->requestStack->getCurrentRequest()->getRequestUri();
        $record->extra['post'] = $post;
        $record->extra['referrer'] = $this->requestStack->getCurrentRequest()->getHost();

        return $record;
    }
}
