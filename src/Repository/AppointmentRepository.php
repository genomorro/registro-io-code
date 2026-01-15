<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
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
        $todayStart = new \DateTime('today midnight');
        $todayEnd = new \DateTime('tomorrow midnight');

        return $this->createQueryBuilder('a')
		    ->andWhere('a.patient = :patient')
		    ->andWhere('a.date_at >= :todayStart')
		    ->andWhere('a.date_at < :todayEnd')
		    ->setParameter('patient', $patient)
		    ->setParameter('todayStart', $todayStart)
		    ->setParameter('todayEnd', $todayEnd)
		    ->orderBy('a.date_at', 'ASC');
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createOtherAppointmentsByPatientQueryBuilder(Patient $patient): \Doctrine\ORM\QueryBuilder
    {
        $tomorrow = new \DateTime('tomorrow midnight');

        return $this->createQueryBuilder('a')
		    ->andWhere('a.patient = :patient')
		    ->andWhere('a.date_at >= :tomorrow')
		    ->setParameter('patient', $patient)
		    ->setParameter('tomorrow', $tomorrow)
		    ->orderBy('a.date_at', 'ASC');
    }

    /**
     * @return Query
     */
    public function paginateAppointment(string $filter = null): Query
    {
        $query = $this->createQueryBuilder('a')
		      ->join('a.patient', 'p')
		      ->addSelect('p')
		      ->orderBy('a.id', 'ASC');

        if ($filter) {
            $query->andWhere('p.name LIKE :filter OR a.agenda LIKE :filter OR a.date_at LIKE :filter')
                  ->setParameter('filter', '%' . $filter . '%');
        }

        return $query->getQuery();
    }
}
