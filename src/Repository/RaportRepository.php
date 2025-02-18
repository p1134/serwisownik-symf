<?php

namespace App\Repository;

use App\Entity\Raport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\GroupBy;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Raport>
 */
class RaportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Raport::class);
    }

    public function getAllRaports($user){
        $query = $this->createQueryBuilder('r');
        $query->leftJoin('r.user', 'u')
            ->addSelect('r.pdf', 'r.dateCreate', 'r.filename', 'r.id')
            ->addSelect('u')
            ->where('u.id = :userID')
            ->setParameter('userID', $user->getId())
            ->orderBy('r.filename', 'DESC');

        return $query->getQuery()->getResult();
    }

    //    /**
    //     * @return Raport[] Returns an array of Raport objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Raport
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
