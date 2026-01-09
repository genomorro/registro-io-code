<?php

namespace App\Repository;

use App\Entity\Hospitalized;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hospitalized>
 */
class HospitalizedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hospitalized::class);
    }

    /**
     * @param Patient $patient
     * @return Hospitalized|null
     */
    public function findOneByPatient(Patient $patient): ?Hospitalized
    {
        return $this->createQueryBuilder('h')
		    ->andWhere('h.patient = :patient')
		    ->setParameter('patient', $patient)
		    ->getQuery()
		    ->getOneOrNullResult()
        ;
    }
}
