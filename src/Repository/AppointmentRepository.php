<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appointment>
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createTodaysAppointmentsByPatientQueryBuilder(Patient $patient): \Doctrine\ORM\QueryBuilder
    {
        $todayStart = new \DateTime('today');
        $todayEnd = new \DateTime('tomorrow');

        return $this->createQueryBuilder('a')
		    ->andWhere('a.patient = :patient')
		    ->andWhere('a.date_at >= :todayStart')
		    ->andWhere('a.date_at < :todayEnd')
		    ->setParameter('patient', $patient)
		    ->setParameter('todayStart', $todayStart)
		    ->setParameter('todayEnd', $todayEnd)
		    ->orderBy('a.date_at', 'DESC');
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createOtherAppointmentsByPatientQueryBuilder(Patient $patient): \Doctrine\ORM\QueryBuilder
    {
        $todayStart = new \DateTime('today');

        return $this->createQueryBuilder('a')
		    ->andWhere('a.patient = :patient')
		    ->andWhere('a.date_at != :todayStart')
		    ->setParameter('patient', $patient)
		    ->setParameter('todayStart', $todayStart)
		    ->orderBy('a.date_at', 'DESC');
    }
}
