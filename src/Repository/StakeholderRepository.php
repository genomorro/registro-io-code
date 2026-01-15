<?php

namespace App\Repository;

use App\Entity\Stakeholder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stakeholder>
 */
class StakeholderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stakeholder::class);
    }

    /**
     * @return Query
     */
    public function paginateStakeholder(string $filter = null): Query
    {
        $query = $this->createQueryBuilder('s')
		      ->orderBy('s.id', 'ASC');

        if ($filter) {
            $query->andWhere('s.name LIKE :filter OR s.tag LIKE :filter')
                  ->setParameter('filter', '%' . $filter . '%');
        }

        return $query->getQuery();
    }
}
