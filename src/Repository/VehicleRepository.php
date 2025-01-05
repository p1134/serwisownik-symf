<?php

namespace App\Repository;

use DateTime;
use App\Entity\Vehicle;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Vehicle>
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    public function findAllQuery(
        bool $withId = false,
        bool $withOwner = false,
        bool $withBrand = false,
        bool $withModel = false,
        bool $withYear = false,
        bool $withNumberPlate = false,
        bool $withDatePurchase = false,
        bool $withService = false,
    ): QueryBuilder{
        $query = $this->createQueryBuilder('v');

        if($withId){
                $query->addSelect('v.id');
        }
        if($withOwner){
            $query->leftJoin('v.owner', 'o')
                ->addSelect('o');
        }
        if($withBrand){
                $query->addSelect('v.brand');
        }
        if($withModel){
                $query->addSelect('v.model');
        }
        if($withYear){
                $query->addSelect('v.year');
        }
        if($withNumberPlate){
                $query->addSelect('v.numberPlate');
        }
        if($withDatePurchase){
                $query->addSelect('v.datePurchase');
        }
        if($withService){
                $query->addSelect('v.service');
        }

        return $query;
    }

    public function findAllByOwner($user):array{
        return $this->findAllQuery(withOwner: true)
            ->where('v.owner = :ownerId')
            ->setParameter('ownerId', $user->getId())
            ->getQuery()
            ->getResult();

    }

    public function nextService($user, $now){
            return $this->findAllQuery(withOwner: true, withService: true)
                ->select('MIN(v.service) AS min_service')
                ->where('v.owner = :ownerId')
                ->setParameter('ownerId', $user->getId())
                ->andWhere('v.service >= :now')
                ->setParameter('now', $now)
                ->getQuery()
                ->getSingleScalarResult();
    }


    // public function findAllByOwner($user):array{
    //     $query = $this->createQueryBuilder('v')
    //         ->leftJoin('v.owner', 'o')
    //         ->addSelect('o')
    //         ->where('o.id = :ownerId')
    //         ->setParameter('ownerId', $user->getId())
    //         ->getQuery();

    //     return $query->getResult();

    // }

    public function deleteVehicle(Vehicle $vehicle){
        $entityManager = $this->getEntityManager();
       $entityManager ->remove($vehicle);
        $entityManager->flush();
    }

    public function lastAddedVehicle($user){
        return $this->findAllQuery(
            withOwner: true, 
            withId: true, 
            withBrand: true, 
            withModel: true,
            withYear: true,
            withNumberPlate: true,
            )
            ->where('v.owner = :ownerId')
            ->setParameter('ownerId', $user->getId())
            ->orderBy('v.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    public function oldestVehicle($user){
        return $this->findAllQuery(
            withYear: true,
            withBrand: true,
            withModel: true,
            withNumberPlate: true,
        )
        ->where('v.owner = :ownerId')
        ->setParameter('ownerId', $user->getId())
        ->orderBy('v.year', 'ASC')
        ->setMaxResults(1)
        ->getQuery()
        ->getResult();
    }

    // public function nextService($user, $now){
    //     $query = $this->createQueryBuilder('v')
    //         ->leftJoin('v.owner', 'o')
    //         ->addSelect('o')
    //         ->where('v.owner = :ownerId')
    //         ->setParameter('ownerId', $user->getId())
    //         ->select('MIN(v.service) as min_service')
    //         ->andWhere('v.service >= :now')
    //         ->setParameter('now', $now)
    //         ->getQuery();
        
    //         // var_dump($query->getResult());
    //         // var_dump($now);
    //     return $query->getSingleScalarResult();
            
    // }

    
    //    /**
    //     * @return Vehicle[] Returns an array of Vehicle objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Vehicle
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
