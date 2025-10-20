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
		    ->setParameter('patientId', $patient->getId())
		    ->setParameter('todayStart', $todayStart)
		    ->setParameter('todayEnd', $todayEnd)
		    ->orderBy('v.checkInAt', 'DESC');
    }

    public function findByNameAndCheckInToday(string $name): array
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return $this->createQueryBuilder('v')
            ->where('v.checkInAt >= :today AND v.checkInAt < :tomorrow')
            ->andWhere('v.name LIKE :name')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('name', '%' . $name . '%')
            ->getQuery()
            ->getResult();
    }

    public function findByTagAndCheckInToday(int $tag): array
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');

        return $this->createQueryBuilder('v')
            ->innerJoin('v.patient', 'p')
            ->innerJoin('p.attendances', 'att')
            ->where('v.checkInAt >= :today AND v.checkInAt < :tomorrow')
            ->andWhere('att.tag = :tag')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('tag', $tag)
            ->getQuery()
            ->getResult();
    }
}
