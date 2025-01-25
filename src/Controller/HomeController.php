<?php

namespace App\Controller;

use DateTime;
use App\Entity\Vehicle;
use App\Form\VehicleType;
use Symfony\UX\Chartjs\Model\Chart;
use App\Repository\RepairRepository;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    #[IsGranted('ROLE_USER')]
    public function index(VehicleRepository $vehicles, RepairRepository $repairs, ChartBuilderInterface $chartBuilder): Response
    {
        $user = $this->getUser();
        $date = new DateTime('now');
        $now = $date->format('Y-m-d');
        
        $numberOfRepairs = 0;
        $lastVehicle = $vehicles->lastAddedVehicle($user);
        $oldestVehicle = $vehicles->oldestVehicle($user);

        //Chart
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $repairsSum = $repairs->repairChart($user);

        $repairsSumArray = [];
        foreach($repairsSum as $item){
            $repairsSumArray[$item['Month']] = $item['Sum'];
        }

        $repairsSumChart = array_fill(1,12, 0);
        
        foreach ($repairsSumArray as $month => $sum) {
            if(isset($repairsSumChart[$month])){
                $repairsSumChart[$month] = $sum;
            }
        }
        $SumChartData = array_map('intval',array_values($repairsSumChart));

        $chart->setData([
            'labels' => ['Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'],
            'color' => 'rgb(95, 131, 224)',
            'datasets' => [
                [
                'data' => $SumChartData,
                'label' => 'Wydatki '.$date->format('Y'),
                'borderColor' => 'rgb(95, 131, 224)',
                'borderWidth' => '2',
                'color' => 'white',
                ]
            ]
            ]);
            $chart->setOptions([
                'responsive' => true,
                'maintainAspectRatio' => false, 
                'scales' => [
                    'y' => [
                        'suggestedMin' => 0,
                        'suggestedMax' => 200,
                        'ticks' =>[
                            'color' => 'white',
                            'font' => [
                                'size' => 16,
                                'weight' => 200,
                            ],
                            // 'padding' => 3,
                        ],
                        'grid' => [
                            'color' => '#E2E2E2',
                            'lineWidth' => 0.1,
                        ],
                        'offset' => true,
                    ],
                    'x' => [
                        'grid' => [
                            'display' => false
                        ],
                        'ticks' =>[
                            'color' => 'white',
                            'font' => [
                                'size' => 16,
                                'weight' => 200,
                            ],
                            ],
                            'offset' => true,
                        ],
                ],
                'plugins' => [
                    'legend' => [
                        'labels' => [
                            'color' => 'white',
                            'font' => [
                                'size' => 24,
                                'weight' => 300,
                            ]
                        ]
                    ]
                ]
            ]);
        //
        //Growth or decrease
        $growthRepairs = null;
        $decreaseRepairs = null;
        $nothing = null;
        $cvp = $repairs->CVPRepairs($user);
        if($cvp['Current'] >= $cvp['Previous'] && $cvp['Previous'] != null){
            $growthRepairs = abs((($cvp['Current']-$cvp['Previous'])/$cvp['Previous'])*100);
        }
        elseif($cvp['Current'] < $cvp['Previous'] && $cvp['Previous'] != null){
            $decreaseRepairs = abs((($cvp['Current']-$cvp['Previous'])/$cvp['Previous'])*100);
        }
        elseif($cvp['Current'] == null && $cvp['Previous'] == null){
            $nothing = 0;
        }

        else{
            $decreaseRepairs = abs((($cvp['Current']-$cvp['Previous'])/$cvp['Current'] ? 0 : $cvp['Previous'])*100);
        }


        $growthCost = null;
        $decreaseCost = null;
        $nothingCost = null;

        $cvpCost = $repairs->CVPCost($user);
        if($cvpCost['Current'] >= $cvpCost['Previous'] && $cvpCost['Previous'] != null){
            $growthCost = abs((($cvpCost['Current']-$cvpCost['Previous'])/$cvpCost['Previous'])*100);
        }
        elseif($cvpCost['Current'] < $cvpCost['Previous'] && $cvpCost['Previous'] != null){
            $decreaseCost = abs((($cvpCost['Current']-$cvpCost['Previous'])/$cvpCost['Previous'])*100);
        }
        elseif($cvpCost['Current'] == 0 && $cvpCost['Previous'] == 0){
            $nothingCost = 0;
        }
        else{
            $decreaseCost = abs((($cvpCost['Current']-$cvpCost['Previous'])/$cvpCost['Current'])*100);
        }


        //
        
        $nextServiceCount = date_diff( date_create($vehicles->nextService($user, $now)), date_create($now), );
        $nextServiceCountM = $nextServiceCount->m;
        $nextServiceCountD = $nextServiceCount->d;
        
        $nextInsuranceCount = date_diff( date_create($vehicles->nextInsurance($user, $now)), date_create($now), );
        $nextInsuranceCountM = $nextInsuranceCount->m;
        $nextInsuranceCountD = $nextInsuranceCount->d;


        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'user' => $user->getUserIdentifier(),
            'vehicles' => $vehicles->findAllByOwner($user)->getQuery()->getResult(),
            'totalRepairs' => $repairs->getTotalRepairCost($user),
            'nextService' => $vehicles->nextService($user, $now),
            'nextServiceCountM' => $nextServiceCountM,
            'nextServiceCountD' => $nextServiceCountD,
            'nextInsurance' => $vehicles->nextInsurance($user, $now),
            'nextInsuranceCountM' => $nextInsuranceCountM,
            'nextInsuranceCountD' => $nextInsuranceCountD,
            'lastVehicle' => $lastVehicle[0] ?? null,
            'oldestVehicle' => $oldestVehicle[0] ?? null,
            'repairs' => $repairs->findAllByVehicle($user)->getQuery()->getResult(),
            'numberOfRepairs' => $numberOfRepairs,
            'mostRepairs' => $repairs->mostRepairs($user),
            'newestRepair' => $repairs->newestRepair($user),
            'chart' => $chart,
            'growthRepairs' => $growthRepairs,
            'decreaseRepairs' => $decreaseRepairs,
            'growthCost' => $growthCost,
            'decreaseCost' => $decreaseCost,
            'nothing' => $nothing,
            'nothingCost' => $nothingCost,
        ]);

    }
}
