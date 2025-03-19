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

        //Chart sum by month
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
                        // 'suggestedMax' => 200,
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

        if($cvp['Current'] >= $cvp['Previous'] && $cvp['Previous'] != null || $cvp['Previous'] != 0){
            $growthRepairs = round(abs((($cvp['Current']-$cvp['Previous'])/$cvp['Previous'])*100), 1);
        }
        elseif($cvp['Current'] > $cvp['Previous'] && $cvp['Previous'] = null || $cvp['Previous'] == 0){
            $growthRepairs = round(abs((1)*100), 1);
        }
        elseif($cvp['Current'] < $cvp['Previous'] && $cvp['Previous'] != null || $cvp['Previous'] != 0){
            $decreaseRepairs = round(abs((($cvp['Current']-$cvp['Previous'])/$cvp['Previous'])*100),1);
        }
        elseif($cvp['Current'] == null && $cvp['Previous'] == null){
            $nothing = 0;
        }

        else{
            $decreaseRepairs = round(abs((($cvp['Current'] - $cvp['Previous']) / ($cvp['Current'] != 0 ? $cvp['Current'] : 1)) * 100), 1);
        }


        $growthCost = null;
        $decreaseCost = null;
        $nothingCost = null;

        $cvpCost = $repairs->CVPCost($user);

        if($cvpCost['Current'] >= $cvpCost['Previous'] && $cvpCost['Previous'] != null || $cvpCost['Previous'] != 0){
            $growthCost = round(abs((($cvpCost['Current']-$cvpCost['Previous'])/$cvpCost['Previous'])*100), 1);
        }
        elseif($cvpCost['Current'] < $cvpCost['Previous'] && $cvpCost['Previous'] != null || $cvpCost['Previous'] != 0){
            $decreaseCost = round(abs((($cvpCost['Current']-$cvpCost['Previous'])/$cvpCost['Previous'])*100), 1);
        }
        elseif($cvpCost['Current'] == 0 && $cvpCost['Previous'] == 0){
            $nothingCost = 0;
        }
        elseif($cvpCost['Current'] > $cvpCost['Previous'] && $cvpCost['Previous'] = null || $cvpCost['Previous'] == 0){
            $growthCost = round(abs((1)*100), 1);
        }
        else{
            $decreaseCost = round(abs((($cvpCost['Current']-$cvpCost['Previous'])/$cvpCost['Current'])*100), 1);
        }


        //
        //Calendar events
        $nextServiceCount = date_diff( date_create($vehicles->nextService($user, $now)), date_create($now), );
        $nextServiceCountM = $nextServiceCount->m;
        $nextServiceCountD = $nextServiceCount->d;
        
        $nextInsuranceCount = date_diff( date_create($vehicles->nextInsurance($user, $now)), date_create($now), );
        $nextInsuranceCountM = $nextInsuranceCount->m;
        $nextInsuranceCountD = $nextInsuranceCount->d;
        //

        //Chart sum by vehicle
        $chartSBV = $chartBuilder->createChart(CHART::TYPE_DOUGHNUT);
        $repairsSBV = $repairs->getRepairsByVehicle($user);

        $repairsSBVArray = [];
        foreach($repairsSBV as $item){
            $repairsSBVArray[$item['Vehicle']] = $item['Sum'];
        }

        $SBVDataVehicles = array_keys($repairsSBVArray);
        $SBVDataSum = array_map('intval', array_values($repairsSBVArray));

        function getRandomColor() {
            $randomColor = sprintf('#%06X', mt_rand(390, 499)); // Losuje liczbę z zakresu 0 do 16777215 i konwertuje na HEX
            return $randomColor;
        }
        $colorsArray = [
            '#FF6F61', '#4682B4', '#D3A125', '#6B8E23', '#9B59B6', '#FF6347',
            '#2E8B57', '#FFD700', '#9ACD32', '#E9967A', '#6495ED', '#A52A2A',
            '#20B2AA', '#D2691E', '#FF4500', '#DC143C', '#32CD32', '#8A2BE2',
            '#8B4513', '#FF1493'
        ];


        $chartSBV->setData([
            'labels' => $SBVDataVehicles,
            'datasets' => [
                [
                    'backgroundColor' => $colorsArray,
                    'borderColor' => 'transparent',
                    'data' => $SBVDataSum,
                ],
            ],
        ]);

        $chartSBV->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    // 'maxWidth' => 100,
                    'position' => 'left',
                    'align' => 'end',
                    'labels' => [
                        'boxWidth' => 15,
                        'boxHeight' => 15,
                        'pointStyle' => 'rectRounded', 
                        'usePointStyle' => true,
                        'color' => 'white',
                        'font' => [
                            'size' => 16,
                            'weight' => 200,
                        ],
                        ],
                        'padding' => [
                            'top' => 20,
                        ]
                    ],
                    'title' => [
                        'align' => 'start',
                        'display' => true,
                        'text' => 'Wydatki wg pojazdów',
                        'color' => 'white',
                        'font' => [
                            'size' => 20,
                            'weight' => 200,
                        ],
                        'padding' => [
                            // 'bottom' => 15,
                        ]
                    ],
                    ],
                    'layout' => [
                        'align' => 'center',
                        'padding' => [
                            'left' => 20,
                            // 'bottom' => 10,
                            
                        ]
                    ]
        ]);

        //

        //Sum by part
        $chartCBP = $chartBuilder->createChart(Chart::TYPE_BAR);
        // $repairsSBP = $repairs->getSumByPart($user);
        $repairsCBP = $repairs->getCountByPart($user);
        $repairsCBPArray = [];
        foreach ($repairsCBP as $item) {
            switch($item['Part']){
                
                case 'mechanic':
                    $repairsCBPArray['Mechaniczne'] = $item['Count'];
                break;
                    
                case 'body':
                    $repairsCBPArray['Karoseryjne'] = $item['Count'];
                break;

                case 'electric_electronic':
                    $repairsCBPArray['Układ elektryczny i elektroniczny'] = $item['Count'];
                break;

                case 'ac_ventilation':
                    $repairsCBPArray['Klimatyzacja i wentylacja'] = $item['Count'];
                break;

                case 'fluid':
                    $repairsCBPArray['Płyny eksploatacyjne'] = $item['Count'];
                break;

                case 'wheels':
                    $repairsCBPArray['Opony i felgi'] = $item['Count'];
                break;

                case 'interior':
                    $repairsCBPArray['Wnętrze'] = $item['Count'];
                break;

                case 'other':
                    $repairsCBPArray['Inne'] = $item['Count'];
                break;

                default:
                    $repairsCBPArray[$item['Part']] = $item['Count'];
                break;
            }
        }
        $LabelsCBP = array_keys($repairsCBPArray);
        $DataCBP = array_values($repairsCBPArray);
        // dd($repairsCBPArray, $LabelsCBP, $DataCBP);

        $chartCBP->setData([
            'labels' =>  $LabelsCBP,
            'color' => 'rgb(95, 131, 224)',
            'datasets' => [
                [
                'borderRadius' => 10,
                'data' => $DataCBP,
                'label' => 'Naprawy według rodzaju',
                'backgroundColor' => '#416DAE',
                'borderWidth' => '2',
                'color' => 'white',
                ]],
            ]);

                $chartCBP->setOptions([
                'responsive' => true,
                'maintainAspectRatio' => false, 
                'scales' => [
                    'y' => [
                        'suggestedMin' => 0,
                        // 'suggestedMax' => 10,
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
                            'display' => false,
                            ],
                            'offset' => true,
                        ],
                ],
                'plugins' => [
                    'legend' => [
                        'labels' => [
                            'boxWidth' => 0,
                            'color' => 'white',
                            'font' => [
                                'size' => 20,
                                'weight' => 300,
                            ]
                        ]
                        ]
                ]
            ]);

        //

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
            'chartSBV' => $chartSBV,
            'getSumByPart' => $repairs->getSumByPart($user),
            'chartCBP' => $chartCBP,
        ]);

    }
}
