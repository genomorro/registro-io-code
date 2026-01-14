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

    //    /**
    //     * @return Stakeholder[] Returns an array of Stakeholder objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Stakeholder
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

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
