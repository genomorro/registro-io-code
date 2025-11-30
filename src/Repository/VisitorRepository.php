<?php

namespace App\Repository;

use App\Entity\Patient;
use App\Entity\Visitor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Visitor>
 */
class VisitorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visitor::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createTodaysVisitorsByPatientQueryBuilder(Patient $patient): \Doctrine\ORM\QueryBuilder
    {
        $todayStart = new \DateTime('today');
        $todayEnd = new \DateTime('tomorrow');

        return $this->createQueryBuilder('v')
		    ->innerJoin('v.patient', 'p')
		    ->andWhere('p.id = :patientId')
		    ->andWhere('v.checkInAt >= :todayStart')
		    ->andWhere('v.checkInAt < :todayEnd')
		    ->orderBy('v.checkInAt', 'ASC')
		    ->setParameter('patientId', $patient->getId())
		    ->setParameter('todayStart', $todayStart)
		    ->setParameter('todayEnd', $todayEnd)
		    ->orderBy('v.checkInAt', 'DESC')
	;
    }

    public function findOneByTag(int $tag): ?Visitor
    {
        return $this->createQueryBuilder('v')
		    ->andWhere('v.tag = :tag')
		    ->andWhere('v.checkInAt IS NOT NULL')
		    ->andWhere('v.checkOutAt IS NULL')
		    ->setParameter('tag', $tag)
		    ->getQuery()
		    ->getOneOrNullResult()
        ;
    }

    /**
     * @param string $name
     * @return Visitor[]
     */
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('v')
		    ->andWhere('LOWER(v.name) LIKE LOWER(:name)')
		    ->setParameter('name', '%' . str_replace(' ', '%', $name) . '%')
		    ->getQuery()
		    ->getResult();
    }
}
