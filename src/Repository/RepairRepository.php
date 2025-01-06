<?php

namespace App\Repository;

use App\Entity\Repair;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    public function findAllQuery(
        bool $withId = false,
        bool $withVehicle = false,
        bool $withPart = false,
        bool $withPrice = false,
        bool $withDateRepair = false,
        bool $withDescription = false,
    ):QueryBuilder{
        $query = $this->createQueryBuilder('r');
        
        if($withId){
            $query->addSelect('r.id');
        }
        if($withVehicle){
            $query->leftJoin('r.vehicle', 'v')
                ->addSelect('v');
        }
        if($withPart){
            $query->addSelect('r.part');
        }
        if($withPrice){
            $query->addSelect('r.price');
        }
        if($withDateRepair){
            $query->addSelect('r.dateRepair');
        }
        if($withDescription){
            $query->addSelect('r.description');
        }

        return $query;
    }

    public function findAllByVehicle($user): array{
        return $this->findAllQuery(withVehicle: true)
            ->where('v.owner = :ownerId')
            ->setParameter('ownerId', $user->getId())
            ->getQuery()
            ->getResult();
    }

    // public function findAllByVehicle($user): array{
    //     $query = $this->createQueryBuilder('r')
    //         ->leftJoin('r.vehicle', 'v')
    //         ->addSelect('v')
    //         ->leftJoin('v.owner', 'o')
    //         ->addSelect('o')
    //         ->where('v.owner = :ownerId')
    //         ->setParameter('ownerId', $user->getId())
    //         ->getQuery();

    //     return $query->getResult();
    // }

    public function deleteRepair(Repair $repair){
        $entityManager = $this->getEntityManager();
        $entityManager->remove($repair);
        $entityManager->flush();
    }

    public function getTotalRepairCost($user){
        return $this->findAllQuery(withVehicle: true, withPrice: true)
            ->select('SUM(r.price)')
            ->where('v.owner = :ownerId')
            ->setParameter('ownerId', $user->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function mostRepairs($user){
        return $this->findAllQuery(withVehicle: true)
            ->select('COUNT(r.vehicle) AS count')
            ->where('v.owner = :ownerId')
            ->setParameter('ownerId', $user->getId())
            ->addSelect('v.brand')
            ->addSelect('v.model')
            ->addSelect('v.numberPlate')
            ->groupBy('r.vehicle')
            ->orderBy('count', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function newestRepair($user){
        return $this->findAllQuery(
            withId: true,
            withVehicle: true,
            withPrice: true,
            withPart: true,
            withDateRepair: true
        )
            ->where('v.owner = :ownerId')
            ->setParameter('ownerId', $user->getId())
            ->orderBy('r.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }


    // public function getTotalRepairCost($user){
    //     $query = $this->createQueryBuilder('r')
    //         ->select('SUM(r.price)')
    //         ->innerJoin('r.vehicle', 'v')
    //         ->where('v.owner = :ownerId')
    //         ->setParameter('ownerId', $user->getId())
    //         ->getQuery();

    //     $sum = $query->getSingleScalarResult();
    //     // var_dump($sum);
    //     return (float) $sum;
    // }

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
