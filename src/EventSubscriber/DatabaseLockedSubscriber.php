<?php

namespace App\EventSubscriber;

use Doctrine\DBAL\Exception as DBALException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class DatabaseLockedSubscriber implements EventSubscriberInterface
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof DBALException && str_contains($exception->getMessage(), 'database is locked')) {
            $response = new Response(
                $this->twig->render('bundles/TwigBundle/Exception/error_db_locked.html.twig'),
                Response::HTTP_SERVICE_UNAVAILABLE
            );
            $event->setResponse($response);
        }
    }
}
