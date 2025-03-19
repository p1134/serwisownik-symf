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
        bool $withStatus = false,
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
        if($withStatus){
            $query->addSelect('r.status');
        }
        if($withDescription){
            $query->addSelect('r.description');
        }

        return $query;
    }

    
    public function findAllByVehicle($user): QueryBuilder{
        return $this->findAllQuery(withVehicle: true)
        ->where('v.owner = :ownerId')
            ->setParameter('ownerId', $user->getId())
            ->orderBy('r.dateRepair', 'DESC');
        }
        
        public function getReport($user): array{

            $now = new \DateTime('now');
            $currentStart = clone($now)->modify('first day of this month')->setTime(0, 0);
            $currentEnd = clone($now)->modify('last day of this month')->setTime(23, 59);
            
            $query = $this->findAllQuery(withVehicle: true, withPart: true, withDateRepair: true, withPrice: true, withDescription:true )
            ->addSelect('v.brand')
            ->addSelect('v.model')
            ->addSelect('v.numberPlate')
            ->where('r.dateRepair BETWEEN :start AND :end')
            ->setParameter('start', $currentStart)
            ->setParameter('end', $currentEnd)
            ->andwhere('v.owner = :ownerId')
            ->setParameter('ownerId', $user->getId())
            ->getQuery();
            return $query->getResult();
            // dd($query);

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
        if(!empty($filters['part'])){
            $query->andWhere('r.part = :part')
            ->setParameter('part', $filters['part']);
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

    // public function findAllByFilter($user, array $filters){
    //     $query = $this->createQueryBuilder('r');

    //     if(isset($filters['statusFilter'])){
    //         $query->andWhere('r.status = :status')
    //         ->setParameter('status', $filters['statusFilter']);
    //     }

    //     if(isset($filters['vehicleFilter'])){
    //         $query->leftJoin('r.vehicle','v')
    //         ->addselect( 'v')
    //         ->where('v.onwer = :ownerId')
    //         ->setParameter('ownerId', $user->getId());
    //     }
    //     if(isset($filters['dateRepairFilter'])){
    //         $query->andWhere('r.dateRepair BETWEEN :start AND :end')
    //         ->setParameter('start', $filters['dateRepair']['start'])
    //         ->setParameter('end', $filters['dateRepair']['end']);
    //     }
    //     if(isset($filters['partFilter'])){
    //         $query->andWhere('r.part = :part')
    //         ->setParameter('part', $filters['partFilter']);
    //     }

    //     return $query->getQuery()->getResult();
        
    // }

    public function repairChart($user){
        $now = new \DateTime('now');
        $year = $now->format('Y');

        $query = $this->findAllQuery(withVehicle: true, withPrice: true, withDateRepair: true)
            ->where('v.owner = :ownerId')
            ->setParameter('ownerId', $user->getId())
            ->select('MONTH(r.dateRepair) as Month', 'SUM(r.price) as Sum')
            ->andWhere("DATE_FORMAT(r.dateRepair, '%Y') = :year")
            ->setParameter('year', $year)
            ->groupBy('Month')
            ->getQuery();
        return $query->getArrayResult();
    }

    public function currentMonth($user){
        $now = new \DateTime('now');
        $currentStart = clone($now)->modify('first day of this month')->setTime(0, 0);
        $currentEnd = clone($now)->modify('last day of this month')->setTime(23, 59);
        
        $currentMonth = $this->findAllQuery(withPrice: true, withDateRepair: true, withVehicle:true)
        ->where('v.owner = :ownerId')
        ->setParameter('ownerId', $user->getId())
        ->andWhere('r.dateRepair BETWEEN :start AND :end')
        ->setParameter('start', $currentStart)
        ->setParameter('end', $currentEnd);
        

        return $currentMonth;
    }

    public function previousMonth($user){
        $now = new \DateTime('now');
        $previousStart = clone($now)->modify('first day of previous month')->setTime(0, 0);
        $previousEnd = clone($now)->modify('last day of this month')->setTime(23, 59);

        $previousMonth = $this->findAllQuery(withPrice: true, withDateRepair: true, withVehicle: true)
        ->where ('v.owner = :ownerId')
        ->setParameter('ownerId', $user->getId())
        // ->addSelect('r.dateRepair as Month')
        ->andWhere('r.dateRepair BETWEEN :start AND :end')
        ->setParameter('start', $previousStart)
        ->setParameter('end', $previousEnd);
        // ->groupBy('Month');

        return $previousMonth;
    }

    public function CVPRepairs($user){
        // $currentMonth = $this->currentMonth($user);
            $currentMonth = $this->currentMonth($user)
                ->select('COUNT(r.price) as Current')
                ->addSelect("DATE_FORMAT(r.dateRepair, '%Y-%m') as Date") 
                ->groupBy('Date')
                ->getQuery()
                ->getResult();

            $previousMonth = $this->previousMonth($user)
                ->select('COUNT(r.price) as Previous')
                ->addSelect("DATE_FORMAT(r.dateRepair, '%Y-%m') as Date") 
                ->groupBy('Date')
                ->getQuery()
                ->getResult();

        

        $currentMonth = !empty($currentMonth) && isset($currentMonth[0])  ? intval($currentMonth[0]['Current']) : 0;
        $previousMonth = !empty($previousMonth) && isset($previousMonth[0]) ? intval($previousMonth[0]['Previous']) : 0;

        return $cvp = ['Current' => $currentMonth, 'Previous' => $previousMonth];
    }

    public function CVPCost($user){
        $currentMonth = $this->currentMonth($user)
            ->select('SUM(r.price) as Current')
            ->addSelect("DATE_FORMAT(r.dateRepair, '%Y-%m') as Date") 
            ->groupBy('Date')
            ->getQuery()
            ->getResult();



        $previousMonth = $this->previousMonth($user)
            ->select('SUM(r.price) as Previous')
            ->addSelect("DATE_FORMAT(r.dateRepair, '%Y-%m') as Date") 
            ->groupBy('Date')
            ->getQuery()
            ->getResult();



        $currentMonth = !empty($currentMonth) && isset($currentMonth[0])  ? intval($currentMonth[0]['Current']) : 0;
        $previousMonth = !empty($currentMonth) && isset($previousMonth[0]) ? intval($previousMonth[0]['Previous']) : 0;

        return $cvp = ['Current' => $currentMonth, 'Previous' => $previousMonth];
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

    public function getRepairsByVehicle($user){
        $query = $this->findAllQuery(withVehicle: true, withPrice: true)
            ->leftJoin('r.vehicle', 'vehicle')
            ->select('SUM(r.price) AS Sum')
            ->addSelect('CONCAT(vehicle.brand, \' \', vehicle.model, \' | \', vehicle.numberPlate) AS Vehicle')
            ->where('v.owner = :ownerId')
            ->setParameter('ownerId', $user->getId())
            ->groupBy('Vehicle')
            ->getQuery()
            ->getResult();
        return $query;
    }

    public function getSumByPart($user){
        $query = $this-> findAllQuery(withPrice: true, withPart: true, withVehicle: true)
            ->select('SUM(r.price) as Sum')
            ->addSelect('r.part AS Part')
            ->where('r.user = :user')
            ->setParameter('user', $user->getId())
            ->groupBy('Part')
            ->getQuery()
            ->getResult();
        return $query;
    }
    public function getCountByPart($user){
        $query = $this->findAllQuery(withPart: true, withVehicle: true, withId: true)

            ->select('COUNT(r.part) AS Count')
            ->addselect('r.part AS Part')
            ->where('r.user = :user')
            ->setParameter('user', $user->getId())
            ->groupBy('r.part')
            ->getQuery()
            ->getResult();
            // dd($query);
            return $query;
            

    ;}

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
