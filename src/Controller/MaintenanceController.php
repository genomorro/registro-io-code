<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MaintenanceController extends AbstractController
{
    #[Route('/maintenance', name: 'app_maintenance')]
    public function index(): Response
    {
        return $this->render('maintenance/index.html.twig', [], new Response('', 503));
    }
}
