<?php

namespace App\Repository;

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
        // You can't use a specific entity here, so we'll just pick one.
        // This won't affect the custom DQL queries.
        parent::__construct($registry, \App\Entity\Patient::class);
    }

    public function findPatientsByTag(string $tag)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT a
                FROM App\Entity\Attendance a
                JOIN a.patient p
                WHERE a.tag LIKE :tag
                AND a.checkInAt >= :start_of_day
                AND a.checkOutAt IS NULL'
            )
            ->setParameter('tag', '%' . $tag . '%')
            ->setParameter('start_of_day', new \DateTime('today'))
            ->getResult();
    }

    public function findVisitorsByTag(string $tag)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT v
                FROM App\Entity\Visitor v
                WHERE v.tag LIKE :tag
                AND v.checkInAt >= :start_of_day
                AND v.checkOutAt IS NULL'
            )
            ->setParameter('tag', '%' . $tag . '%')
            ->setParameter('start_of_day', new \DateTime('today'))
            ->getResult();
    }
}
