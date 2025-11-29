<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Visitor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername('admin');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $user->setName('Admin');
        $manager->persist($user);

        $visitor1 = new Visitor();
        $visitor1->setName('Test Visitor 1');
        $visitor1->setTag(1);
        $visitor1->setDni('123456789');
        $visitor1->setDestination('Someplace');
        $visitor1->setCheckInAt(new \DateTimeImmutable());
        $manager->persist($visitor1);

        $visitor2 = new Visitor();
        $visitor2->setName('Test Visitor 2');
        $visitor2->setTag(2);
        $visitor2->setDni('987654321');
        $visitor2->setDestination('Another Place');
        $visitor2->setCheckInAt(new \DateTimeImmutable());
        $manager->persist($visitor2);

        $manager->flush();
    }
}
