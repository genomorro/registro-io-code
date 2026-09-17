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
        $today = new \DateTime('today midnight');
        $tomorrow = new \DateTime('tomorrow midnight');

	$query = $this->createQueryBuilder('s')
		      ->leftJoin('s.scheduledAttendances', 'sa', 'WITH', 'sa.checkInAt >= :today AND sa.checkInAt < :tomorrow')
		      ->addSelect('sa')
		      ->orderBy('s.id', 'ASC');

	if ($filter) {
	    $query->andWhere('s.label LIKE :filter OR s.name LIKE :filter')
		  ->setParameter('filter', '%' . $filter . '%');
	}

	$query->setParameter('today', $today)
	      ->setParameter('tomorrow', $tomorrow);

	return $query->getQuery();
    }
}
