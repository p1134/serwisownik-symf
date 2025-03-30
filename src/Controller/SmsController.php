<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Entity\User;
use App\Form\SmsType;
use App\Service\SmsService;
use App\Repository\UserRepository;
use App\Repository\RaportRepository;
use App\Repository\RepairRepository;
use PhpParser\Node\Expr\Instanceof_;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SmsController extends AbstractController
{
    
    private $SmsService;
    
    public function __construct(SmsService $smsService)
    {
        $this->SmsService = $smsService;
    }
    #[Route('/profile/{user}/send-verification-code', name: 'app_sms')]
    public function sendVerificationCode(Request $request, EntityManagerInterface $entityManager, RaportRepository $raports): Response
    {
        $user = $this->getUser();
        
        if(!$user instanceof User){
            throw new \LogicException('Użytkownik nie jest instancji User.');
        }

        $phoneNumber = $user->getPhoneNumber();
        $sms = $user->isSms();
        
        if($phoneNumber === 0 || $phoneNumber === null){
            $this->addFlash('error', 'Brak przypisanego numeru telefonu');
        }

        elseif($sms === false){
            $this->addFlash('error', 'Brak pozwolenia na otrzymywanie powiadomień');
        }

        else{
            $verificationCode = rand(100000, 999999);
            $message = "Kod weryfikacyjny: ".$verificationCode;
            $smsStatus = $this->SmsService->sendSms($phoneNumber, $message);
            
            $session = $request->getSession();
            if(!$session->has('verification_code')){
                $session->set('verification_code', $verificationCode);
                $session->save();
            }
            
            if(strpos($smsStatus, 'Error') === false){
                $this->addFlash('success', 'Kod weryfikacyjny został wysłany');
                // return $this->redirectToRoute('app_profile');
            }
            else {
                $this->addFlash('error', 'Wystąpił problem z wysłaniem kodu: ' . $smsStatus);
            }
            
            $form = $this->createForm(SmsType::class);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $code = $form->get('code')->getData();

                if($code == $session->get('verification_code')){
                    $user->setSms(true);
            }
            else{
                return $this->redirectToRoute('app_edit_profile');
            }
        }
        $entityManager->persist($user);
        $entityManager->flush();
    }
        
        
        return $this->render('profile/index.html.twig', [
            'verificationCode' => $verificationCode,
            'form_type' => 'verification',
            'form' => $form->createView(),
            'user' => $user->getUserIdentifier(),
            'raports' => $raports->getAllRaports($user),
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
            'phoneNumber' => $user->getPhoneNumber(),
            // 'smsStatus' => $smsStatus,
            'raport' => 'raport' ?? null,
        ]);
    }
    
    #[Route('/profile/{user}/send-notification', name: 'app_notification')]
    public function sendNotification(Request $request, RaportRepository $raports, VehicleRepository $vehicles)
    {
        $user = $this->getUser();
        $now = new DateTime('now');


    }
}
