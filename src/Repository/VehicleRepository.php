<?php

namespace App\Repository;

use App\Entity\Vehicle;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vehicle>
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }


    public function findAllByOwner($user):array{
        $query = $this->createQueryBuilder('v')
            ->leftJoin('v.owner', 'o')
            ->addSelect('o')
            ->where('o.id = :ownerId')
            ->setParameter('ownerId', $user->getId())
            ->getQuery();

        return $query->getResult();

    }

    public function deleteVehicle(Vehicle $vehicle){
        $entityManager = $this->getEntityManager();
       $entityManager ->remove($vehicle);
        $entityManager->flush();
    }

    public function nextService($user, $now){
        $query = $this->createQueryBuilder('v')
            ->leftJoin('v.owner', 'o')
            ->addSelect('o')
            ->where('v.owner = :ownerId')
            ->setParameter('ownerId', $user->getId())
            ->select('MIN(v.service) as min_service')
            ->andWhere('v.service >= :now')
            ->setParameter('now', $now)
            ->getQuery();
        
            // var_dump($query->getResult());
            // var_dump($now);
        return $query->getSingleScalarResult();
            
    }

    
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
