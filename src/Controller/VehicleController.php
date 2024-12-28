<?php

namespace App\Controller;

use App\Entity\Vehicle;
use App\Form\VehicleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VehicleController extends AbstractController
{
    #[Route('/vehicle', name: 'app_vehicle')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VehicleType::class, new Vehicle());

        $form->handleRequest($request);

        if($form ->isSubmitted() && $form->isValid()){
            $vehicle = $form->getData();
    
            $entityManager->persist($vehicle);
            $entityManager->flush();

            $this->addFlash('success', 'Pojazd został dodany');

            return $this->redirectToRoute('app_vehicle');
        }


        return $this->render('vehicle/index.html.twig', [
            'form' => $form,
        ]);
    }
}
