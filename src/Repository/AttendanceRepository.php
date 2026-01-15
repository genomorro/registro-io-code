<?php

namespace App\Repository;

use App\Entity\Attendance;
use App\Entity\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
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

    public function findLatestByPatientAndDate(Patient $patient, \DateTimeInterface $date): ?Attendance
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
		    ->orderBy('a.checkInAt', 'DESC')
		    ->setMaxResults(1)
		    ->getQuery()
		    ->getOneOrNullResult()
        ;
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

    /**
     * @return Query
     */
    public function paginateAttendance(string $filter = null): Query
    {
        $query = $this->createQueryBuilder('a')
		      ->join('a.patient', 'p')
		      ->addSelect('p')
		      ->orderBy('a.id', 'ASC');

        if ($filter) {
            $query->andWhere('p.name LIKE :filter OR a.tag LIKE :filter')
                  ->setParameter('filter', '%' . $filter . '%');
        }

        return $query->getQuery();
    }
}
