<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RaportController extends AbstractController
{
    #[Route('/raport', name: 'app_raport')]
    public function index(): Response
    {
        return $this->render('raport/index.html.twig', [
            'controller_name' => 'RaportController',
        ]);
    }
}
