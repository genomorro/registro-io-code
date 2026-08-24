<?php

namespace App\Repository;

use App\Entity\Scheduled;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Scheduled>
 */
class ScheduledRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scheduled::class);
    }

    /**
     * @return Query
     */
    public function paginateScheduled(?string $filter = null): Query
    {
	$query = $this->createQueryBuilder('s')
		      ->orderBy('s.id', 'ASC');

	if($filter) {
	    $query->andWhere('s.label LIKE :filter OR s.name LIKE :filter')
		  ->setParameter('filter', '%' . $filter . '%');
	}

	return $query->getQuery();
    }
}
