<?php

namespace App\Controller;

use App\Entity\Vehicle;
use App\Form\VehicleType;
use App\Repository\RepairRepository;
use App\Repository\VehicleRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(VehicleRepository $vehicles, RepairRepository $repairs): Response
    {
        $user = $this->getUser();
        $date = new DateTime('now');
        $now = $date->format('Y-m-d');
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'vehicles' => $vehicles->findAllByOwner($user),
            'totalRepairs' => $repairs->getTotalRepairCost($user),
            'nextService' => $vehicles->nextService($user, $now)
        ]);

    }
}
