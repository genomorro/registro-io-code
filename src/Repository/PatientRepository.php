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
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return $this->createQueryBuilder('p')
		    ->select('p', 'a', 'att')
		    ->innerJoin('p.appointments', 'a', 'WITH', 'a.date_at >= :today AND a.date_at < :tomorrow')
		    ->leftJoin('p.attendances', 'att', 'WITH', 'att.checkInAt >= :today AND att.checkInAt < :tomorrow')
		    ->setParameter('today', $today)
		    ->setParameter('tomorrow', $tomorrow);
    }

    /**
     * @param string $file
     * @return Patient|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByFile(string $file): ?Patient
    {
        return $this->createQueryBuilder('p')
		    ->andWhere('p.file LIKE :file')
		    ->setParameter('file', '%' . $file)
		    ->getQuery()->getOneOrNullResult();
    }
}
