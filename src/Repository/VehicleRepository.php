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
        bool $withInsurance = false,
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
        if($withInsurance){
                $query->addSelect('v.insurance');
        }

        return $query;
    }

    public function findAllBySort($user, $sort){
        $query = $this->findAllByOwner($user);
        switch($sort){
            case 'alphabetASC':
                $query->orderBy('v.brand', 'ASC');
                break;
            case 'alphabetDESC':
                $query->orderBy('v.brand', 'DESC');
                break;
            case 'serviceASC':
                $query->orderBy('v.service', 'ASC');
                break;
            case 'serviceDESC':
                $query->orderBy('v.service', 'DESC');
                break;
            case 'insuranceASC':
                $query->orderBy('v.insurance', 'ASC');
                break;
            case 'insuranceDESC':
                $query->orderBy('v.insurance', 'DESC');
                break;
            default:
                $query->getQuery()->getResult();
                break;
        }
        return $query->getQuery()->getResult();
    }

    public function findAllByOwner($user):QueryBuilder{
        return $this->findAllQuery(withOwner: true)
            ->where('v.owner = :ownerId')
            ->setParameter('ownerId', $user->getId());

    }

    public function nextService($user, $now){
            $query = $this->findAllQuery(withOwner: true, withService: true)
                ->select('MIN(v.service) AS min_service')
                ->where('v.owner = :ownerId')
                ->setParameter('ownerId', $user->getId())
                ->andWhere('v.service >= :now')
                ->setParameter('now', $now)
                ->getQuery()
                ->getSingleScalarResult();
            
            return $query ?: null;
    }
    public function nextInsurance($user, $now){
            $query = $this->findAllQuery(withOwner: true, withService: true)
                ->select('MIN(v.insurance) AS min_insurance')
                ->where('v.owner = :ownerId')
                ->setParameter('ownerId', $user->getId())
                ->andWhere('v.insurance >= :now')
                ->setParameter('now', $now)
                ->getQuery()
                ->getSingleScalarResult();
            
            return $query ?: null;
    }


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

    public function smsServiceNotification(){

        $now = new DateTime('now');
        $smsDay = clone($now);
        $smsDay->modify('+7 days')->setTime(0,0,0);
        return $this->findAllQuery(
        )
        ->Where('v.service = :smsDay')
        ->setParameter('smsDay', $smsDay)
        ->getQuery()
        ->getResult();
    }

    public function smsInsuranceNotification(){

        $now = new DateTime('now');
        $smsDay = clone($now);
        $smsDay->modify('+7 days')->setTime(0,0,0);
        return $this->findAllQuery(
        )
        ->Where('v.service = :smsDay')
        ->setParameter('smsDay', $smsDay)
        ->getQuery()
        ->getResult();
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
