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
