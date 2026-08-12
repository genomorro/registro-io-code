<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserCheckerTest extends TestCase
{
    public function testCheckPreAuthWithActiveUser(): void
    {
        $user = new User();
        $user->setActive(true);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->never())->method('trans');

        $checker = new UserChecker($translator);

        // This should not throw any exception
        $checker->checkPreAuth($user);
        $this->assertTrue($user->isActive());
    }

    public function testCheckPreAuthWithInactiveUserThrowsException(): void
    {
        $user = new User();
        $user->setActive(false);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())
            ->method('trans')
            ->with('This user is not active, contact with an administrator.', [], 'security')
            ->willReturn('This user is not active, contact with an administrator.');

        $checker = new UserChecker($translator);

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('This user is not active, contact with an administrator.');

        $checker->checkPreAuth($user);
    }
}
