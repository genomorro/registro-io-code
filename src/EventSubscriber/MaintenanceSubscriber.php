<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class MaintenanceSubscriber implements EventSubscriberInterface
{
    private $router;
    private $lockFilePath;

    public function __construct(RouterInterface $router, string $projectDir)
    {
        $this->router = $router;
        $this->lockFilePath = $projectDir . '/var/maintenance.lock';
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (file_exists($this->lockFilePath)) {
            $maintenanceRoute = 'app_maintenance';
            $currentRoute = $event->getRequest()->attributes->get('_route');

            if ($currentRoute !== $maintenanceRoute) {
                $response = new RedirectResponse($this->router->generate($maintenanceRoute));
                $event->setResponse($response);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
