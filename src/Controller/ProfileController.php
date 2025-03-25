<?php

namespace App\Controller;

use TCPDF;
use App\Entity\User;
use App\Form\SmsType;
use App\Entity\Raport;
use App\Form\ProfileType;
use App\Form\changeEmailType;
use App\Security\EmailVerifier;
use App\Form\ChangePasswordType;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Repository\RaportRepository;
use App\Repository\RepairRepository;
use App\Repository\VehicleRepository;
use Symfony\Component\Asset\Packages;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(RaportRepository $raports, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if(!$user instanceof User ){
            throw new \LogicException('Użytkownik nie jest instancji User.');
        }


        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'user' => $user->getUserIdentifier(),
            'data_sort' == null,
            'raports' => $raports->getAllRaports($user),
            'form_type' => '',
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
            'phoneNumber' => $user->getPhoneNumber(),
        ]);
    }

    #[Route('profile/raport', name: 'app_profile_raport')]
    public function newRaport(EntityManagerInterface $entityManager, RaportRepository $raports, RepairRepository $repairs): response
    {
        
        $user = $this->getUser();
        if(!$user instanceof User){
            throw new \LogicException('Użytkownik nie jest instancji User.');
        }
        $date = new \DateTime('now');
    
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', true);

            
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
                'data_sort' => null,
            ]);
        }
           
                
            

    #[Route('profile/raport/download/{id}', name: 'app_profile_raport_download')]
    public function downloadRaport(int $id, EntityManagerInterface $entityManager){
        $rapport = $entityManager->getRepository(Raport::class)->find($id);

        if(!$rapport || !$rapport->getPdf()){
            throw $this->createNotFoundException('Plik PDF nie został znaleziony');
        }

        $pdf = $rapport->getPdf();
        $pdf = stream_get_contents($pdf);
        if (!is_string($pdf)) {
            throw $this->createNotFoundException('Zawartość pliku PDF jest nieprawidłowa.');
        }
        
        $response = new Response($pdf);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$rapport->getFilename());

        return $response;
    

}

#[Route('/profile/{user}/change-email', name: 'app_profile_change_email')]
public function editProfile(Request $request, EntityManagerInterface $entityManager, RaportRepository $raports, UserPasswordHasherInterface $userPass){

    $user = $this->getUser();

    if(!$user){
        $this->addFlash('error', 'Uzytkownik niezalogowany');
        return $this->redirectToRoute('app_login');
    }
    if (!$user instanceof User) {
        throw new \LogicException('Oczekiwana instancja klasy User');
    }
    
    $form = $this->createForm(changeEmailType::class);
    $form->handleRequest($request);

    if($form->isSubmitted() && $form->isValid()){
        $data = $form->getData();
        
        if($user->getUserIdentifier() !== $data['email']){
            $this->addFlash('error', 'Niepoprawny adres email');
            return $this->redirectToRoute('app_profile_change_email', [
                'user' => $user->getUserIdentifier(),
            ]);
        }

        if($userPass->isPasswordValid($user, $data['password'])){
            $user->setEmail($data['newEmail']);
        }
        
        else{
            $this->addFlash('error', 'Niepoprawne hasło');
            return $this->redirectToRoute('app_profile_change_email', [
                'user' => $user->getUserIdentifier(),
            ]);
        }
        
        $entityManager->persist($user);
        $entityManager->flush();
        
        $this->addFlash('success', 'Adres email został zmieniony');
        return $this->redirectToRoute('app_login');
    }


    // $currentUser = $this->getUser()->getUserIdentifier();
    // $user = $entityManager->getRepository(User::class)->find($currentUser);

    // $form = $this->createForm(changeEmailType::class);
    // $form->handleRequest($request);

    // if($form->isSubmitted() && $form->isValid()){

    //     $data = $form->getData();

    //     if($user->getUserIdentifier() !== $data['email']){
    //         $this->addFlash('error', 'Niepoprawny adres email');
    //         return $this->redirectToRoute('app_profile_change_email');
    //     }
    //     elseif($data['password' !== $passwordHasher->isPasswordValid($user, $data['password'])]){
    //         $this->addFlash('error', 'Niepoprawne hasło');
    //         return $this->redirectToRoute('app_profile_change_email');
    //     }

    //     $user->setEmail($data['email']);
        
    //     $this->addFlash('success', 'Adres email został zaktualizowany');
    // }

    return $this->render('profile/index.html.twig', [
        'form_type' => 'edit_email',
        'form' => $form->createView(),
        'user' => $user->getUserIdentifier(),
        'raports' => $raports->getAllRaports($user),
    ]);
}


