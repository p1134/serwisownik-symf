<?php

namespace App\Controller;


use App\Entity\Repair;
use App\Form\RepairType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class RepairController extends AbstractController
{
    #[Route('/repair', name: 'app_repair')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RepairType::class, new Repair());

        $form -> handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $repair = $form->getData();

            $entityManager->persist($repair);
            $entityManager->flush();

            $this->addFlash('success', 'Naprawa została dodana');
            return $this->redirectToRoute('app_repair');
        }


        return $this->render('repair/index.html.twig', [
            'controller_name' => 'RepairController',
            'form' => $form,
        ]);
    }
}
