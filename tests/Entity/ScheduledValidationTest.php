<?php

namespace App\Tests\Entity;

use App\Entity\Area;
use App\Entity\Scheduled;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ScheduledValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidScheduled(): void
    {
        $area = new Area();
        $area->setBuilding('Edificio 1');

        $scheduled = new Scheduled();
        $scheduled->setLabel('LABEL001');
        $scheduled->setName('John Doe');
        $scheduled->setInstitution('Test Inst');
        $scheduled->setSubject('Cultura');
        $scheduled->setBeginAt(new \DateTimeImmutable('2025-01-01'));
        $scheduled->setEndAt(new \DateTimeImmutable('2025-01-02'));
        $scheduled->setArea($area);

        $violations = $this->validator->validate($scheduled);
        $this->assertCount(0, $violations);
    }

    public function testEndAtSameAsBeginAtIsValid(): void
    {
        $area = new Area();
        $area->setBuilding('Edificio 1');

        $scheduled = new Scheduled();
        $scheduled->setLabel('LABEL002');
        $scheduled->setName('John Doe');
        $scheduled->setInstitution('Test Inst');
        $scheduled->setSubject('Cultura');
        $scheduled->setBeginAt(new \DateTimeImmutable('2025-01-01'));
        $scheduled->setEndAt(new \DateTimeImmutable('2025-01-01'));
        $scheduled->setArea($area);

        $violations = $this->validator->validate($scheduled);
        $this->assertCount(0, $violations);
    }

    public function testEndAtBeforeBeginAtIsInvalid(): void
    {
        $area = new Area();
        $area->setBuilding('Edificio 1');

        $scheduled = new Scheduled();
        $scheduled->setLabel('LABEL003');
        $scheduled->setName('John Doe');
        $scheduled->setInstitution('Test Inst');
        $scheduled->setSubject('Cultura');
        $scheduled->setBeginAt(new \DateTimeImmutable('2025-01-05'));
        $scheduled->setEndAt(new \DateTimeImmutable('2025-01-01'));
        $scheduled->setArea($area);

        $violations = $this->validator->validate($scheduled);
        $this->assertGreaterThan(0, count($violations));
        $this->assertEquals('endAt', $violations[0]->getPropertyPath());
    }

    public function testNullAreaIsInvalid(): void
    {
        $scheduled = new Scheduled();
        $scheduled->setLabel('LABEL004');
        $scheduled->setName('John Doe');
        $scheduled->setInstitution('Test Inst');
        $scheduled->setSubject('Cultura');
        $scheduled->setBeginAt(new \DateTimeImmutable('2025-01-01'));
        $scheduled->setEndAt(new \DateTimeImmutable('2025-01-02'));
        $scheduled->setArea(null);

        $violations = $this->validator->validate($scheduled);
        $this->assertGreaterThan(0, count($violations));
        $this->assertEquals('area', $violations[0]->getPropertyPath());
    }
}
