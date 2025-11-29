<?php

namespace App\Repository;

use App\Entity\Attendance;
use App\Entity\Visitor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null find($id, $lockMode = null, $lockVersion = null)
 * @method null findOneBy(array $criteria, array $orderBy = null)
 * @method []    findAll()
 * @method []    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SearchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, \App\Entity\Patient::class);
    }

    public function findCurrentPatientsByTag(string $tag): array
    {
        $startOfDay = new \DateTime('today');
        $endOfDay = new \DateTime('tomorrow');

        return $this->getEntityManager()->createQuery(
            'SELECT a FROM App\Entity\Attendance a
             WHERE a.checkInAt >= :startOfDay AND a.checkInAt < :endOfDay AND a.checkOutAt IS NULL AND a.tag LIKE :tag'
        )
		    ->setParameter('startOfDay', $startOfDay)
		    ->setParameter('endOfDay', $endOfDay)
		    ->setParameter('tag', '%'.$tag.'%')
		    ->getResult();
    }

    public function findCurrentVisitorsByTag(string $tag): array
    {
        $startOfDay = new \DateTime('today');
        $endOfDay = new \DateTime('tomorrow');

        return $this->getEntityManager()->createQuery(
            'SELECT v FROM App\Entity\Visitor v
             WHERE v.checkInAt >= :startOfDay AND v.checkInAt < :endOfDay AND v.checkOutAt IS NULL AND v.tag LIKE :tag'
        )
		    ->setParameter('startOfDay', $startOfDay)
		    ->setParameter('endOfDay', $endOfDay)
		    ->setParameter('tag', '%'.$tag.'%')
		    ->getResult();
    }

    public function findCurrentPatients(): array
    {
        $startOfDay = new \DateTime('today midnight');
        $endOfDay = new \DateTime('today 23:59:59');

        return $this->getEntityManager()->createQuery(
            'SELECT a FROM App\Entity\Attendance a
             WHERE a.checkInAt >= :startOfDay AND a.checkInAt < :endOfDay AND a.checkOutAt IS NULL'
        )
		    ->setParameter('startOfDay', $startOfDay)
		    ->setParameter('endOfDay', $endOfDay)
		    ->getResult();
    }

    public function findCurrentVisitors(): array
    {
        $startOfDay = new \DateTime('today midnight');
        $endOfDay = new \DateTime('today 23:59:59');

        return $this->getEntityManager()->createQuery(
            'SELECT v FROM App\Entity\Visitor v
             WHERE v.checkInAt >= :startOfDay AND v.checkInAt < :endOfDay AND v.checkOutAt IS NULL'
        )
		    ->setParameter('startOfDay', $startOfDay)
		    ->setParameter('endOfDay', $endOfDay)
		    ->getResult();
    }
}
