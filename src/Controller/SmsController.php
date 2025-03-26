<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Form\SmsType;
use App\Repository\RaportRepository;
use App\Service\SmsService;
use App\Repository\UserRepository;
use PhpParser\Node\Expr\Instanceof_;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormTypeInterface;

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
        $verificationCode = rand(100000, 999999);
        
        if($phoneNumber === 0 || $phoneNumber === null){
            $this->addFlash('error', 'Brak przypisanego numeru telefonu');
        }

        elseif($sms === false){
            $this->addFlash('error', 'Brak pozwolenia na otrzymywanie powiadomień');
        }

        else{
            $message = "Kod weryfikacyjny: ".$verificationCode;
            $smsStatus = $this->SmsService->sendSms($phoneNumber, $message);
            
            $session = $request->getSession();
            $session->set('verification_code', $verificationCode);
            
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
                // dd($code);
                if($code === $session->get('verification_code')){
                    $user->setSms(true);
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
            'smsStatus' => $smsStatus,
            'raport' => 'raport' ?? null,
        ]);
    }
}
