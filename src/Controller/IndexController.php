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

    #[Route('/php')]
    public function php(): Response
    {
	$this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        ob_start();
        phpinfo();
        $info = ob_get_clean();

        return new Response($info);
    }

    #[Route('/about')]
    public function about (): Response
    {
	$creator = 'Edgar Uriel Domínguez Espinoza';
	$year = '2025';

	return new Response("Created by ".$creator." (".$year.").");
    }

    public function redirectToLocale(): RedirectResponse
    {
        return $this->redirectToRoute('app_index', ['_locale' => 'es']);
    }
}
