<?php

namespace App\Controller;

use App\Entity\Vehicle;
use App\Form\VehicleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VehicleType::class, new Vehicle());

        $form->handleRequest($request);

        if($form ->isSubmitted() && $form->isValid()){
            $vehicle = $form->getData();

            $entityManager->persist($vehicle);
            $entityManager->flush();

            $this->addFlash('success', 'Pojazd został dodany');

            return $this->redirectToRoute('app_index');
        }


        return $this->render('home/index.html.twig', [
            'form' => $form,
        ]);
    }
}
