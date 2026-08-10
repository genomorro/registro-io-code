<?php

namespace App\Tests\Entity;

use App\Entity\Employee;
use App\Entity\Visitor;
use App\Entity\Stakeholder;
use PHPUnit\Framework\TestCase;

class EmployeeHostTest extends TestCase
{
    public function testVisitorHostRelationship(): void
    {
        $employee = new Employee();
        $employee->setName('John Doe');

        $visitor = new Visitor();
        $visitor->setName('Jane Smith');

        $visitor->setHost($employee);

        $this->assertSame($employee, $visitor->getHost());
        $this->assertEquals('John Doe', $visitor->getHost()->getName());
    }

    public function testStakeholderHostRelationship(): void
    {
        $employee = new Employee();
        $employee->setName('John Doe');

        $stakeholder = new Stakeholder();
        $stakeholder->setName('Stakeholder Corp');

        $stakeholder->setHost($employee);

        $this->assertSame($employee, $stakeholder->getHost());
        $this->assertEquals('John Doe', $stakeholder->getHost()->getName());
    }
}
