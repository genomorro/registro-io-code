<?php

namespace App\Tests\Controller;

use App\Entity\Appointment;
use App\Entity\Area;
use App\Entity\Attendance;
use App\Entity\Employee;
use App\Entity\Patient;
use App\Entity\Stakeholder;
use App\Entity\User;
use App\Entity\Visitor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeletionPreventionTest extends WebTestCase
{
    private function createSuperAdmin(EntityManagerInterface $em): User
    {
        $user = new User();
        $user->setUsername('superadmin_' . uniqid());
        $user->setName('Super Admin');
        $user->setPassword('password');
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setActive(true);

        $em->persist($user);
        $em->flush();

        return $user;
    }

    public function testPatientCannotBeDeletedWithAssociations(): void
    {
        $client = static::createClient();
        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $router = $client->getContainer()->get('router');

        $superAdmin = $this->createSuperAdmin($em);
        $client->loginUser($superAdmin);

        // 1. Patient with Appointment
        $patient1 = new Patient();
        $patient1->setFile('F' . rand(10000, 99999));
        $patient1->setName('Patient With Appointment');
        $patient1->setDisability(false);
        $em->persist($patient1);

        $appointment = new Appointment();
        $appointment->setAgenda('Agenda 1');
        $appointment->setSpecialty('Specialty');
        $appointment->setLocation('Room 1');
        $appointment->setDateAt(new \DateTimeImmutable());
        $appointment->setType('Primera vez');
        $appointment->setStatus('Agendada');
        $appointment->setPatient($patient1);
        $em->persist($appointment);

        // 2. Patient with Attendance
        $patient2 = new Patient();
        $patient2->setFile('F' . rand(10000, 99999));
        $patient2->setName('Patient With Attendance');
        $patient2->setDisability(false);
        $em->persist($patient2);

        $attendance = new Attendance();
        $attendance->setCheckInAt(new \DateTimeImmutable());
        $attendance->setTag(101);
        $attendance->setPatient($patient2);
        $attendance->setCheckInUser($superAdmin);
        $em->persist($attendance);

        // 3. Patient with Visitor
        $patient3 = new Patient();
        $patient3->setFile('F' . rand(10000, 99999));
        $patient3->setName('Patient With Visitor');
        $patient3->setDisability(false);
        $em->persist($patient3);

        $area = new Area();
        $area->setBuilding('Building A');
        $em->persist($area);

        $visitor = new Visitor();
        $visitor->setName('Visitor 1');
        $visitor->setDni('12345678');
        $visitor->setTag(201);
        $visitor->setDestination($area);
        $visitor->setCheckInAt(new \DateTimeImmutable());
        $visitor->setCheckInUser($superAdmin);
        $visitor->addPatient($patient3);
        $em->persist($visitor);

        // 4. Unassociated Patient
        $patient4 = new Patient();
        $patient4->setFile('F' . rand(10000, 99999));
        $patient4->setName('Clean Patient');
        $patient4->setDisability(false);
        $em->persist($patient4);

        $em->flush();

        // Attempt deleting patient1
        $url1 = $router->generate('app_patient_delete', ['id' => $patient1->getUuid()]);
        $client->request('POST', $url1);
        $this->assertResponseRedirects();
        $patient1Db = $em->getRepository(Patient::class)->find($patient1->getId());
        $this->assertNotNull($patient1Db, 'Patient with Appointment must not be deleted');

        // Attempt deleting patient2
        $url2 = $router->generate('app_patient_delete', ['id' => $patient2->getUuid()]);
        $client->request('POST', $url2);
        $this->assertResponseRedirects();
        $patient2Db = $em->getRepository(Patient::class)->find($patient2->getId());
        $this->assertNotNull($patient2Db, 'Patient with Attendance must not be deleted');

        // Attempt deleting patient3
        $url3 = $router->generate('app_patient_delete', ['id' => $patient3->getUuid()]);
        $client->request('POST', $url3);
        $this->assertResponseRedirects();
        $patient3Db = $em->getRepository(Patient::class)->find($patient3->getId());
        $this->assertNotNull($patient3Db, 'Patient with Visitor must not be deleted');

        // Attempt deleting patient4 (clean) using show page form token
        $crawler = $client->request('GET', $router->generate('app_patient_show', ['id' => $patient4->getUuid()]));
        $token = $crawler->filter('input[name="_token"]')->attr('value');
        $url4 = $router->generate('app_patient_delete', ['id' => $patient4->getUuid()]);
        $client->request('POST', $url4, ['_token' => $token]);
        $this->assertResponseRedirects();
        $em->clear();
        $patient4Db = $em->getRepository(Patient::class)->find($patient4->getId());
        $this->assertNull($patient4Db, 'Clean patient should be deleted');
    }

    public function testUserCannotBeDeletedWithAssociations(): void
    {
        $client = static::createClient();
        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $router = $client->getContainer()->get('router');

        $superAdmin = $this->createSuperAdmin($em);
        $client->loginUser($superAdmin);

        $patient = new Patient();
        $patient->setFile('F' . rand(10000, 99999));
        $patient->setName('Patient Test');
        $patient->setDisability(false);
        $em->persist($patient);

        $area = new Area();
        $area->setBuilding('Building B');
        $em->persist($area);

        // User associated with Attendance
        $user1 = new User();
        $user1->setUsername('user1_' . uniqid());
        $user1->setName('User One');
        $user1->setPassword('pass');
        $user1->setActive(true);
        $em->persist($user1);

        $attendance = new Attendance();
        $attendance->setCheckInAt(new \DateTimeImmutable());
        $attendance->setTag(102);
        $attendance->setPatient($patient);
        $attendance->setCheckInUser($user1);
        $em->persist($attendance);

        // Clean User
        $userClean = new User();
        $userClean->setUsername('userclean_' . uniqid());
        $userClean->setName('User Clean');
        $userClean->setPassword('pass');
        $userClean->setActive(true);
        $em->persist($userClean);

        $em->flush();

        // Attempt delete user1
        $url1 = $router->generate('app_user_delete', ['id' => $user1->getUuid()]);
        $client->request('POST', $url1);
        $this->assertResponseRedirects();
        $user1Db = $em->getRepository(User::class)->find($user1->getId());
        $this->assertNotNull($user1Db, 'User with Attendance must not be deleted');

        // Attempt delete userClean
        $crawler = $client->request('GET', $router->generate('app_user_show', ['id' => $userClean->getUuid()]));
        $token = $crawler->filter('input[name="_token"]')->attr('value');
        $urlClean = $router->generate('app_user_delete', ['id' => $userClean->getUuid()]);
        $client->request('POST', $urlClean, ['_token' => $token]);
        $this->assertResponseRedirects();
        $em->clear();
        $userCleanDb = $em->getRepository(User::class)->find($userClean->getId());
        $this->assertNull($userCleanDb, 'Clean user should be deleted');
    }

    public function testAreaCannotBeDeletedWithAssociations(): void
    {
        $client = static::createClient();
        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $router = $client->getContainer()->get('router');

        $superAdmin = $this->createSuperAdmin($em);
        $client->loginUser($superAdmin);

        // Area with Employee
        $areaWithEmployee = new Area();
        $areaWithEmployee->setBuilding('Building C');
        $em->persist($areaWithEmployee);

        $employee = new Employee();
        $employee->setNumber(rand(1000, 9999));
        $employee->setName('Employee One');
        $employee->setArea($areaWithEmployee);
        $employee->setActive(true);
        $em->persist($employee);

        // Clean Area
        $areaClean = new Area();
        $areaClean->setBuilding('Building Clean');
        $em->persist($areaClean);

        $em->flush();

        // Attempt delete areaWithEmployee
        $url1 = $router->generate('app_area_delete', ['id' => $areaWithEmployee->getUuid()]);
        $client->request('POST', $url1);
        $this->assertResponseRedirects();
        $areaDb = $em->getRepository(Area::class)->find($areaWithEmployee->getId());
        $this->assertNotNull($areaDb, 'Area with Employee must not be deleted');

        // Attempt delete areaClean
        $crawler = $client->request('GET', $router->generate('app_area_show', ['id' => $areaClean->getUuid()]));
        $token = $crawler->filter('input[name="_token"]')->attr('value');
        $urlClean = $router->generate('app_area_delete', ['id' => $areaClean->getUuid()]);
        $client->request('POST', $urlClean, ['_token' => $token]);
        $this->assertResponseRedirects();
        $em->clear();
        $areaCleanDb = $em->getRepository(Area::class)->find($areaClean->getId());
        $this->assertNull($areaCleanDb, 'Clean area should be deleted');
    }

    public function testEmployeeCannotBeDeletedWithAssociations(): void
    {
        $client = static::createClient();
        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $router = $client->getContainer()->get('router');

        $superAdmin = $this->createSuperAdmin($em);
        $client->loginUser($superAdmin);

        $area = new Area();
        $area->setBuilding('Building D');
        $em->persist($area);

        // Employee with Stakeholder host
        $employeeWithHost = new Employee();
        $employeeWithHost->setNumber(rand(1000, 9999));
        $employeeWithHost->setName('Employee Host');
        $employeeWithHost->setArea($area);
        $employeeWithHost->setActive(true);
        $em->persist($employeeWithHost);

        $stakeholder = new Stakeholder();
        $stakeholder->setName('Stakeholder 1');
        $stakeholder->setDni('87654321');
        $stakeholder->setTag(301);
        $stakeholder->setCompany('Company A');
        $stakeholder->setDestination($area);
        $stakeholder->setSubject('Work');
        $stakeholder->setCheckInAt(new \DateTimeImmutable());
        $stakeholder->setCheckInUser($superAdmin);
        $stakeholder->setHost($employeeWithHost);
        $em->persist($stakeholder);

        // Clean Employee
        $employeeClean = new Employee();
        $employeeClean->setNumber(rand(1000, 9999));
        $employeeClean->setName('Employee Clean');
        $employeeClean->setArea($area);
        $employeeClean->setActive(true);
        $em->persist($employeeClean);

        $em->flush();

        // Attempt delete employeeWithHost
        $url1 = $router->generate('app_employee_delete', ['id' => $employeeWithHost->getUuid()]);
        $client->request('POST', $url1);
        $this->assertResponseRedirects();
        $empDb = $em->getRepository(Employee::class)->find($employeeWithHost->getId());
        $this->assertNotNull($empDb, 'Employee with Stakeholder must not be deleted');

        // Attempt delete employeeClean
        $crawler = $client->request('GET', $router->generate('app_employee_show', ['id' => $employeeClean->getUuid()]));
        $token = $crawler->filter('input[name="_token"]')->attr('value');
        $urlClean = $router->generate('app_employee_delete', ['id' => $employeeClean->getUuid()]);
        $client->request('POST', $urlClean, ['_token' => $token]);
        $this->assertResponseRedirects();
        $em->clear();
        $empCleanDb = $em->getRepository(Employee::class)->find($employeeClean->getId());
        $this->assertNull($empCleanDb, 'Clean employee should be deleted');
    }
}
