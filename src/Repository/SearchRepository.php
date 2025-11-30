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
    
    /**
     * @param string $tag
     * @return Attendance[]
     */
    public function findCurrentPatientsByTag(string $tag): array
    {
        $startOfDay = new \DateTime('today midnight');
        $endOfDay = new \DateTime('tomorrow midnight');

        return $this->getEntityManager()->createQuery(
            'SELECT a FROM App\Entity\Attendance a
             WHERE a.checkInAt >= :startOfDay AND a.checkInAt < :endOfDay AND a.checkOutAt IS NULL AND a.tag LIKE :tag'
        )
		    ->setParameter('startOfDay', $startOfDay)
		    ->setParameter('endOfDay', $endOfDay)
		    ->setParameter('tag', '%'.$tag.'%')
		    ->getResult();
    }

    /**
     * @param string $tag
     * @return Visitor[]
     */
    public function findCurrentVisitorsByTag(string $tag): array
    {
        $startOfDay = new \DateTime('today midnight');
        $endOfDay = new \DateTime('tomorrow midnight');

        return $this->getEntityManager()->createQuery(
            'SELECT v FROM App\Entity\Visitor v
             WHERE v.checkInAt >= :startOfDay AND v.checkInAt < :endOfDay AND v.checkOutAt IS NULL AND v.tag LIKE :tag'
        )
		    ->setParameter('startOfDay', $startOfDay)
		    ->setParameter('endOfDay', $endOfDay)
		    ->setParameter('tag', '%'.$tag.'%')
		    ->getResult();
    }

    /**
     * @return Attendance[]
     */
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

    /**
     * @return Visitor[]
     */
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

    /**
     * @param string $name
     * @return Attendance[]
     */
    public function findCurrentPatientsByName(string $name): array
    {
        $startOfDay = new \DateTime('today midnight');
        $endOfDay = new \DateTime('tomorrow midnight');

        return $this->getEntityManager()->createQuery(
            'SELECT a FROM App\Entity\Attendance a
	     JOIN a.patient p
             WHERE a.checkInAt >= :startOfDay AND a.checkInAt < :endOfDay AND a.checkOutAt IS NULL AND LOWER(p.name) LIKE LOWER(:name)'
        )
		    ->setParameter('startOfDay', $startOfDay)
		    ->setParameter('endOfDay', $endOfDay)
		    ->setParameter('name', '%'.str_replace(' ', '%', $name).'%')
		    ->getResult();
    }

    /**
     * @param string $name
     * @return Visitor[]
     */
    public function findCurrentVisitorsByName(string $name): array
    {
        $startOfDay = new \DateTime('today midnight');
        $endOfDay = new \DateTime('tomorrow midnight');

        return $this->getEntityManager()->createQuery(
            'SELECT v FROM App\Entity\Visitor v
             WHERE v.checkInAt >= :startOfDay AND v.checkInAt < :endOfDay AND v.checkOutAt IS NULL AND LOWER(v.name) LIKE LOWER(:name)'
        )
		    ->setParameter('startOfDay', $startOfDay)
		    ->setParameter('endOfDay', $endOfDay)
		    ->setParameter('name', '%'.str_replace(' ', '%', $name).'%')
		    ->getResult();
    }
}
