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
     * @return Visitor[] Returns an array of Visitor objects
     */
    public function findTodaysVisitorsByPatient(Patient $patient): array
    {
        $todayStart = new \DateTime('today');
        $todayEnd = new \DateTime('tomorrow');

        return $this->createQueryBuilder('v')
		    ->innerJoin('v.patient', 'p')
		    ->andWhere('p.id = :patientId')
		    ->andWhere('v.checkInAt >= :todayStart')
		    ->andWhere('v.checkInAt < :todayEnd')
		    ->setParameter('patientId', $patient->getId())
		    ->setParameter('todayStart', $todayStart)
		    ->setParameter('todayEnd', $todayEnd)
		    ->orderBy('v.checkInAt', 'DESC')
		    ->getQuery()
		    ->getResult()
        ;
    }
}
