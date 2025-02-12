<?php

namespace App\Controller;


use DateTime;
use App\Entity\Repair;
use App\Entity\Vehicle;
use App\Form\FilterType;
use App\Form\RepairType;
use App\Repository\RepairRepository;
use App\Repository\VehicleRepository;
use App\Controller\RepairCrudController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class RepairController extends AbstractController
{

    #[Route('/repair', name: 'app_repair')]
    public function add(Request $request, EntityManagerInterface $entityManager, RepairRepository $repairs, VehicleRepository $vehicles): Response
    {
        $user = $this->getUser();
        $sort = $request->query->get('sort');

        $form = $this->createForm(RepairType::class, new Repair(), [
            'user' => $user
        ]);

        $form -> handleRequest($request);

        $filters = [];
        $filters = $request->query->all('filter');

        if($form->isSubmitted() && $form->isValid()){
            $repairs = $form->getData();
            $repairs->setUser($user);

            $entityManager->persist($repairs);
            $entityManager->flush();

            $this->addFlash('success', 'Naprawa została dodana');
            return $this->redirectToRoute('app_repair');
        }

        

        return $this->render('repair/index.html.twig', [
            'repairs' => $repairs->findAllBySort($user, $sort, $filters),
            'user' => $user->getUserIdentifier(),
            'form' => $form->createView(),
            'form_type' => 'add',
            'sort' => $sort,
            'data_sort' => 'repair' ?? null,
            'vehicles' => $vehicles->findAllByOwner($user)->getQuery()->getResult(),
            'filters' => $filters,

        ]);

    }

    #[Route('/repair/{repair}/edit', name: 'app_repair_edit')]
    public function editRepair(Request $request, EntityManagerInterface $entityManager, RepairRepository $repairs, Repair $repair, VehicleRepository $vehicles): Response{
        
        $user = $this->getUser();
        $sort = $request->query->get('sort');
        
        $form = $this->createForm(RepairType::class, $repair, [
            'user' => $user,
        ]);
        $form->handleRequest($request);

        $filters = [];
        $filters = $request->query->all('filter');

        if($form->isSubmitted() && $form->isValid()){

            $repair = $form->getData();

            $entityManager->persist($repair);
            $entityManager->flush();

            $this->addFlash('success', 'Edytowano pomyślnie');

            return $this->redirectToRoute('app_repair');
        }

        return $this->render('repair/index.html.twig', [
            'form' => $form,
            'repairs' => $repairs->findAllBySort($user, $sort, $filters),
            'repair' => $repair,
            'form_type' => 'edit',
            'sort' => $sort,
            'data_sort' => 'repair' ?? null,
            'user' => $user->getUserIdentifier(),
            'vehicles' => $vehicles->findAllByOwner($user)->getQuery()->getResult(),
            'filters' => $filters,
        ]);
    }

    #[Route('/repair/{repair}/remove', name: 'app_repair_remove')]
    public function removeRepair(RepairRepository $repairs, Repair $repair):Response{
        $repairs->deleteRepair($repair);
        return $this->redirectToRoute('app_repair');
    }
}
