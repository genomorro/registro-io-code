<?php

namespace App\DataFixtures;

use App\Entity\Appointment;
use App\Entity\Patient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create 10 Patients
        for ($i = 0; $i < 10; $i++) {
            $patient = new Patient();
            $patient->setName('Patient ' . $i);
            $patient->setFile('P00' . $i);
            $patient->setDisability(false);
            $manager->persist($patient);

            // Create 5 appointments for each patient
            for ($j = 0; $j < 5; $j++) {
                $appointment = new Appointment();
                $appointment->setPatient($patient);
                $appointment->setPlace('Clinic ' . $j);
                $appointment->setDateAt(new \DateTimeImmutable('now + ' . ($i * 5 + $j) . ' days'));
                $appointment->setType('Checkup');
                $manager->persist($appointment);
            }
        }

        $manager->flush();
    }
}
