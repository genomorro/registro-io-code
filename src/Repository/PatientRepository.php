<?php

namespace App\Repository;

use App\Entity\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Patient>
 */
class PatientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Patient::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findWithAppointmentsAndAttendanceTodayQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        $today = new \DateTime('today midnight');
        $tomorrow = new \DateTime('tomorrow midnight');

        return $this->createQueryBuilder('p')
		    ->select('p', 'a', 'att')
		    ->innerJoin('p.appointments', 'a', 'WITH', 'a.date_at >= :today AND a.date_at < :tomorrow')
		    ->leftJoin('p.attendances', 'att', 'WITH', 'att.checkInAt >= :today AND att.checkInAt < :tomorrow')
		    ->orderBy('a.date_at', 'ASC')
		    ->orderBy('att.checkInAt', 'ASC')
		    ->setParameter('today', $today)
		    ->setParameter('tomorrow', $tomorrow);
    }

    /**
     * @param string $file
     * @return Patient[]
     */
    public function findByFile(string $file): array
    {
        return $this->createQueryBuilder('p')
		    ->andWhere('p.file LIKE :file')
		    ->setParameter('file', '%' . $file)
		    ->getQuery()
		    ->getResult();
    }

    /**
     * @param string $name
     * @return Patient[]
     */
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('p')
		    ->andWhere('LOWER(p.name) LIKE LOWER(:name)')
		    ->setParameter('name', '%' . str_replace(' ', '%', $name) . '%')
		    ->getQuery()
		    ->getResult();
    }
}
