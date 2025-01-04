<?php

namespace App\Controller;


use App\Entity\Repair;
use App\Entity\Vehicle;
use App\Form\RepairType;
use App\Repository\RepairRepository;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RepairController extends AbstractController
{
    #[Route('/repair', name: 'app_repair')]
    public function add(Request $request, EntityManagerInterface $entityManager, RepairRepository $repairs): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(RepairType::class, new Repair(), [
            'user' => $user
        ]);

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
            'repairs' => $repairs->findAllByVehicle($user),
            'form_type' => 'add'
        ]);
    }

    #[Route('/repair/{repair}/edit', name: 'app_repair_edit')]
    public function editRepair(Request $request, EntityManagerInterface $entityManager, RepairRepository $repairs, Repair $repair): Response{
        
        $user = $this->getUser();

        $form = $this->createForm(RepairType::class, $repair, [
            'user' => $user,
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $repair = $form->getData();

            $entityManager->persist($repair);
            $entityManager->flush();

            $this->addFlash('success', 'Edytowano pomyślnie');

            return $this->redirectToRoute('app_repair');
        }

        return $this->render('repair/index.html.twig', [
            'form' => $form,
            'repairs' => $repairs->findAllByVehicle($user),
            'repair' => $repair,
            'form_type' => 'edit'
        ]);
    }

    #[Route('/repair/{repair}/remove', name: 'app_repair_remove')]
    public function removeRepair(RepairRepository $repairs, Repair $repair):Response{
        $repairs->deleteRepair($repair);
        return $this->redirectToRoute('app_repair');
    }
}
