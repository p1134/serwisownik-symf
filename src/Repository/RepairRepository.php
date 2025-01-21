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

    public function findAllByVehicle($user): QueryBuilder{
        return $this->findAllQuery(withVehicle: true)
            ->where('v.owner = :ownerId')
            ->setParameter('ownerId', $user->getId());
    }

    public function findAllBySort($user, $sort, array $filters){
        
        $query = $this->findAllByVehicle($user);
        if(!empty($filters['status'])){
            $query->andWhere('r.status IN (:statuses)')
                ->setParameter('statuses', $filters['status']);
        }
        if(!empty($filters['month'])){
            $now = new \DateTime('now');
            if (in_array('currentMonth', $filters['month'])) {
                $start = clone($now)->modify('first day of this month')->setTime(0, 0);
                $end = clone($now)->modify('last day of this month')->setTime(23, 59);
                
                $query->andWhere('r.dateRepair BETWEEN :start AND :end ')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
            }
            elseif (in_array('previousMonth', $filters['month'])) {
                $start = clone($now)->modify('first day of previous month')->setTime(0, 0);
                $end = clone($now)->modify('last day of this month')->setTime(23, 59);
                
                $query->andWhere('r.dateRepair BETWEEN :start AND :end ')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
            }
        }
        if(!empty($filters['vehicle'])){
            $query->andWhere('r.vehicle IN (:vehicle)')
                ->setParameter('vehicle', $filters['vehicle']);
        }

        switch($sort){
            case 'alphabetASC':
                $query->orderBy('v.brand', 'ASC');
                break;
            case 'alphabetDESC':
                $query->orderBy('v.brand', 'DESC');
                break;
            case 'dateRepairASC':
                $query->orderBy('r.dateRepair', 'ASC');
                break;
            case 'dateRepairDESC':
                $query->orderBy('r.dateRepair', 'DESC');
                break;
            case 'priceASC':
                $query->orderBy('r.price', 'ASC');
                break;
            case 'priceDESC':
                $query->orderBy('r.price', 'DESC');
                break;
            default:
                $query->getQuery()->getResult();
                break;
        }
        return $query->getQuery()->getResult();
    }

    public function findAllByFilter($user, array $filters){
        $query = $this->createQueryBuilder('r');

        if(isset($filters['statusFilter'])){
            $query->andWhere('r.status = :status')
            ->setParameter('status', $filters['statusFilter']);
        }

        if(isset($filters['vehicleFilter'])){
            $query->leftJoin('r.vehicle','v')
            ->addselect( 'v')
            ->where('v.onwer = :ownerId')
            ->setParameter('ownerId', $user->getId());
        }
        if(isset($filters['dateRepairFilter'])){
            $query->andWhere('r.dateRepair BETWEEN :start AND :end')
            ->setParameter('start', $filters['dateRepair']['start'])
            ->setParameter('end', $filters['dateRepair']['end']);
        }

        return $query->getQuery()->getResult();
        
    }

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
