<?php

namespace App\Tests\Controller;

use App\Entity\Area;
use App\Entity\Employee;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AreaControllerTest extends WebTestCase
{
    public function testShowAreaWithEmployeesAndFilter(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');

        // Create Area
        $area = new Area();
        $area->setBuilding('Building Test');
        $area->setUnit('Unit Test');
        $area->setExtension(9999);
        $entityManager->persist($area);

        // Create Employees
        $emp1 = new Employee();
        $emp1->setNumber(1001);
        $emp1->setName('John Doe');
        $emp1->setArea($area);
        $entityManager->persist($emp1);

        $emp2 = new Employee();
        $emp2->setNumber(1002);
        $emp2->setName('Jane Smith');
        $emp2->setArea($area);
        $entityManager->persist($emp2);

        $emp3 = new Employee();
        $emp3->setNumber(1003);
        $emp3->setName('Robert Johnson');
        $emp3->setArea($area);
        $entityManager->persist($emp3);

        $entityManager->flush();

        // Ensure UUID is generated
        $uuid = $area->getUuid();
        $this->assertNotEmpty($uuid);

        // 1. Visit the app_area_show route for this Area in English locale
        $crawler = $client->request('GET', '/en/area/' . $uuid);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Area');
        $this->assertSelectorTextContains('h2', 'Employees');

        // Verify all 3 employees are displayed
        $this->assertStringContainsString('John Doe', $client->getResponse()->getContent());
        $this->assertStringContainsString('Jane Smith', $client->getResponse()->getContent());
        $this->assertStringContainsString('Robert Johnson', $client->getResponse()->getContent());

        // 2. Filter by name (Jane)
        $crawler = $client->request('GET', '/en/area/' . $uuid . '?filter=Jane');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Jane Smith', $client->getResponse()->getContent());
        $this->assertStringNotContainsString('John Doe', $client->getResponse()->getContent());
        $this->assertStringNotContainsString('Robert Johnson', $client->getResponse()->getContent());

        // 3. Filter by work number (1001)
        $crawler = $client->request('GET', '/en/area/' . $uuid . '?filter=1001');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('John Doe', $client->getResponse()->getContent());
        $this->assertStringNotContainsString('Jane Smith', $client->getResponse()->getContent());
        $this->assertStringNotContainsString('Robert Johnson', $client->getResponse()->getContent());
    }
}
