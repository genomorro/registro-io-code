<?php

namespace App\Repository;

use App\Entity\Attendance;
use App\Entity\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Attendance>
 */
class AttendanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attendance::class);
    }

    public function findOneByPatientAndDate(Patient $patient, \DateTimeInterface $date): ?Attendance
    {
        $startOfDay = (clone $date)->setTime(0, 0, 0);
        $endOfDay = (clone $date)->setTime(23, 59, 59);

        return $this->createQueryBuilder('a')
		    ->andWhere('a.patient = :patient')
		    ->andWhere('a.checkInAt >= :startOfDay')
		    ->andWhere('a.checkInAt <= :endOfDay')
		    ->setParameter('patient', $patient)
		    ->setParameter('startOfDay', $startOfDay)
		    ->setParameter('endOfDay', $endOfDay)
		    ->getQuery()
		    ->getOneOrNullResult()
        ;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findAllWithPatient(): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder('a')
		    ->select('a', 'p')
		    ->leftJoin('a.patient', 'p')
		    ->orderBy('a.checkInAt', 'ASC');
    }

    public function findPatientByTag(int $tag): ?Patient
    {
        $attendance = $this->createQueryBuilder('a')
			   ->where('a.tag = :tag')
			   ->andWhere('a.checkInAt IS NOT NULL')
			   ->andWhere('a.checkOutAt IS NULL')
			   ->orderBy('a.checkInAt', 'ASC')
			   ->setParameter('tag', $tag)
			   ->getQuery()
			   ->getOneOrNullResult();

        if ($attendance) {
            return $attendance->getPatient();
        }

        return null;
    }
}
