<?php

namespace App\EventListener;

use App\Exception\PaymentIntentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class PaymentIntentExceptionListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $throwable = $event->getThrowable();

        if(!$throwable instanceof PaymentIntentException) {
            return;
        }

        $response = (new Response())
            ->setContent($throwable->getMessage())
            ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR)
        ;

        $event->setResponse($response);
    }
}