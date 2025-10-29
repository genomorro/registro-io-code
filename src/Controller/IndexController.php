<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route(path: '/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    public function redirectToLocale(): RedirectResponse
    {
        return $this->redirectToRoute('app_index', ['_locale' => 'es']);
    }
}
