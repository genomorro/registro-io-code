<?php

namespace App\Repository;

use App\Entity\ScheduledAttendance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScheduledAttendance>
 */
class ScheduledAttendanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScheduledAttendance::class);
    }

    public function findLatestByScheduledAndDate(\App\Entity\Scheduled $scheduled, \DateTimeInterface $date): ?ScheduledAttendance
    {
        $startOfDay = (clone $date)->setTime(0, 0, 0);
        $endOfDay = (clone $date)->setTime(23, 59, 59);

        return $this->createQueryBuilder('sa')
		    ->andWhere('sa.scheduled = :scheduled')
		    ->andWhere('sa.checkInAt >= :startOfDay')
		    ->andWhere('sa.checkInAt <= :endOfDay')
		    ->setParameter('scheduled', $scheduled)
		    ->setParameter('startOfDay', $startOfDay)
		    ->setParameter('endOfDay', $endOfDay)
		    ->orderBy('sa.checkInAt', 'DESC')
		    ->setMaxResults(1)
		    ->getQuery()
		    ->getOneOrNullResult();
    }

    /**
     * @return Query
     */
    public function paginateScheduledAttendance(?string $filter = null): Query
    {
	$query = $this->createQueryBuilder('a')
		      ->join('a.scheduled', 's')
		      ->addSelect('s')
		      ->orderBy('a.id','ASC');

	if ($filter) {
	    $query->andWhere('s.name LIKE :filter')
	    ->setParameter('filter', '%' . $filter . '%');
	}

	return $query->getQuery();
    }
}
