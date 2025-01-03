<?php

namespace App\Repository;

use App\Entity\Repair;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Repair>
 */
class RepairRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Repair::class);
    }

    public function findAllByVehicle($user): array{
        $query = $this->createQueryBuilder('r')
            ->leftJoin('r.vehicle', 'v')
            ->addSelect('v')
            ->leftJoin('v.owner', 'o')
            ->addSelect('o')
            ->where('v.owner = :ownerId')
            ->setParameter('ownerId', $user->getId())
            ->getQuery();

        return $query->getResult();
    }

    public function deleteRepair(Repair $repair){
        $entityManager = $this->getEntityManager();
        $entityManager->remove($repair);
        $entityManager->flush();
    }

    //    /**
    //     * @return Repair[] Returns an array of Repair objects
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

    //    public function findOneBySomeField($value): ?Repair
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
