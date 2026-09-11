<?php

namespace App\Tests\Controller;

use App\Entity\Area;
use App\Entity\Scheduled;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AreaControllerTest extends WebTestCase
{
    public function testDeleteAreaWithScheduledFails(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        // Create super admin user with unique username
        $user = new User();
        $user->setUsername('superadmin_test_' . uniqid());
        $user->setName('Super Admin');
        $user->setPassword('password');
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setActive(true);
        $em->persist($user);

        // Create area
        $area = new Area();
        $area->setBuilding('Edificio Test');
        $area->setUnit('Unidad Test');
        $em->persist($area);

        // Create scheduled linked to area
        $scheduled = new Scheduled();
        $scheduled->setLabel('LABEL_TEST_DEL_' . uniqid());
        $scheduled->setName('Test Scheduled');
        $scheduled->setInstitution('Inst Test');
        $scheduled->setSubject('Cultura');
        $scheduled->setBeginAt(new \DateTimeImmutable('2025-01-01'));
        $scheduled->setEndAt(new \DateTimeImmutable('2025-01-02'));
        $scheduled->setArea($area);
        $em->persist($scheduled);

        $em->flush();

        // Login super admin
        $client->loginUser($user);

        // Enable session and visit localized index
        $client->getCookieJar();
        $crawler = $client->request('GET', '/en/area');

        // Attempt delete using localized URL
        $client->request('POST', sprintf('/en/area/%s', $area->getUuid()), [
            '_token' => 'dummy',
        ]);

        $this->assertResponseRedirects('/en/area');
        $client->followRedirect();

        // Verify flash message or area still exists in database
        $reloadedArea = $em->getRepository(Area::class)->find($area->getId());
        $this->assertNotNull($reloadedArea, 'Area should not be deleted when associated with scheduleds.');
    }
}
