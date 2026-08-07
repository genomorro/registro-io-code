<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employee>
 */
class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    //    /**
    //     * @return Employee[] Returns an array of Employee objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Employee
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @return Query
     */
    public function paginateEmployee(string $filter = null): Query
    {
	$query = $this->createQueryBuilder('e')
		      ->leftJoin('e.area', 'a')
		      ->addSelect('a')
		      ->orderBy('e.id', 'ASC');

	if ($filter) {
	    $query->andWhere('e.number LIKE :filter OR e.name LIKE :filter')
		  ->setParameter('filter', '%' . $filter . '%');
	}

	return $query->getQuery();
    }
}
