<?php

namespace App\Tests\Controller;

use App\Entity\Scheduled;
use App\Entity\ScheduledAttendance;
use App\Controller\ScheduledController;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ScheduledControllerTest extends TestCase
{
    public function testDeleteScheduledWithAttendancesIsBlocked(): void
    {
        $scheduled = $this->createStub(Scheduled::class);
        $attendance = new ScheduledAttendance();
        $scheduled->method('getScheduledAttendances')
            ->willReturn(new ArrayCollection([$attendance]));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('remove');
        $entityManager->expects($this->never())->method('flush');

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')
            ->willReturnCallback(fn($id) => $id);

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->with('ROLE_SUPER_ADMIN')->willReturn(true);

        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->method('generate')
            ->with('app_scheduled_index', [])
            ->willReturn('/scheduled');

        $flashBag = $this->createMock(FlashBagInterface::class);
        $flashBag->expects($this->once())
            ->method('add')
            ->with('danger', 'Cannot delete scheduled because it is associated with scheduled attendances.');

        $session = $this->createStub(FlashBagAwareSessionInterface::class);
        $session->method('getFlashBag')->willReturn($flashBag);

        $request = new Request();
        $request->setSession($session);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturnCallback(fn($id) => in_array($id, ['security.authorization_checker', 'router', 'request_stack', 'translator']));
        $container->method('get')->willReturnCallback(function ($id) use ($authChecker, $router, $requestStack, $translator) {
            return match ($id) {
                'security.authorization_checker' => $authChecker,
                'router' => $router,
                'request_stack' => $requestStack,
                'translator' => $translator,
                default => null,
            };
        });

        $controller = new ScheduledController();
        $controller->setContainer($container);

        $response = $controller->delete($request, $scheduled, $entityManager, $translator);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/scheduled', $response->getTargetUrl());
    }
}
