<?php

namespace App\Controller;

use DateTime;
use App\Entity\Vehicle;
use App\Form\VehicleType;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VehicleController extends AbstractController
{
    #[Route('/vehicle', name: 'app_vehicle')]
    public function addVehicle(Request $request, EntityManagerInterface $entityManager, VehicleRepository $vehicles): Response
    {
        $user = $this->getUser();
        $now = new DateTime('today');
        $sort = $request->query->get('sort');
        
        $form = $this->createForm(VehicleType::class, new Vehicle());

        $form->handleRequest($request);

        $owner = $this->getUser();
        if($form ->isSubmitted() && $form->isValid()){

            $vehicle = $form->getData();
            $vehicle->setOwner($this->getUser());
    
            $entityManager->persist($vehicle);
            $entityManager->flush();

            $this->addFlash('success', 'Pojazd został dodany');

            return $this->redirectToRoute('app_vehicle');
        }


        return $this->render('vehicle/index.html.twig', [
            'form' => $form,
            'owner' => $owner,
            'vehicles' => $vehicles->findAllBySort($user, $sort),
            'form_type' => 'add',
            'currentDate' => $now,
            'sort' =>$sort,
            'data_sort' => 'vehicle' ?? null,
            'user' => $user->getUserIdentifier(),
        ]);
    }

    #[Route('/vehicle/{vehicle}/edit', name: 'app_vehicle_edit')]
    public function editVehicle(Request $request, EntityManagerInterface $entityManager, Vehicle $vehicle, VehicleRepository $vehicles): Response
    {
        $user = $this->getUser();
        $now = new DateTime('today');
        $sort = $request->query->get('sort');

        $form = $this->createForm(VehicleType::class, $vehicle);

        $form->handleRequest($request);

        if($form ->isSubmitted() && $form->isValid()){
            $vehicle = $form->getData();
    
            $entityManager->persist($vehicle);
            $entityManager->flush();

            $this->addFlash('success', 'Pojazd został edytowany');

            return $this->redirectToRoute('app_vehicle');
        }


        return $this->render('vehicle/index.html.twig', [
            'form' => $form->createView(),
            'vehicle' => $vehicle,
            'vehicles' => $vehicles->findAllBySort($user, $sort),
            'form_type' => 'edit',
            'currentDate' => $now,
            'sort' => $sort,
            'data_sort' => 'vehicle' ?? null,
            'user' => $user->getUserIdentifier(),
        ]);
    }

    #[Route('/vehicle/{vehicle}/remove', name: 'app_vehicle_remove')]
    public function removeVehicle(Vehicle $vehicle, VehicleRepository $vehicles){
        $vehicles->deleteVehicle($vehicle);
        return $this->redirectToRoute('app_vehicle');
    }


}
