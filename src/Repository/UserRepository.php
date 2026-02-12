<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @return Query
     */
    public function paginateUser(string $filter = null): Query
    {
        $query = $this->createQueryBuilder('u')
		      ->orderBy('u.id', 'ASC');

        if ($filter) {
            $query->andWhere('u.username LIKE :filter OR u.name LIKE :filter')
                  ->setParameter('filter', '%' . $filter . '%');
        }

        return $query->getQuery();
    }

    /**
     * @return array
     */
    public function findUserActivityReportData(\DateTimeInterface $today): array
    {
        return $this->getEntityManager()->createQuery(
            'SELECT u.name as userName,
                (SELECT COUNT(a1.id) FROM App\Entity\Attendance a1 WHERE a1.checkInUser = u) as attendanceCheckInTotal,
                (SELECT COUNT(a2.id) FROM App\Entity\Attendance a2 WHERE a2.checkOutUser = u) as attendanceCheckOutTotal,
                (SELECT COUNT(a3.id) FROM App\Entity\Attendance a3 WHERE a3.checkInUser = u AND a3.checkInAt >= :today) as attendanceCheckInToday,
                (SELECT COUNT(a4.id) FROM App\Entity\Attendance a4 WHERE a4.checkOutUser = u AND a4.checkOutAt >= :today) as attendanceCheckOutToday,

                (SELECT COUNT(v1.id) FROM App\Entity\Visitor v1 WHERE v1.checkInUser = u) as visitorCheckInTotal,
                (SELECT COUNT(v2.id) FROM App\Entity\Visitor v2 WHERE v2.checkOutUser = u) as visitorCheckOutTotal,
                (SELECT COUNT(v3.id) FROM App\Entity\Visitor v3 WHERE v3.checkInUser = u AND v3.checkInAt >= :today) as visitorCheckInToday,
                (SELECT COUNT(v4.id) FROM App\Entity\Visitor v4 WHERE v4.checkOutUser = u AND v4.checkOutAt >= :today) as visitorCheckOutToday,

                (SELECT COUNT(s1.id) FROM App\Entity\Stakeholder s1 WHERE s1.checkInUser = u) as stakeholderCheckInTotal,
                (SELECT COUNT(s2.id) FROM App\Entity\Stakeholder s2 WHERE s2.checkOutUser = u) as stakeholderCheckOutTotal,
                (SELECT COUNT(s3.id) FROM App\Entity\Stakeholder s3 WHERE s3.checkInUser = u AND s3.checkInAt >= :today) as stakeholderCheckInToday,
                (SELECT COUNT(s4.id) FROM App\Entity\Stakeholder s4 WHERE s4.checkOutUser = u AND s4.checkOutAt >= :today) as stakeholderCheckOutToday
             FROM App\Entity\User u
             ORDER BY u.name ASC'
        )
        ->setParameter('today', $today)
        ->getResult();
    }
}
