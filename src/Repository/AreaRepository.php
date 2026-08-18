<?php

namespace App\Repository;

use App\Entity\Area;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Area>
 */
class AreaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Area::class);
    }

    /**
     * @retun Query
     */
    public function paginateArea(?string $filter = null): Query
    {
	$query = $this->createQueryBuilder('a')
		      ->orderBy('a.id', 'ASC');

	if ($filter) {
	    $query->andWhere('a.building LIKE :filter OR a.unit LIKE :filter')
		  ->setParameter('filter', '%' . $filter . '%');
	}

	return $query->getQuery();
    }
}
