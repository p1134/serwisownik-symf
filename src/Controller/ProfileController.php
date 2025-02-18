<?php

namespace App\Controller;

use App\Entity\Raport;
use App\Repository\RaportRepository;
use App\Repository\RepairRepository;
use App\Repository\VehicleRepository;
use Symfony\Component\Asset\Packages;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(RaportRepository $raports): Response
    {
        $user = $this->getUser();
        $raport = $raports->getAllRaports($user);

        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'user' => $user->getUserIdentifier(),
            'data_sort' == null,
            'raports' => $raports->getAllRaports($user),
        ]);
    }

    #[Route('profile/raport', name: 'app_profile_raport')]
    public function newRaport(Packages $assets, EntityManagerInterface $entityManager, RaportRepository $raports, VehicleRepository $vehicles, RepairRepository $repairs): response
    {
        
        $user = $this->getUser();
        $date = new \DateTime('now');
    
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true);

            
            $logo = $this->getParameter('kernel.project_dir') . '/public/img/logo.png';
            $data = $repairs->getReport($user);
            $parts = [
                "mechanic" => "Mechaniczne",
                "body" => "Karoseryjne",
                "electric_electronic" => "Układ elektryczny i elektroniczny",
                "ac_ventilation" => "Klimatyzacja i wentylacja",
                "fluid" => "Płyny eksploatacyjne",
                "wheels" => "Opony i felgi",
                "interior" => "Wnętrze",
                "other" => "Inne"
            ];
            $pdf->AddPage();
            $pdf->addFont('dejavusans', '', '/public/fonts/DejaVuSans.ttf', true);
            // $pdf->Image($logo, $pdf->GetPageWidth()/2-20, $pdf->GetPageHeight()-50, 40);
            $pdf->SetTitle('Raport_Serwisownik-'.$date->format('Y-m-d'));
            $pdf->SetFont('dejavusans', 'I', 10);
            $pdf->Cell($pdf->GetPageWidth()-20, 0, $date->format('d-m-Y'));
            $pdf->ln();
            $pdf->SetFont('dejavusans', 'I', 14);
            $pdf->Cell($pdf->GetPageWidth()-20, 20, 'Raport ' . $raports->count() + 1 . '/' . $date->format('m') . '/' . $date->format('Y'), 0, 0, 'C');
            $pdf->Ln();
            
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->Cell($pdf->GetPageWidth()-20, 10, 'Historia napraw '.$date->format('m/Y'), 0, 0);
            $pdf->Ln();
            
            $pdf->SetFont('dejavusans', '', 7);
            $repairs = '
            <table cellpadding="4" align="center" style="border: 0.5, border-color: #b3ccff">
            <thead>
            <tr bgcolor="#ccddff" style="font-weight: bolder">
            <th width="30" style="border: 0.5, border-color: #b3ccff">ID</th>
            <th style="border: 0.5, border-color: #b3ccff">Pojazd</th>
            <th width="60" style="border: 0.5, border-color: #b3ccff">Rejestracja</th>
            <th width="130" style="border: 0.5, border-color: #b3ccff">Rodzaj naprawy</th>
            <th width="50" style="border: 0.5, border-color: #b3ccff">Cena</th>
            <th width="60" style="border: 0.5, border-color: #b3ccff">Data</th>
            <th width="140" style="border: 0.5, border-color: #b3ccff">Notatka</th>
            </tr>
            </thead>
            <tbody>';
            
            $repairsContent = null;
            $sumPrice = null;
            foreach ($data as $index => $vehicle) {
                $part = null;
                if(array_key_exists($vehicle['part'], $parts)){
                    $part = $parts[$vehicle['part']];
                };
                
                $sumPrice += $vehicle['price'];
                
                $repairsContent = '
                <tr style="border: 0.5, border-color: #b3ccff">
                <td width="30" style="border: 0.5, border-color: #b3ccff">'.$index + 1 .'</td>
                <td style="border: 0.5, border-color: #b3ccff">'.$vehicle['brand'].''.$vehicle['model'].'</td>
                <td width="60" style="border: 0.5, border-color: #b3ccff">'.$vehicle['numberPlate'].'</td>
                <td width="130" style="border: 0.5, border-color: #b3ccff">'.$part.'</td>
                <td width="50" style="border: 0.5, border-color: #b3ccff">'.$vehicle['price'].' zł</td>
                <td width="60" style="border: 0.5, border-color: #b3ccff">'.$vehicle['dateRepair']->format('d-m-Y').'</td>
                <td width="140" style="border: 0.5, border-color: #b3ccff">'.$vehicle['description'].'</td>
                </tr>
                ';
                $repairs .= $repairsContent;
            };
            
            $repairs .= '
            <tr bgcolor="#ccddff" style="font-weight: bolder">
            <td colspan="2" style="border: 0.5, border-color: #b3ccff">Razem:</td>
            <td style="border: 0.5, border-color: #b3ccff"></td>
            <td style="border: 0.5, border-color: #b3ccff"></td>
            <td style="border: 0.5, border-color: #b3ccff">'.$sumPrice.' zł</td>
            <td style="border: 0.5, border-color: #b3ccff"></td>
            <td style="border: 0.5, border-color: #b3ccff"></td>
            </tr>
            </tbody>
            </table>
            ';
            $pdf->writeHTML($repairs);
            $pdf->Cell($pdf->getPageWidth(), 0, $pdf->Image($logo, $pdf->getPageWidth()/2 - 10, $pdf->getPageHeight()-38, 20), 0, 0, 'C');
            

            $pdfFile = $pdf->Output('Raport_Serwisownik-'.$date->format('Y-m-d').'_['.$date->format('H:i:s').']', 'S');
    
            $raport = new Raport();
            $raport->setDateCreate($date);
            $raport->setUser($user);
            $raport->setPdf($pdfFile);
            $raport->setFilename('Raport_Serwisownik-'.$date->format('Y-m-d').'_['.$date->format('H:i:s').']');
            
            if($raport){

                $entityManager->persist($raport);
                $entityManager->flush();
    
                $this->addFlash('success', 'Raport został wygenerowany');
    
                return $this->redirectToRoute('app_profile');
            }
    
            return $this->render('profile/index.html.twig', [
                'controller_name' => 'ProfileController',
                'user' => $user->getUserIdentifier(),
                'data_sort' == null,
            ]);
    }

}