public function __construct(private EmailVerifier $emailVerifier)
{
}

#[Route('/send', 'app_send_reset_link')]
    public function sendResetLink(MailerInterface $mailer){

        $user = $this->getUser();
        $token = bin2hex(random_bytes(16));
        $link = $this->generateUrl('app_form_change_password', ['token' => $token, 'user' => $user->getUserIdentifier()], UrlGeneratorInterface::ABSOLUTE_URL);

        if(!$user){
            $this->addFlash('error', 'Uzytkownik niezalogowany');
            return $this->redirectToRoute('app_login');
        }
        if (!$user instanceof User) {
            throw new \LogicException('Oczekiwana instancja klasy User');
        }

        $email = (new Email())
            ->from('weryfikacja@serwisownik.com.pl')
            ->to((string)$user->getEmail())
            ->subject(('Resetowanie hasła'))
            ->html("<p>Aby zresetować hasło, kliknij <a href=\"$link\">tutaj</a></p>");

        $mailer->send($email);
        $this->addFlash('success', 'Wiadomość potwierdzająca została wysłana na adres email');
        
        return $this->redirectToRoute('app_profile');
        

    }

    #[Route('/password/{user}/change', 'app_form_change_password')]
    public function resetPassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPass){
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            
            if(!$user instanceof User){
                throw new \LogicException('Oczekiwana instancja klasy User');
            }
            if($userPass->isPasswordValid($user, $data['password'])){
                $user->setPassword($userPass->hashPassword($user, $data['plainPassword']));
            }
            $entityManager->persist($user);
            $entityManager->flush();
            
            $this->addFlash('success', 'Hasło zostało zmienione');
            return $this->redirectToRoute('app_profile');

        }
        return $this->render('profile/_change_password.html.twig', [
            'form' => $form->createView(),
            'user' => $user->getUserIdentifier()
        ]);
    }

    #[Route('profile/{user}/edit', 'app_edit_profile')]
        public function editUserProfile(Request $request, EntityManagerInterface $entityManager, RaportRepository $raports){
            $user = $this->getUser();

            $form = $this->createForm(ProfileType::class, $user);
            $form->handleRequest($request);
            
            if(!$user instanceof User){
                throw new \LogicException('Oczekiwana instancja klasy User');
            }

            if($form->isSubmitted() && $form->isValid()){
                $data = $form->getData();
                // dd($data['name']);
                if($data->getName() != null){
                    $user->setName($data->getName());
                }
                if($data->getSurname() != null){
                    $user->setSurname($data->getSurname());
                }
                if($data->getPhoneNumber() != null){
                    $user->setPhoneNumber($data->getPhoneNumber());
                }
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Twój profil został zaktualizowany');
                return $this->redirectToRoute('app_profile');

            }

            return $this->render('profile/index.html.twig', [
                'form_type' => 'edit_profile',
                'form' => $form->createView(),
                'user' => $user->getUserIdentifier(),
                'raports' => $raports->getAllRaports($user),
                'name' => $user->getName(),
                'surname' => $user->getSurname(),
                'phoneNumber' => $user->getPhoneNumber(),
            ]);
        }
    
}
